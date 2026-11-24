<?php

namespace App\Features\Users\Application\DTOs;

use App\Features\Users\Domain\ValueObjects\PhoneNumber;
use App\Features\Users\Presentation\Http\Requests\LoginRequest;

final readonly class LoginDTO
{
    public function __construct(
        public PhoneNumber $phone,
        public string $password,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            phone: PhoneNumber::fromString($validated['phone']),
            password: $validated['password'],
        );
    }
}
