<?php

namespace App\Features\Customers\Domain\Entities;

use App\Features\Customers\Domain\ValueObjects\Coordinates;
use App\Features\Customers\Infrastructure\Persistence\Models\Customer;
use App\Features\Users\Domain\Entities\UserEntity;
use DateTimeImmutable;

final readonly class CustomerEntity
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public ?string $phoneVerificationCode = null,
        public ?DateTimeImmutable $phoneVerifiedAt = null,
        public ?DateTimeImmutable $verificationCodeSentAt = null,
        public ?DateTimeImmutable $verificationCodeExpiresAt = null,
        public ?string $city = null,
        public ?string $district = null,
        public ?string $currentAddress = null,
        public ?Coordinates $coordinates = null,
        public ?UserEntity $user = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function fromModel(Customer $customer, bool $withUser = true): self
    {
        $coordinates = null;

        if ($customer->latitude !== null && $customer->longitude !== null) {
            $coordinates = Coordinates::fromFloats((float) $customer->latitude, (float) $customer->longitude);
        }

        $user = null;

        if ($withUser && $customer->relationLoaded('user') && $customer->user !== null) {
            $user = UserEntity::fromModel($customer->user, false);
        }

        return new self(
            id: $customer->id,
            userId: $customer->user_id,
            phoneVerificationCode: $customer->phone_verification_code,
            phoneVerifiedAt: $customer->phone_verified_at?->toDateTimeImmutable(),
            verificationCodeSentAt: $customer->verification_code_sent_at?->toDateTimeImmutable(),
            verificationCodeExpiresAt: $customer->verification_code_expires_at?->toDateTimeImmutable(),
            city: $customer->city,
            district: $customer->district,
            currentAddress: $customer->current_address,
            coordinates: $coordinates,
            user: $user,
            createdAt: $customer->created_at?->toDateTimeImmutable(),
            updatedAt: $customer->updated_at?->toDateTimeImmutable(),
        );
    }
}
