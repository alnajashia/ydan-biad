<?php

namespace App\Features\Lookups\Application\Services;

use App\Features\Lookups\Domain\Repositories\RoleRepositoryInterface;
use App\Features\Lookups\Infrastructure\Persistence\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->roles->paginate($perPage);
    }

    public function find(int $id): ?Role
    {
        return $this->roles->findById($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        return $this->roles->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role
    {
        return $this->roles->update($role, $data);
    }

    public function delete(Role $role): bool
    {
        return $this->roles->delete($role);
    }
}
