<?php

namespace App\Http\Requests\Landing;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }
}
