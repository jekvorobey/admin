<?php

namespace App\Http\Requests\Landing;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property bool $active
 * @property string $code
 * @property string $name
 * @property string $content
 * @property string $meta_title
 * @property string $meta_description
 * @property string $hash
 */
class LandingActionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'active' => 'boolean|required',
            'code' => 'string|required',
            'name' => 'string|required',
            'content' => 'string|required',
            'meta_title' => 'string|nullable',
            'meta_description' => 'string|nullable',
            'hash' => 'string|nullable',
        ];
    }
}
