<?php

namespace App\Features\Users\Application\UseCases;

use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;

class DeleteUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(UserEntity $user): bool
    {
        return $this->users->delete($user);
    }
}
