<?php

namespace App\Features\Users\Application\Services;

use App\Features\Users\Domain\Repositories\CustomerRepositoryInterface;
use App\Features\Users\Infrastructure\Persistence\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->customers->paginate($perPage);
    }

    public function find(int $id): ?Customer
    {
        return $this->customers->findById($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer
    {
        return $this->customers->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer
    {
        return $this->customers->update($customer, $data);
    }

    public function delete(Customer $customer): bool
    {
        return $this->customers->delete($customer);
    }
}
