<?php

namespace App\Features\Users\Application\DTOs;

use App\Features\Users\Domain\Enums\Gender;
use App\Features\Users\Domain\Enums\IdentityType;
use App\Features\Users\Domain\Enums\UserRole;
use App\Features\Users\Domain\Enums\UserStatus;
use App\Features\Users\Domain\ValueObjects\PhoneNumber;
use App\Features\Users\Presentation\Http\Requests\StoreUserRequest;
use DateTimeImmutable;
use Illuminate\Http\UploadedFile;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $fullName,
        public PhoneNumber $phone,
        public string $password,
        public Gender $gender,
        public IdentityType $identityType,
        public string $identityNumber,
        public UploadedFile|string $identityFrontImage,
        public UploadedFile|string $identityBackImage,
        public DateTimeImmutable $identityExpiryDate,
        public UserStatus $status = UserStatus::Suspended,
        public ?DateTimeImmutable $accountVerifiedAt = null,
        public UserRole $role = UserRole::Customer,
    ) {}

    public static function fromRequest(StoreUserRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            fullName: $validated['full_name'],
            phone: PhoneNumber::fromString($validated['phone']),
            password: $validated['password'],
            gender: Gender::from($validated['gender']),
            identityType: IdentityType::from($validated['identity_type']),
            identityNumber: $validated['identity_number'],
            identityFrontImage: $validated['identity_front_image'],
            identityBackImage: $validated['identity_back_image'],
            identityExpiryDate: new DateTimeImmutable($validated['identity_expiry_date']),
            status: isset($validated['status']) ? UserStatus::from($validated['status']) : UserStatus::Suspended,
            accountVerifiedAt: isset($validated['account_verified_at']) ? new DateTimeImmutable($validated['account_verified_at']) : null,
            role: isset($validated['role']) ? UserRole::from($validated['role']) : UserRole::Customer,
        );
    }

    /**
     * Return a copy with the uploaded images replaced by their stored paths.
     */
    public function withStoredImages(string $frontImage, string $backImage): self
    {
        return new self(
            fullName: $this->fullName,
            phone: $this->phone,
            password: $this->password,
            gender: $this->gender,
            identityType: $this->identityType,
            identityNumber: $this->identityNumber,
            identityFrontImage: $frontImage,
            identityBackImage: $backImage,
            identityExpiryDate: $this->identityExpiryDate,
            status: $this->status,
            accountVerifiedAt: $this->accountVerifiedAt,
            role: $this->role,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'phone' => $this->phone->value,
            'password' => $this->password,
            'gender' => $this->gender->value,
            'status' => $this->status->value,
            'identity_type' => $this->identityType->value,
            'identity_number' => $this->identityNumber,
            'identity_front_image' => $this->identityFrontImage,
            'identity_back_image' => $this->identityBackImage,
            'identity_expiry_date' => $this->identityExpiryDate->format('Y-m-d'),
            'account_verified_at' => $this->accountVerifiedAt?->format('Y-m-d H:i:s'),
            'role' => $this->role->value,
        ];
    }
}
