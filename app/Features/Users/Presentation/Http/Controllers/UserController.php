<?php

namespace App\Features\Users\Presentation\Http\Controllers;

use App\Features\Users\Application\Services\UserService;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use App\Features\Users\Presentation\Http\Requests\StoreUserRequest;
use App\Features\Users\Presentation\Http\Requests\UpdateUserRequest;
use App\Features\Users\Presentation\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return UserResource::collection($this->userService->paginate($request->integer('per_page', 15)));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return response()->json(['user' => new UserResource($user)], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(['user' => new UserResource($user->load(['customer', 'status', 'role']))]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updated = $this->userService->update($user, $request->validated());

        return response()->json(['user' => new UserResource($updated)]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return response()->json(['message' => 'User deleted.']);
    }
}
