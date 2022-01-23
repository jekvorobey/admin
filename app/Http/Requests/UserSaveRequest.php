<?php

namespace App\Http\Requests;

use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\RoleDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $id
 * @property string $login
 * @property string $password
 * @property string $infinity_sip_extension
 * @property array[]|array $fronts
 * @property array[]|array $roles
 */
class UserSaveRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'id' => 'nullable|integer',
            'login' => 'required',
            'front' => ['required_without:fronts', Rule::in(array_keys(Front::allFronts()))],
            'fronts' => 'required_without:front|array',
            'fronts.*' => Rule::in(array_keys(Front::allFronts())),
            'roles' => 'required|array',
            'roles.*' => Rule::in(array_keys(RoleDto::roles())),
            'password' => 'required_without:id',
            'infinity_sip_extension' => 'nullable|string',
        ];
    }
}
