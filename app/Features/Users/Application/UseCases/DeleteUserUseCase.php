<?php

namespace App\Features\Users\Application\UseCases;

use App\Features\Users\Domain\Repositories\UserRepositoryInterface;

class DeleteUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(int $userId): bool
    {
        return $this->users->delete($userId);
    }
}
