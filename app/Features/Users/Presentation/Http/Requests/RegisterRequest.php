<?php

namespace App\Features\Users\Presentation\Http\Requests;

use App\Features\Users\Domain\Enums\Gender;
use App\Features\Users\Domain\Enums\IdentityType;
use App\Features\Users\Domain\Enums\UserRole;
use App\Features\Users\Domain\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class RegisterRequest extends FormRequest
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
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:9', Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gender' => ['required',new Enum(Gender::class)],
            'status' => [new Enum(UserStatus::class)],
            'identity_type' => [new Enum(IdentityType::class)],
            'identity_number' => ['required','string', 'max:100', Rule::unique('users', 'identity_number')],
            'identity_front_image' => [ 'image', 'max:5120','mimes:png,jpg,jpeg,webp'],
            'identity_back_image' => ['image', 'max:5120','mimes:png,jpg,jpeg,webp'],
            'identity_expiry_date' => ['required', 'date'],
            'role' => ['nullable', new Enum(UserRole::class)],
        ];
    }
}
