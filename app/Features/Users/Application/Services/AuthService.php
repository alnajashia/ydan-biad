<?php

namespace App\Features\Users\Application\Services;

use App\Features\Users\Application\DTOs\LoginDTO;
use App\Features\Users\Application\DTOs\RegisterUserDTO;
use App\Features\Users\Application\UseCases\LoginUserUseCase;
use App\Features\Users\Application\UseCases\LogoutUserUseCase;
use App\Features\Users\Application\UseCases\RefreshTokenUseCase;
use App\Features\Users\Application\UseCases\RegisterUserUseCase;
use App\Features\Users\Domain\Entities\UserEntity;

class AuthService
{
    public function __construct(
        private readonly RegisterUserUseCase $registerUser,
        private readonly LoginUserUseCase $loginUser,
        private readonly LogoutUserUseCase $logoutUser,
        private readonly RefreshTokenUseCase $refreshToken,
        private readonly TokenService $tokens,
    ) {}

    public function register(RegisterUserDTO $dto): UserEntity
    {
        return $this->registerUser->execute($dto);
    }

    /**
     * @return array{user: UserEntity, access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function login(LoginDTO $dto): array
    {
        return $this->loginUser->execute($dto);
    }

    public function logout(?int $accessTokenId): void
    {
        $this->logoutUser->execute($accessTokenId);
    }

    /**
     * @return array{user: UserEntity, access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function refresh(string $plainToken): array
    {
        return $this->refreshToken->execute($plainToken);
    }

    /**
     * Log the user out of every device: all access and refresh tokens.
     */
    public function logoutEverywhere(int $userId): void
    {
        $this->tokens->revokeAllSessions($userId);
    }
}
