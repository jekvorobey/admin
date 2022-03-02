<?php

namespace App\Http\Requests\Landing;

use Illuminate\Foundation\Http\FormRequest;

class LandingActionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'active' => 'integer|required',
            'code' => 'string|required',
            'name' => 'string|required',
            'widgets' => 'string|required',
            'meta_title' => 'string|nullable',
            'meta_description' => 'string|nullable',
        ];
    }
}
