<?php

namespace App\Features\Customers\Presentation\Http\Controllers;

use App\Features\Customers\Application\Services\CustomerService;
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
        return CustomerResource::collection($this->customerService->paginate($request->integer('per_page', 15)));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->create($request->validated());

        return response()->json(['customer' => new CustomerResource($customer)], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json(['customer' => new CustomerResource($customer->load('user'))]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $updated = $this->customerService->update($customer, $request->validated());

        return response()->json(['customer' => new CustomerResource($updated)]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerService->delete($customer);

        return response()->json(['message' => 'Customer deleted.']);
    }
}
