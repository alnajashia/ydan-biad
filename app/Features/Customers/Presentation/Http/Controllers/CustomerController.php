<?php

namespace App\Features\Customers\Presentation\Http\Controllers;

use App\Features\Customers\Application\DTOs\CreateCustomerDTO;
use App\Features\Customers\Application\DTOs\UpdateCustomerDTO;
use App\Features\Customers\Application\Services\CustomerService;
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
        private readonly CustomerService $customerService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return CustomerResource::collection($this->customerService->paginate($perPage));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->create(CreateCustomerDTO::fromRequest($request));

        return response()->json(['customer' => new CustomerResource($customer)], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $entity = $this->customerService->find($customer->id);

        if ($entity === null) {
            abort(404);
        }

        return response()->json(['customer' => new CustomerResource($entity)]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $updated = $this->customerService->update(CustomerEntity::fromModel($customer), UpdateCustomerDTO::fromRequest($request));

        return response()->json(['customer' => new CustomerResource($updated)]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerService->delete(CustomerEntity::fromModel($customer));

        return response()->json(['message' => 'Customer deleted.']);
    }
}
