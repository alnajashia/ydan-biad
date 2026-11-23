<?php

namespace App\Features\Lookups\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStatusRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', Rule::unique('statuses', 'name')],
            'value' => ['required', 'boolean'],
        ];
    }
}
