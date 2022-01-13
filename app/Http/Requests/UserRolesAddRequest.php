<?php

namespace App\Http\Requests;

use Greensight\CommonMsa\Dto\RoleDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                Rule::in(array_keys(RoleDto::roles())),
            ],
        ];
    }
}
