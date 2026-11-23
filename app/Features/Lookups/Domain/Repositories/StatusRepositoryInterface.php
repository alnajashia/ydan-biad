<?php

namespace App\Features\Lookups\Domain\Repositories;

use App\Features\Lookups\Infrastructure\Persistence\Models\Status;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StatusRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Status;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Status $status, array $data): Status;

    public function delete(Status $status): bool;

    public function findById(int $id): ?Status;

    public function findByName(string $name): ?Status;

    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
