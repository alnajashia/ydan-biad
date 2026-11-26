<?php

namespace App\Features\Users\Application\DTOs;

use App\Features\Users\Domain\ValueObjects\PhoneNumber;

final readonly class LoginDTO
{
    public function __construct(
        public PhoneNumber $phone,
        public string $password,
    ) {}

    public static function toDto(array $data): self
    {
       

        return new self(
            phone: PhoneNumber::fromString($data['phone']),
            password: $data['password'],
        );
    }
}
