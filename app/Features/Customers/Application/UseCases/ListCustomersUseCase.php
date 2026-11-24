<?php

namespace App\Features\Customers\Application\UseCases;

use App\Features\Customers\Domain\Entities\CustomerEntity;
use App\Features\Customers\Domain\Repositories\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomersUseCase
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {}

    /**
     * @return LengthAwarePaginator<int, CustomerEntity>
     */
    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        return $this->customers->paginate($perPage);
    }
}
