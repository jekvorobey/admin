<?php

namespace App\Http\Requests\Settings\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'code' => 'required|string',
            'active' => 'required|boolean',
            'is_postpaid' => 'required|boolean',
            'is_need_payment' => 'required|boolean',
            'settings' => 'nullable|array',
        ];
    }
}
