<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $name
 */
class BlockPermissionRequest extends FormRequest
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
        $rules = [
            'blocks' => 'required',
        ];
        foreach($this->request->get('blocks') as $key => $val) {
            $rules['state.'.$key] = 'required|integer';
        }

        return $rules;
    }
}
