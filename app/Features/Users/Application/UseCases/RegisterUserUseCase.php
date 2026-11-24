<?php

namespace App\Features\Users\Application\UseCases;

use App\Features\Users\Application\DTOs\RegisterUserDTO;
use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;

class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * Register a new user. The account starts suspended (database default)
     * until an admin activates it, so no token is issued here.
     */
    public function execute(RegisterUserDTO $dto): UserEntity
    {
        $dto = $dto->withStoredImages(
            $this->storeImage($dto->identityFrontImage),
            $this->storeImage($dto->identityBackImage),
        );

        return $this->users->create($dto->toPersistenceArray());
    }

    private function storeImage(UploadedFile|string $image): string
    {
        if ($image instanceof UploadedFile) {
            return $image->store('identity', 'public');
        }

        return $image;
    }
}
