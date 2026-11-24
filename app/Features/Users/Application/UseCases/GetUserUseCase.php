<?php

namespace App\Features\Users\Application\UseCases;

use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;

class GetUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(int $id): ?UserEntity
    {
        return $this->users->findById($id);
    }
}
