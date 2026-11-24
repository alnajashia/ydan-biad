<?php

namespace App\Features\Users\Presentation\Http\Requests;

use App\Features\Users\Domain\Enums\Gender;
use App\Features\Users\Domain\Enums\IdentityType;
use App\Features\Users\Domain\ValueObjects\PhoneNumber;
use App\Features\Users\Presentation\Http\Rules\PhoneNumberRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge(['phone' => PhoneNumber::normalize((string) $this->input('phone'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255', Rule::unique('users', 'full_name')],
            'phone' => ['required', 'string', new PhoneNumberRule, Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gender' => ['required', new Enum(Gender::class)],
            'identity_type' => ['required', new Enum(IdentityType::class)],
            'identity_number' => ['required', 'string', 'max:100', Rule::unique('users', 'identity_number')],
            'identity_front_image' => ['required', 'image', 'max:5120', 'mimes:png,jpg,jpeg,webp'],
            'identity_back_image' => ['required', 'image', 'max:5120', 'mimes:png,jpg,jpeg,webp'],
            'identity_expiry_date' => ['required', 'date'],
        ];
    }
}
