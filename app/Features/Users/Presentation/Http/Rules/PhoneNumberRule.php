<?php

namespace App\Features\Users\Presentation\Http\Rules;

use App\Features\Users\Domain\ValueObjects\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumberRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || PhoneNumber::tryFromString($value) === null) {
            $fail('The :attribute must be a valid 9-digit phone number starting with 7.');
        }
    }
}
