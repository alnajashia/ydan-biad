<?php

namespace App\Features\Customers\Presentation\Http\Resources;

use App\Features\Users\Presentation\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'phone_verification_code' => $this->phoneVerificationCode,
            'phone_verified_at' => $this->phoneVerifiedAt?->format(DATE_ATOM),
            'verification_code_sent_at' => $this->verificationCodeSentAt?->format(DATE_ATOM),
            'verification_code_expires_at' => $this->verificationCodeExpiresAt?->format(DATE_ATOM),
            'city' => $this->city,
            'district' => $this->district,
            'current_address' => $this->currentAddress,
            'latitude' => $this->coordinates?->latitude,
            'longitude' => $this->coordinates?->longitude,
            'user' => $this->user !== null ? new UserResource($this->user) : null,
            'created_at' => $this->createdAt?->format(DATE_ATOM),
            'updated_at' => $this->updatedAt?->format(DATE_ATOM),
        ];
    }
}
