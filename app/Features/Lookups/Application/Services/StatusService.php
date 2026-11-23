<?php

namespace App\Features\Lookups\Application\Services;

use App\Features\Lookups\Domain\Repositories\StatusRepositoryInterface;
use App\Features\Lookups\Infrastructure\Persistence\Models\Status;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StatusService
{
    public function __construct(
        private readonly StatusRepositoryInterface $statuses,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->statuses->paginate($perPage);
    }

    public function find(int $id): ?Status
    {
        return $this->statuses->findById($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Status
    {
        return $this->statuses->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Status $status, array $data): Status
    {
        return $this->statuses->update($status, $data);
    }

    public function delete(Status $status): bool
    {
        return $this->statuses->delete($status);
    }
}
