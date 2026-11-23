<?php

namespace App\Features\Lookups\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatusRequest extends FormRequest
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
        $statusId = $this->route('status');

        return [
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('statuses', 'name')->ignore($statusId)],
            'value' => ['sometimes', 'boolean'],
        ];
    }
}
