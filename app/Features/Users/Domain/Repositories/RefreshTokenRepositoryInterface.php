<?php

namespace App\Features\Users\Domain\Repositories;

use App\Features\Users\Domain\Entities\RefreshTokenEntity;

interface RefreshTokenRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): RefreshTokenEntity;

    public function findByHash(string $hash): ?RefreshTokenEntity;

    public function revoke(RefreshTokenEntity $token): RefreshTokenEntity;

    public function revokeAllForUser(int $userId): void;

    public function revokeForAccessToken(int $accessTokenId): void;
}
