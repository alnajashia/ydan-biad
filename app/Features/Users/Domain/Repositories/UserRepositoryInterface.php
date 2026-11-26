<?php

namespace App\Features\Users\Domain\Repositories;

use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\ValueObjects\PhoneNumber;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): UserEntity;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $userId, array $data): UserEntity;

    public function delete(int $userId): bool;

    public function findById(int $id): ?UserEntity;

    public function findByPhone(PhoneNumber $phone): ?UserEntity;

    /**
     * Find a user by phone and password. The password hash never leaves
     * the infrastructure layer: a wrong password returns null.
     */
    public function findByCredentials(PhoneNumber $phone, string $password): ?UserEntity;

    /**
     * @return LengthAwarePaginator<int, UserEntity>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Issue a Sanctum access token for the user.
     *
     * @return array{token: string, id: int}
     */
    public function issueAccessToken(UserEntity $user): array;

    public function revokeAccessTokenById(int $id): void;

    public function revokeAllAccessTokens(int $userId): void;
}
