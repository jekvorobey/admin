<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property array[]|array $roles
 */
class UserRolesAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'roles' => 'required|array',
            'roles.*' => [
                'required',
                'integer',
            ],
        ];
    }
}
