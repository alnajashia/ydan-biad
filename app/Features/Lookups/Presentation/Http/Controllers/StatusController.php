<?php

namespace App\Features\Lookups\Presentation\Http\Controllers;

use App\Features\Lookups\Application\Services\StatusService;
use App\Features\Lookups\Infrastructure\Persistence\Models\Status;
use App\Features\Lookups\Presentation\Http\Requests\StoreStatusRequest;
use App\Features\Lookups\Presentation\Http\Requests\UpdateStatusRequest;
use App\Features\Lookups\Presentation\Http\Resources\StatusResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StatusController extends Controller
{
    public function __construct(
        private readonly StatusService $statusService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return StatusResource::collection($this->statusService->paginate($request->integer('per_page', 15)));
    }

    public function store(StoreStatusRequest $request): JsonResponse
    {
        $status = $this->statusService->create($request->validated());

        return response()->json(['status' => new StatusResource($status)], 201);
    }

    public function show(Status $status): JsonResponse
    {
        return response()->json(['status' => new StatusResource($status)]);
    }

    public function update(UpdateStatusRequest $request, Status $status): JsonResponse
    {
        $updated = $this->statusService->update($status, $request->validated());

        return response()->json(['status' => new StatusResource($updated)]);
    }

    public function destroy(Status $status): JsonResponse
    {
        $this->statusService->delete($status);

        return response()->json(['message' => 'Status deleted.']);
    }
}
