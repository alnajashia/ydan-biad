<?php

namespace App\Features\Lookups\Domain\Repositories;

use App\Features\Lookups\Infrastructure\Persistence\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoleRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role;

    public function delete(Role $role): bool;

    public function findById(int $id): ?Role;

    public function findByName(string $name): ?Role;

    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
