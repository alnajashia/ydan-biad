<?php

namespace App\Features\Customers\Infrastructure\Persistence\Repositories;

use App\Features\Customers\Domain\Entities\CustomerEntity;
use App\Features\Customers\Domain\Repositories\CustomerRepositoryInterface;
use App\Features\Customers\Infrastructure\Persistence\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CustomerEntity
    {
        $model = Customer::query()->create($data);

        return CustomerEntity::fromModel($model->refresh());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CustomerEntity $customer, array $data): CustomerEntity
    {
        $model = Customer::query()->findOrFail($customer->id);
        $model->update($data);

        return CustomerEntity::fromModel($model->refresh());
    }

    public function delete(CustomerEntity $customer): bool
    {
        $model = Customer::query()->find($customer->id);

        if ($model === null) {
            return false;
        }

        return (bool) $model->delete();
    }

    public function findById(int $id): ?CustomerEntity
    {
        $model = Customer::query()->with('user')->find($id);

        if ($model === null) {
            return null;
        }

        return CustomerEntity::fromModel($model);
    }

    /**
     * @return LengthAwarePaginator<int, CustomerEntity>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $paginator = Customer::query()->with('user')->latest()->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (Customer $model): CustomerEntity => CustomerEntity::fromModel($model)),
        );

        return $paginator;
    }
}
