<?php

namespace App\Features\Customers\Presentation\Http\Controllers;

use App\Features\Customers\Application\DTOs\CreateCustomerDTO;
use App\Features\Customers\Application\DTOs\UpdateCustomerDTO;
use App\Features\Customers\Application\UseCases\CreateCustomerUseCase;
use App\Features\Customers\Application\UseCases\DeleteCustomerUseCase;
use App\Features\Customers\Application\UseCases\GetCustomerUseCase;
use App\Features\Customers\Application\UseCases\ListCustomersUseCase;
use App\Features\Customers\Application\UseCases\UpdateCustomerUseCase;
use App\Features\Customers\Domain\Entities\CustomerEntity;
use App\Features\Customers\Infrastructure\Persistence\Models\Customer;
use App\Features\Customers\Presentation\Http\Requests\StoreCustomerRequest;
use App\Features\Customers\Presentation\Http\Requests\UpdateCustomerRequest;
use App\Features\Customers\Presentation\Http\Resources\CustomerResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function __construct(
        private readonly ListCustomersUseCase $listCustomers,
        private readonly GetCustomerUseCase $getCustomer,
        private readonly CreateCustomerUseCase $createCustomer,
        private readonly UpdateCustomerUseCase $updateCustomer,
        private readonly DeleteCustomerUseCase $deleteCustomer,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return CustomerResource::collection($this->listCustomers->execute($perPage));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->createCustomer->execute(CreateCustomerDTO::fromRequest($request));

        return response()->json(['customer' => new CustomerResource($customer)], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $entity = $this->getCustomer->execute($customer->id);

        if ($entity === null) {
            abort(404);
        }

        return response()->json(['customer' => new CustomerResource($entity)]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $updated = $this->updateCustomer->execute(CustomerEntity::fromModel($customer), UpdateCustomerDTO::fromRequest($request));

        return response()->json(['customer' => new CustomerResource($updated)]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->deleteCustomer->execute(CustomerEntity::fromModel($customer));

        return response()->json(['message' => 'Customer deleted.']);
    }
}
