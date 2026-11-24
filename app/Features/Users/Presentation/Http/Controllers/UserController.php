<?php

namespace App\Features\Users\Presentation\Http\Controllers;

use App\Features\Users\Application\DTOs\CreateUserDTO;
use App\Features\Users\Application\DTOs\UpdateUserDTO;
use App\Features\Users\Application\UseCases\CreateUserUseCase;
use App\Features\Users\Application\UseCases\DeleteUserUseCase;
use App\Features\Users\Application\UseCases\GetUserUseCase;
use App\Features\Users\Application\UseCases\ListUsersUseCase;
use App\Features\Users\Application\UseCases\UpdateUserUseCase;
use App\Features\Users\Domain\Entities\UserEntity;
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
        private readonly ListUsersUseCase $listUsers,
        private readonly GetUserUseCase $getUser,
        private readonly CreateUserUseCase $createUser,
        private readonly UpdateUserUseCase $updateUser,
        private readonly DeleteUserUseCase $deleteUser,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return UserResource::collection($this->listUsers->execute($perPage));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->createUser->execute(CreateUserDTO::fromRequest($request));

        return response()->json(['user' => new UserResource($user)], 201);
    }

    public function show(User $user): JsonResponse
    {
        $entity = $this->getUser->execute($user->id);

        if ($entity === null) {
            abort(404);
        }

        return response()->json(['user' => new UserResource($entity)]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updated = $this->updateUser->execute(UserEntity::fromModel($user), UpdateUserDTO::fromRequest($request));

        return response()->json(['user' => new UserResource($updated)]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->deleteUser->execute(UserEntity::fromModel($user));

        return response()->json(['message' => 'User deleted.']);
    }
}
