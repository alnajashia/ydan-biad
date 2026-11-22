<?php

namespace App\Features\Users\Infrastructure\Persistence\Repositories;

use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    public function findById(int $id): ?User
    {
        return User::query()->with('customer')->find($id);
    }

    public function findByPhone(string $phone): ?User
    {
        return User::query()->where('phone', $phone)->first();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()->with('customer')->latest()->paginate($perPage);
    }
}
