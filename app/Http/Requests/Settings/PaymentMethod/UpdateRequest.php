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
            'is_apply_discounts' => 'required|boolean',
            'button_text' => 'nullable|string',
            'min_available_price' => 'nullable|numeric',
            'max_available_price' => 'nullable|numeric',
            'settings' => 'nullable|array',
        ];
    }
}
