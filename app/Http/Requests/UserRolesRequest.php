<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property array $roles
 */
class UserRolesRequest extends FormRequest
{
    /**
     * @return array<int>
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
