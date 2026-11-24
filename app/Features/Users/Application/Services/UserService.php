<?php

namespace App\Features\Users\Application\Services;

use App\Features\Users\Application\DTOs\CreateUserDTO;
use App\Features\Users\Application\DTOs\UpdateUserDTO;
use App\Features\Users\Application\UseCases\CreateUserUseCase;
use App\Features\Users\Application\UseCases\DeleteUserUseCase;
use App\Features\Users\Application\UseCases\GetUserUseCase;
use App\Features\Users\Application\UseCases\ListUsersUseCase;
use App\Features\Users\Application\UseCases\UpdateUserUseCase;
use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\Enums\UserStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private readonly ListUsersUseCase $listUsers,
        private readonly GetUserUseCase $getUser,
        private readonly CreateUserUseCase $createUser,
        private readonly UpdateUserUseCase $updateUser,
        private readonly DeleteUserUseCase $deleteUser,
    ) {}

    /**
     * @return LengthAwarePaginator<int, UserEntity>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->listUsers->execute($perPage);
    }

    public function find(int $id): ?UserEntity
    {
        return $this->getUser->execute($id);
    }

    public function create(CreateUserDTO $dto): UserEntity
    {
        return $this->createUser->execute($dto);
    }

    public function update(UserEntity $user, UpdateUserDTO $dto): UserEntity
    {
        return $this->updateUser->execute($user, $dto);
    }

    public function delete(UserEntity $user): bool
    {
        return $this->deleteUser->execute($user);
    }

    /**
     * Activate a suspended account so it can log in again.
     */
    public function activate(UserEntity $user): UserEntity
    {
        return $this->updateUser->execute($user, new UpdateUserDTO(status: UserStatus::Active));
    }

    /**
     * Suspend an account: it can no longer log in or use the API.
     */
    public function suspend(UserEntity $user): UserEntity
    {
        return $this->updateUser->execute($user, new UpdateUserDTO(status: UserStatus::Suspended));
    }
}
