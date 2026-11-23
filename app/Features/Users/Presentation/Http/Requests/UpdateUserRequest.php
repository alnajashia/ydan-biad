<?php

namespace App\Features\Users\Presentation\Http\Requests;

use App\Features\Users\Domain\Enums\Gender;
use App\Features\Users\Domain\Enums\IdentityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user');

        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($userId)],
            'password' => ['sometimes', 'string', 'min:8'],
            'gender' => ['nullable', new Enum(Gender::class)],
            'status_id' => ['nullable', 'integer', Rule::exists('statuses', 'id')],
            'identity_type' => ['nullable', new Enum(IdentityType::class)],
            'identity_number' => ['nullable', 'string', 'max:100', Rule::unique('users', 'identity_number')->ignore($userId)],
            'identity_front_image' => ['nullable', 'image', 'max:5120'],
            'identity_back_image' => ['nullable', 'image', 'max:5120'],
            'identity_expiry_date' => ['nullable', 'date'],
            'account_verified_at' => ['nullable', 'date'],
            'role_id' => ['nullable', 'integer', Rule::exists('roles', 'id')],
        ];
    }
}
