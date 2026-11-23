<?php

namespace App\Features\Lookups\Presentation\Http\Controllers;

use App\Features\Lookups\Application\Services\RoleService;
use App\Features\Lookups\Infrastructure\Persistence\Models\Role;
use App\Features\Lookups\Presentation\Http\Requests\StoreRoleRequest;
use App\Features\Lookups\Presentation\Http\Requests\UpdateRoleRequest;
use App\Features\Lookups\Presentation\Http\Resources\RoleResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return RoleResource::collection($this->roleService->paginate($request->integer('per_page', 15)));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return response()->json(['role' => new RoleResource($role)], 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json(['role' => new RoleResource($role)]);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $updated = $this->roleService->update($role, $request->validated());

        return response()->json(['role' => new RoleResource($updated)]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->delete($role);

        return response()->json(['message' => 'Role deleted.']);
    }
}
