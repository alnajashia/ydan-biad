<?php

namespace App\Features\Users\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $customerId = $this->route('customer');

        return [
            'user_id' => ['sometimes', 'integer', Rule::exists('users', 'id'), Rule::unique('customers', 'user_id')->ignore($customerId)],
            'phone_verification_code' => ['nullable', 'string', 'max:20'],
            'phone_verified_at' => ['nullable', 'date'],
            'verification_code_sent_at' => ['nullable', 'date'],
            'verification_code_expires_at' => ['nullable', 'date'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'current_address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
