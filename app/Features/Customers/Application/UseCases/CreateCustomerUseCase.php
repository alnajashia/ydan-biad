<?php

namespace App\Features\Customers\Application\UseCases;

use App\Features\Customers\Application\DTOs\CreateCustomerDTO;
use App\Features\Customers\Domain\Entities\CustomerEntity;
use App\Features\Customers\Domain\Repositories\CustomerRepositoryInterface;

class CreateCustomerUseCase
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {}

    public function execute(CreateCustomerDTO $dto): CustomerEntity
    {
        return $this->customers->create($dto->toPersistenceArray());
    }
}
