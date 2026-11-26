<?php

namespace App\Features\Users\Domain\Entities;

use App\Features\Customers\Domain\Entities\CustomerEntity;
use App\Features\Users\Domain\Enums\Gender;
use App\Features\Users\Domain\Enums\IdentityType;
use App\Features\Users\Domain\Enums\UserRole;
use App\Features\Users\Domain\Enums\UserStatus;
use App\Features\Users\Domain\ValueObjects\PhoneNumber;
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

  
}
