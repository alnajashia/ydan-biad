<?php

namespace App\Features\Users\Application\DTOs;

use App\Features\Users\Domain\Enums\Gender;
use App\Features\Users\Domain\Enums\IdentityType;
use App\Features\Users\Domain\Enums\UserRole;
use App\Features\Users\Domain\Enums\UserStatus;
use App\Features\Users\Domain\ValueObjects\PhoneNumber;
use App\Features\Users\Presentation\Http\Requests\UpdateUserRequest;
use DateTimeImmutable;
use Illuminate\Http\UploadedFile;

final readonly class UpdateUserDTO
{
    public function __construct(
        public ?string $fullName = null,
        public ?PhoneNumber $phone = null,
        public ?string $password = null,
        public ?Gender $gender = null,
        public ?UserStatus $status = null,
        public ?IdentityType $identityType = null,
        public ?string $identityNumber = null,
        public UploadedFile|string|null $identityFrontImage = null,
        public UploadedFile|string|null $identityBackImage = null,
        public ?DateTimeImmutable $identityExpiryDate = null,
        public ?DateTimeImmutable $accountVerifiedAt = null,
        public ?UserRole $role = null,
    ) {}

    public static function fromRequest(UpdateUserRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            fullName: $validated['full_name'] ?? null,
            phone: isset($validated['phone']) ? PhoneNumber::fromString($validated['phone']) : null,
            password: $validated['password'] ?? null,
            gender: isset($validated['gender']) ? Gender::from($validated['gender']) : null,
            status: isset($validated['status']) ? UserStatus::from($validated['status']) : null,
            identityType: isset($validated['identity_type']) ? IdentityType::from($validated['identity_type']) : null,
            identityNumber: $validated['identity_number'] ?? null,
            identityFrontImage: $validated['identity_front_image'] ?? null,
            identityBackImage: $validated['identity_back_image'] ?? null,
            identityExpiryDate: isset($validated['identity_expiry_date']) ? new DateTimeImmutable($validated['identity_expiry_date']) : null,
            accountVerifiedAt: isset($validated['account_verified_at']) ? new DateTimeImmutable($validated['account_verified_at']) : null,
            role: isset($validated['role']) ? UserRole::from($validated['role']) : null,
        );
    }

    /**
     * Return a copy with any uploaded images replaced by their stored paths.
     */
    public function withStoredImages(?string $frontImage, ?string $backImage): self
    {
        return new self(
            fullName: $this->fullName,
            phone: $this->phone,
            password: $this->password,
            gender: $this->gender,
            status: $this->status,
            identityType: $this->identityType,
            identityNumber: $this->identityNumber,
            identityFrontImage: $frontImage ?? $this->identityFrontImage,
            identityBackImage: $backImage ?? $this->identityBackImage,
            identityExpiryDate: $this->identityExpiryDate,
            accountVerifiedAt: $this->accountVerifiedAt,
            role: $this->role,
        );
    }

    /**
     * Only the fields that were actually sent are returned.
     *
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        $data = [];

        if ($this->fullName !== null) {
            $data['full_name'] = $this->fullName;
        }

        if ($this->phone !== null) {
            $data['phone'] = $this->phone->value;
        }

        if ($this->password !== null) {
            $data['password'] = $this->password;
        }

        if ($this->gender !== null) {
            $data['gender'] = $this->gender->value;
        }

        if ($this->status !== null) {
            $data['status'] = $this->status->value;
        }

        if ($this->identityType !== null) {
            $data['identity_type'] = $this->identityType->value;
        }

        if ($this->identityNumber !== null) {
            $data['identity_number'] = $this->identityNumber;
        }

        if ($this->identityFrontImage !== null) {
            $data['identity_front_image'] = $this->identityFrontImage;
        }

        if ($this->identityBackImage !== null) {
            $data['identity_back_image'] = $this->identityBackImage;
        }

        if ($this->identityExpiryDate !== null) {
            $data['identity_expiry_date'] = $this->identityExpiryDate->format('Y-m-d');
        }

        if ($this->accountVerifiedAt !== null) {
            $data['account_verified_at'] = $this->accountVerifiedAt->format('Y-m-d H:i:s');
        }

        if ($this->role !== null) {
            $data['role'] = $this->role->value;
        }

        return $data;
    }
}
