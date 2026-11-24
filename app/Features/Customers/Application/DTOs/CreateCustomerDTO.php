<?php

namespace App\Features\Customers\Application\DTOs;

use App\Features\Customers\Domain\ValueObjects\Coordinates;
use App\Features\Customers\Presentation\Http\Requests\StoreCustomerRequest;
use DateTimeImmutable;

final readonly class CreateCustomerDTO
{
    public function __construct(
        public int $userId,
        public ?string $phoneVerificationCode = null,
        public ?DateTimeImmutable $phoneVerifiedAt = null,
        public ?DateTimeImmutable $verificationCodeSentAt = null,
        public ?DateTimeImmutable $verificationCodeExpiresAt = null,
        public ?string $city = null,
        public ?string $district = null,
        public ?string $currentAddress = null,
        public ?Coordinates $coordinates = null,
    ) {}

    public static function fromRequest(StoreCustomerRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            userId: $validated['user_id'],
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
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        $data = [
            'user_id' => $this->userId,
            'phone_verification_code' => $this->phoneVerificationCode,
            'phone_verified_at' => $this->phoneVerifiedAt?->format('Y-m-d H:i:s'),
            'verification_code_sent_at' => $this->verificationCodeSentAt?->format('Y-m-d H:i:s'),
            'verification_code_expires_at' => $this->verificationCodeExpiresAt?->format('Y-m-d H:i:s'),
            'city' => $this->city,
            'district' => $this->district,
            'current_address' => $this->currentAddress,
            'latitude' => $this->coordinates?->latitude,
            'longitude' => $this->coordinates?->longitude,
        ];

        return array_filter($data, fn (mixed $value, string $key): bool => $value !== null || $key === 'user_id', ARRAY_FILTER_USE_BOTH);
    }
}
