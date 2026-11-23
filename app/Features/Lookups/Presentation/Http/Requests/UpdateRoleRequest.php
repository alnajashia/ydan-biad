<?php

namespace App\Features\Lookups\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
        $roleId = $this->route('role');

        return [
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($roleId)],
            'value' => ['sometimes', 'boolean'],
        ];
    }
}
