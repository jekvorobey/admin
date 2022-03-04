<?php

namespace App\Http\Requests\Content\Seo;

use Illuminate\Foundation\Http\FormRequest;

class SeoUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'code' => 'required|string',
            'meta_title' => 'required|string',
            'meta_description' => 'required|string',
        ];
    }
}
