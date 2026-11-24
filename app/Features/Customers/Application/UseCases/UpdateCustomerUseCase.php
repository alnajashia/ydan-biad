<?php

namespace App\Features\Customers\Application\UseCases;

use App\Features\Customers\Application\DTOs\UpdateCustomerDTO;
use App\Features\Customers\Domain\Entities\CustomerEntity;
use App\Features\Customers\Domain\Repositories\CustomerRepositoryInterface;

class UpdateCustomerUseCase
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {}

    public function execute(CustomerEntity $customer, UpdateCustomerDTO $dto): CustomerEntity
    {
        return $this->customers->update($customer, $dto->toPersistenceArray());
    }
}
