<?php

namespace App\Features\Lookups\Infrastructure\Persistence\Repositories;

use App\Features\Lookups\Domain\Repositories\StatusRepositoryInterface;
use App\Features\Lookups\Infrastructure\Persistence\Models\Status;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentStatusRepository implements StatusRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Status
    {
        return Status::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Status $status, array $data): Status
    {
        $status->update($data);

        return $status->refresh();
    }

    public function delete(Status $status): bool
    {
        return (bool) $status->delete();
    }

    public function findById(int $id): ?Status
    {
        return Status::query()->find($id);
    }

    public function findByName(string $name): ?Status
    {
        return Status::query()->where('name', $name)->first();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Status::query()->orderBy('id')->paginate($perPage);
    }
}
