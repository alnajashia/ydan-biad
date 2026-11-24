<?php

namespace App\Features\Customers\Domain\Repositories;

use App\Features\Customers\Domain\Entities\CustomerEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CustomerEntity;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CustomerEntity $customer, array $data): CustomerEntity;

    public function delete(CustomerEntity $customer): bool;

    public function findById(int $id): ?CustomerEntity;

    /**
     * @return LengthAwarePaginator<int, CustomerEntity>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
