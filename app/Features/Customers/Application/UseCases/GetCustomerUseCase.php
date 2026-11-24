<?php

namespace App\Features\Customers\Application\UseCases;

use App\Features\Customers\Domain\Entities\CustomerEntity;
use App\Features\Customers\Domain\Repositories\CustomerRepositoryInterface;

class GetCustomerUseCase
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {}

    public function execute(int $id): ?CustomerEntity
    {
        return $this->customers->findById($id);
    }
}
