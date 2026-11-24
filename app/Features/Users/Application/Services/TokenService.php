<?php

namespace App\Features\Users\Application\Services;

use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\Repositories\RefreshTokenRepositoryInterface;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Str;

class TokenService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RefreshTokenRepositoryInterface $refreshTokens,
    ) {}

    /**
     * Issue a short-lived access token with a long-lived refresh token.
     *
     * The refresh token is stored only as a SHA-256 hash and is linked to
     * the access token it was issued with, so logging out can revoke both.
     *
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function issuePair(UserEntity $user): array
    {
        $access = $this->users->issueAccessToken($user);
        $plainRefreshToken = Str::random(64);

        $this->refreshTokens->create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plainRefreshToken),
            'access_token_id' => $access['id'],
            'expires_at' => now()->addDays(30),
        ]);

        return [
            'access_token' => $access['token'],
            'refresh_token' => $plainRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => (int) config('sanctum.expiration', 60) * 60,
        ];
    }

    /**
     * Rotate a refresh token: the old one is revoked and its access token
     * deleted, then a fresh pair is issued. Returns null when the token
     * is unknown, expired, revoked, or belongs to an inactive account.
     *
     * @return array{user: UserEntity, access_token: string, refresh_token: string, token_type: string, expires_in: int}|null
     */
    public function refresh(string $plainToken): ?array
    {
        $stored = $this->refreshTokens->findByHash(hash('sha256', $plainToken));

        if ($stored === null || ! $stored->isValid()) {
            return null;
        }

        $user = $this->users->findById($stored->userId);

        if ($user === null || ! $user->canAuthenticate()) {
            return null;
        }

        $this->refreshTokens->revoke($stored);

        if ($stored->accessTokenId !== null) {
            $this->users->revokeAccessTokenById($stored->accessTokenId);
        }

        return ['user' => $user, ...$this->issuePair($user)];
    }

    /**
     * Revoke one session: its access token and the refresh token linked to it.
     */
    public function revokeCurrentSession(?int $accessTokenId): void
    {
        if ($accessTokenId === null) {
            return;
        }

        $this->refreshTokens->revokeForAccessToken($accessTokenId);
        $this->users->revokeAccessTokenById($accessTokenId);
    }

    /**
     * Revoke every session of a user: all access and refresh tokens.
     */
    public function revokeAllSessions(int $userId): void
    {
        $this->refreshTokens->revokeAllForUser($userId);
        $this->users->revokeAllAccessTokens($userId);
    }
}
