<?php

namespace App\Http\Requests;

use Greensight\CommonMsa\Dto\Front;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $id
 * @property string $login
 * @property string $login_email
 * @property string $password
 * @property string $infinity_sip_extension
 * @property string $first_name
 * @property string $last_name
 * @property string $middle_name
 * @property string $phone
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
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|regex:/^\+7\d{10}$/',
            'login' => 'required_with:phone|regex:/^\+7\d{10}$/',
            'login_email' => 'required_with:email|email',
            'password' => 'nullable|string',
            'fronts' => 'required_without:front|array',
            'fronts.*' => [
                'required',
                'integer',
                Rule::in(array_keys(Front::allFronts())),
            ],
            'roles' => 'required|array',
            'roles.*' => [
                'required',
                'integer',
            ],
            'infinity_sip_extension' => 'nullable|string',
        ];
    }
}
