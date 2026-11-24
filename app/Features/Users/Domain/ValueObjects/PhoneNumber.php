<?php

namespace App\Features\Users\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class PhoneNumber
{
    private function __construct(
        public string $value,
    ) {}

    /**
     * Normalize a raw phone number to the 9-digit local form.
     *
     * Accepts the local form (777123456), the national form (0777123456)
     * and the international form (+967777123456 or 967777123456).
     */
    public static function normalize(string $value): string
    {
        $value = preg_replace('/[\s\-]/', '', trim($value)) ?? '';
        $value = ltrim($value, '+');

        if (str_starts_with($value, '967')) {
            $value = substr($value, 3);
        }

        return ltrim($value, '0');
    }

    public static function tryFromString(string $value): ?self
    {
        $normalized = self::normalize($value);

        if (! preg_match('/^7\d{8}$/', $normalized)) {
            return null;
        }

        return new self($normalized);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        $phone = self::tryFromString($value);

        if ($phone === null) {
            throw new InvalidArgumentException('The phone number must be 9 digits starting with 7.');
        }

        return $phone;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
