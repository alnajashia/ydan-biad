<?php

namespace App\Features\Customers\Application\DTOs;

use App\Features\Customers\Domain\ValueObjects\Coordinates;
use App\Features\Customers\Presentation\Http\Requests\UpdateCustomerRequest;
use DateTimeImmutable;

final readonly class UpdateCustomerDTO
{
    public function __construct(
        public ?int $userId = null,
        public ?string $phoneVerificationCode = null,
        public ?DateTimeImmutable $phoneVerifiedAt = null,
        public ?DateTimeImmutable $verificationCodeSentAt = null,
        public ?DateTimeImmutable $verificationCodeExpiresAt = null,
        public ?string $city = null,
        public ?string $district = null,
        public ?string $currentAddress = null,
        public ?Coordinates $coordinates = null,
    ) {}

    public static function fromRequest(UpdateCustomerRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            userId: $validated['user_id'] ?? null,
            phoneVerificationCode: $validated['phone_verification_code'] ?? null,
            phoneVerifiedAt: isset($validated['phone_verified_at']) ? new DateTimeImmutable($validated['phone_verified_at']) : null,
            verificationCodeSentAt: isset($validated['verification_code_sent_at']) ? new DateTimeImmutable($validated['verification_code_sent_at']) : null,
            verificationCodeExpiresAt: isset($validated['verification_code_expires_at']) ? new DateTimeImmutable($validated['verification_code_expires_at']) : null,
            city: $validated['city'] ?? null,
            district: $validated['district'] ?? null,
            currentAddress: $validated['current_address'] ?? null,
            coordinates: Coordinates::tryFromFloats(
                isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            ),
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

        if ($this->userId !== null) {
            $data['user_id'] = $this->userId;
        }

        if ($this->phoneVerificationCode !== null) {
            $data['phone_verification_code'] = $this->phoneVerificationCode;
        }

        if ($this->phoneVerifiedAt !== null) {
            $data['phone_verified_at'] = $this->phoneVerifiedAt->format('Y-m-d H:i:s');
        }

        if ($this->verificationCodeSentAt !== null) {
            $data['verification_code_sent_at'] = $this->verificationCodeSentAt->format('Y-m-d H:i:s');
        }

        if ($this->verificationCodeExpiresAt !== null) {
            $data['verification_code_expires_at'] = $this->verificationCodeExpiresAt->format('Y-m-d H:i:s');
        }

        if ($this->city !== null) {
            $data['city'] = $this->city;
        }

        if ($this->district !== null) {
            $data['district'] = $this->district;
        }

        if ($this->currentAddress !== null) {
            $data['current_address'] = $this->currentAddress;
        }

        if ($this->coordinates !== null) {
            $data['latitude'] = $this->coordinates->latitude;
            $data['longitude'] = $this->coordinates->longitude;
        }

        return $data;
    }
}
