<?php

namespace App\Features\Lookups\Infrastructure\Persistence\Repositories;

use App\Features\Lookups\Domain\Repositories\RoleRepositoryInterface;
use App\Features\Lookups\Infrastructure\Persistence\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        return Role::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role->refresh();
    }

    public function delete(Role $role): bool
    {
        return (bool) $role->delete();
    }

    public function findById(int $id): ?Role
    {
        return Role::query()->find($id);
    }

    public function findByName(string $name): ?Role
    {
        return Role::query()->where('name', $name)->first();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Role::query()->orderBy('id')->paginate($perPage);
    }
}
