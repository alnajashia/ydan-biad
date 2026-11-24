<?php

namespace App\Features\Users\Application\UseCases;

use App\Features\Users\Application\Services\TokenService;
use App\Features\Users\Domain\Entities\UserEntity;
use Illuminate\Validation\ValidationException;

class RefreshTokenUseCase
{
    public function __construct(
        private readonly TokenService $tokens,
    ) {}

    /**
     * @return array{user: UserEntity, access_token: string, refresh_token: string, token_type: string, expires_in: int}
     *
     * @throws ValidationException
     */
    public function execute(string $plainToken): array
    {
        $result = $this->tokens->refresh($plainToken);

        if ($result === null) {
            throw ValidationException::withMessages([
                'refresh_token' => ['The provided refresh token is invalid.'],
            ]);
        }

        return $result;
    }
}
