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
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'gender' => $this->gender?->value,
            'status' => $this->status?->value,
            'identity_type' => $this->identity_type?->value,
            'identity_number' => $this->identity_number,
            'identity_front_image' => $this->identity_front_image,
            'identity_back_image' => $this->identity_back_image,
            'identity_expiry_date' => $this->identity_expiry_date?->toDateString(),
            'account_verified_at' => $this->account_verified_at?->toISOString(),
            'role' => $this->role?->value,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
