<?php

namespace App\Features\Users\Application\UseCases;

use App\Features\Users\Application\DTOs\LoginDTO;
use App\Features\Users\Application\Services\TokenService;
use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Validation\ValidationException;

class LoginUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly TokenService $tokens,
    ) {}

    /**
     * @return array{user: UserEntity, access_token: string, refresh_token: string, token_type: string, expires_in: int}
     *
     * @throws ValidationException
     */
    public function execute(LoginDTO $dto): array
    {
        $user = $this->users->findByCredentials($dto->phone, $dto->password);

        if ($user === null) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->canAuthenticate()) {
            throw ValidationException::withMessages([
                'phone' => ['This account is not active.'],
            ]);
        }

        return ['user' => $user, ...$this->tokens->issuePair($user)];
    }
}
