<?php

namespace App\Features\Users\Presentation\Http\Controllers;

use App\Features\Users\Application\DTOs\CreateUserDTO;
use App\Features\Users\Application\DTOs\UpdateUserDTO;
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
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return UserResource::collection($this->userService->paginate($perPage));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
          $validated = $request->validated();
        $user = $this->userService->create(CreateUserDTO::toDto($validated));

        return response()->json(['user' => new UserResource($user)], 201);
    }

    public function show(User $user): JsonResponse
    {
        $entity = $this->userService->find($user->id);

        if ($entity === null) {
            abort(404);
        }

        return response()->json(['user' => new UserResource($entity)]);
    }

    public function update(UpdateUserRequest $request, int $userId): JsonResponse
    {
        $validated = $request->validated();

        $updated = $this->userService->update($userId, UpdateUserDTO::toDto($validated));

        return response()->json(['user' => new UserResource($updated)]);
    }

    public function destroy(int $userId): JsonResponse
    {
        $this->userService->delete($userId);

        return response()->json(['message' => 'User deleted.']);
    }

    public function activate(int $userId): JsonResponse
    {
        $activated = $this->userService->activate($userId);

        return response()->json(['user' => new UserResource($activated)]);
    }

    public function suspend(int $userId): JsonResponse
    {
        $suspended = $this->userService->suspend($userId);

        return response()->json(['user' => new UserResource($suspended)]);
    }
}
