<?php

namespace App\Features\Users\Application\UseCases;

use App\Features\Users\Application\Services\TokenService;

class LogoutUserUseCase
{
    public function __construct(
        private readonly TokenService $tokens,
    ) {}

    public function execute(?int $accessTokenId): void
    {
        $this->tokens->revokeCurrentSession($accessTokenId);
    }
}
