<?php

namespace App\Features\Customers\Application\Services;

use App\Features\Customers\Application\DTOs\CreateCustomerDTO;
use App\Features\Customers\Application\DTOs\UpdateCustomerDTO;
use App\Features\Customers\Application\UseCases\CreateCustomerUseCase;
use App\Features\Customers\Application\UseCases\DeleteCustomerUseCase;
use App\Features\Customers\Application\UseCases\GetCustomerUseCase;
use App\Features\Customers\Application\UseCases\ListCustomersUseCase;
use App\Features\Customers\Application\UseCases\UpdateCustomerUseCase;
use App\Features\Customers\Domain\Entities\CustomerEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function __construct(
        private readonly ListCustomersUseCase $listCustomers,
        private readonly GetCustomerUseCase $getCustomer,
        private readonly CreateCustomerUseCase $createCustomer,
        private readonly UpdateCustomerUseCase $updateCustomer,
        private readonly DeleteCustomerUseCase $deleteCustomer,
    ) {}

    /**
     * @return LengthAwarePaginator<int, CustomerEntity>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->listCustomers->execute($perPage);
    }

    public function find(int $id): ?CustomerEntity
    {
        return $this->getCustomer->execute($id);
    }

    public function create(CreateCustomerDTO $dto): CustomerEntity
    {
        return $this->createCustomer->execute($dto);
    }

    public function update(CustomerEntity $customer, UpdateCustomerDTO $dto): CustomerEntity
    {
        return $this->updateCustomer->execute($customer, $dto);
    }

    public function delete(CustomerEntity $customer): bool
    {
        return $this->deleteCustomer->execute($customer);
    }
}
