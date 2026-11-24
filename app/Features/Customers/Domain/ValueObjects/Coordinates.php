<?php

namespace App\Features\Customers\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Coordinates
{
    private function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    public static function tryFromFloats(?float $latitude, ?float $longitude): ?self
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        return new self($latitude, $longitude);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromFloats(float $latitude, float $longitude): self
    {
        $coordinates = self::tryFromFloats($latitude, $longitude);

        if ($coordinates === null) {
            throw new InvalidArgumentException('The coordinates are out of range.');
        }

        return $coordinates;
    }

    /**
     * @return array{latitude: float, longitude: float}
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->latitude === $other->latitude && $this->longitude === $other->longitude;
    }
}
