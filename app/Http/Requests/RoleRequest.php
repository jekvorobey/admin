<?php

namespace App\Http\Requests;

use Greensight\CommonMsa\Dto\Front;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $id
 * @property string $name
 * @property int $front
 */
class RoleRequest extends FormRequest
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
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'front' => [
                'required',
                'integer',
                Rule::in(array_keys(Front::allFronts())),
            ],
        ];
    }
}
