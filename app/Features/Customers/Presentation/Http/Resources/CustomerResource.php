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
            'user_id' => $this->user_id,
            'phone_verification_code' => $this->phone_verification_code,
            'phone_verified_at' => $this->phone_verified_at?->toISOString(),
            'verification_code_sent_at' => $this->verification_code_sent_at?->toISOString(),
            'verification_code_expires_at' => $this->verification_code_expires_at?->toISOString(),
            'city' => $this->city,
            'district' => $this->district,
            'current_address' => $this->current_address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
