<?php

namespace App\Features\Users\Infrastructure\Persistence\Repositories;

use App\Features\Users\Domain\Entities\RefreshTokenEntity;
use App\Features\Users\Domain\Repositories\RefreshTokenRepositoryInterface;
use App\Features\Users\Infrastructure\Persistence\Models\RefreshToken;

class EloquentRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): RefreshTokenEntity
    {
        $model = RefreshToken::query()->create($data);

        return RefreshTokenEntity::fromModel($model->refresh());
    }

    public function findByHash(string $hash): ?RefreshTokenEntity
    {
        $model = RefreshToken::query()->where('token', $hash)->first();

        if ($model === null) {
            return null;
        }

        return RefreshTokenEntity::fromModel($model);
    }

    public function revoke(RefreshTokenEntity $token): RefreshTokenEntity
    {
        $model = RefreshToken::query()->findOrFail($token->id);
        $model->update(['revoked_at' => now()]);

        return RefreshTokenEntity::fromModel($model->refresh());
    }

    public function revokeAllForUser(int $userId): void
    {
        RefreshToken::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    public function revokeForAccessToken(int $accessTokenId): void
    {
        RefreshToken::query()
            ->where('access_token_id', $accessTokenId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }
}
