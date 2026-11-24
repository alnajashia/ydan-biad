<?php

namespace App\Features\Users\Application\UseCases;

use App\Features\Users\Application\DTOs\UpdateUserDTO;
use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;

class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(UserEntity $user, UpdateUserDTO $dto): UserEntity
    {
        $frontImage = $dto->identityFrontImage instanceof UploadedFile
            ? $this->storeImage($dto->identityFrontImage)
            : null;

        $backImage = $dto->identityBackImage instanceof UploadedFile
            ? $this->storeImage($dto->identityBackImage)
            : null;

        $dto = $dto->withStoredImages($frontImage, $backImage);

        return $this->users->update($user, $dto->toPersistenceArray());
    }

    private function storeImage(UploadedFile $image): string
    {
        return $image->store('identity', 'public');
    }
}
