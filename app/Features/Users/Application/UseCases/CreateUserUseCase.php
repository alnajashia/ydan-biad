<?php

namespace App\Features\Users\Application\UseCases;

use App\Features\Users\Application\DTOs\CreateUserDTO;
use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;

class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(CreateUserDTO $dto): UserEntity
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
