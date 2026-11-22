<?php

namespace App\Features\Users\Domain\Repositories;

use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User;

    public function delete(User $user): bool;

    public function findById(int $id): ?User;

    public function findByPhone(string $phone): ?User;

    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
