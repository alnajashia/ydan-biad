<?php

namespace App\Features\Users\Domain\Repositories;

use App\Features\Users\Infrastructure\Persistence\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer;

    public function delete(Customer $customer): bool;

    public function findById(int $id): ?Customer;

    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
