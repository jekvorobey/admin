<?php

namespace App\Http\Requests\Discount;

use Illuminate\Foundation\Http\FormRequest;

class CopyAndDeleteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ids' => 'array|required',
            'ids.*' => 'integer|required',
        ];
    }
}
