<?php

namespace App\Features\Users\Presentation\Http\Resources;

use App\Features\Customers\Presentation\Http\Resources\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'full_name' => $this->fullName,
            'phone' => $this->phone->value,
            'gender' => $this->gender->value,
            'status' => $this->status->value,
            'identity_type' => $this->identityType->value,
            'identity_number' => $this->identityNumber,
            'identity_front_image' => $this->identityFrontImage,
            'identity_back_image' => $this->identityBackImage,
            'identity_expiry_date' => $this->identityExpiryDate->format('Y-m-d'),
            'account_verified_at' => $this->accountVerifiedAt?->format(DATE_ATOM),
            'role' => $this->role->value,
            'customer' => $this->customer !== null ? new CustomerResource($this->customer) : null,
            'created_at' => $this->createdAt?->format(DATE_ATOM),
            'updated_at' => $this->updatedAt?->format(DATE_ATOM),
        ];
    }
}
