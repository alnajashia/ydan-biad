<?php

namespace App\Features\Users\Application\DTOs;

readonly class RegisterUserDTO
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public array $attributes,
    ) {}
}
