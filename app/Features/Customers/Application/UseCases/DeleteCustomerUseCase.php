<?php

namespace App\Features\Customers\Application\UseCases;

use App\Features\Customers\Domain\Entities\CustomerEntity;
use App\Features\Customers\Domain\Repositories\CustomerRepositoryInterface;

class DeleteCustomerUseCase
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {}

    public function execute(CustomerEntity $customer): bool
    {
        return $this->customers->delete($customer);
    }
}
