<?php

namespace App\Features\Users\Infrastructure\Persistence\Repositories;

use App\Features\Users\Domain\Repositories\CustomerRepositoryInterface;
use App\Features\Users\Infrastructure\Persistence\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer
    {
        return Customer::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->refresh();
    }

    public function delete(Customer $customer): bool
    {
        return (bool) $customer->delete();
    }

    public function findById(int $id): ?Customer
    {
        return Customer::query()->with('user')->find($id);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()->with('user')->latest()->paginate($perPage);
    }
}
