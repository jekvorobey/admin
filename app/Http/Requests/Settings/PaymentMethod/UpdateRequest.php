<?php

namespace App\Http\Requests\Settings\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'code' => 'required|string',
            'active' => 'required|boolean',
            'is_postpaid' => 'required|boolean',
        ];
    }
}
