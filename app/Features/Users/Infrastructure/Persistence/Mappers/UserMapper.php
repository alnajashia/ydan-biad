<?php

namespace App\Features\Users\Infrastructure\Persistence\Mappers;

use App\Features\Users\Domain\Entities\UserEntity;
use App\Features\Users\Domain\ValueObjects\PhoneNumber;
use App\Features\Users\Infrastructure\Persistence\Models\User;

final class UserMapper
{
    public static function toEntity(User $user): UserEntity
    {
        return new UserEntity(
            id: $user->id,
            fullName: $user->full_name,
            phone: PhoneNumber::fromString($user->phone),
            gender: $user->gender,
            status: $user->status,
            identityType: $user->identity_type,
            identityNumber: $user->identity_number,
            identityFrontImage: $user->identity_front_image,
            identityBackImage: $user->identity_back_image,
            identityExpiryDate: $user->identity_expiry_date->toDateTimeImmutable(),
            accountVerifiedAt: $user->account_verified_at?->toDateTimeImmutable(),
            role: $user->role,
        );
    }
}