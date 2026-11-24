<?php

namespace App\Features\Users\Application\UseCases;

use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUsersUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @return LengthAwarePaginator<int, UserEntity>
     */
    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginate($perPage);
    }
}
