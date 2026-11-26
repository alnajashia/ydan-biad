<?php

namespace App\Features\Users\Domain\Entities;

use App\Features\Customers\Domain\Entities\CustomerEntity;
use App\Features\Users\Domain\Enums\Gender;
use App\Features\Users\Domain\Enums\IdentityType;
use App\Features\Users\Domain\Enums\UserRole;
use App\Features\Users\Domain\Enums\UserStatus;
use App\Features\Users\Domain\ValueObjects\PhoneNumber;
use App\Features\Users\Infrastructure\Persistence\Models\User;
use DateTimeImmutable;

final readonly class UserEntity
{
    public function __construct(
        public ?int $id,
        public string $fullName,
        public PhoneNumber $phone,
        public Gender $gender,
        public UserStatus $status,
        public IdentityType $identityType,
        public string $identityNumber,
        public string $identityFrontImage,
        public string $identityBackImage,
        public DateTimeImmutable $identityExpiryDate,
        public ?DateTimeImmutable $accountVerifiedAt,
        public UserRole $role,
        public ?CustomerEntity $customer = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Only an active account may authenticate or use the API.
     */
    public function canAuthenticate(): bool
    {
        return $this->isActive();
    }

    // public static function fromModel(User $user): self
    // {
    //     // bool $withCustomer = true
    //     // $customer = null;

    //     // if ($withCustomer && $user->relationLoaded('customer') && $user->customer !== null) {
    //     //     $customer = CustomerEntity::fromModel($user->customer, false);
    //     // }//the user kind(like customer) should be embbeded in the usecase or service not here

    //     return new self(
    //         id: $user->id,
    //         fullName: $user->full_name,
    //         phone: PhoneNumber::fromString($user->phone),
    //         gender: $user->gender,
    //         status: $user->status,
    //         identityType: $user->identity_type,
    //         identityNumber: $user->identity_number,
    //         identityFrontImage: $user->identity_front_image,
    //         identityBackImage: $user->identity_back_image,
    //         identityExpiryDate: $user->identity_expiry_date->toDateTimeImmutable(),
    //         accountVerifiedAt: $user->account_verified_at?->toDateTimeImmutable(),
    //         role: $user->role,
    //         // customer: $customer,
    //         createdAt: $user->created_at?->toDateTimeImmutable(),
    //         updatedAt: $user->updated_at?->toDateTimeImmutable(),
    //     );
    // }
}
