<?php

use App\Features\Customers\Domain\ValueObjects\Coordinates;
use App\Features\Users\Domain\ValueObjects\PhoneNumber;
use InvalidArgumentException;

test('phone numbers normalize to the 9-digit local form', function () {
    expect(PhoneNumber::fromString('777123456')->value)->toBe('777123456');
    expect(PhoneNumber::fromString('+967777123456')->value)->toBe('777123456');
    expect(PhoneNumber::fromString('967777123456')->value)->toBe('777123456');
    expect(PhoneNumber::fromString('0777123456')->value)->toBe('777123456');
    expect(PhoneNumber::fromString('777 123 456')->value)->toBe('777123456');
});

test('phone numbers reject invalid values', function () {
    expect(PhoneNumber::tryFromString('12345'))->toBeNull();
    expect(PhoneNumber::tryFromString('77712345'))->toBeNull();
    expect(PhoneNumber::tryFromString('677123456'))->toBeNull();
    expect(fn () => PhoneNumber::fromString('not-a-phone'))->toThrow(InvalidArgumentException::class);
});

test('coordinates accept valid ranges and reject the rest', function () {
    expect(Coordinates::fromFloats(15.3694, 44.1910)->toArray())
        ->toBe(['latitude' => 15.3694, 'longitude' => 44.1910]);
    expect(Coordinates::tryFromFloats(91.0, 0.0))->toBeNull();
    expect(Coordinates::tryFromFloats(0.0, 181.0))->toBeNull();
    expect(Coordinates::tryFromFloats(null, 44.0))->toBeNull();
});
