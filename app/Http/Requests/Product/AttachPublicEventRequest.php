<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class AttachPublicEventRequest extends FormRequest
{
    public function rules()
    {
        return [
            'public_events' => 'array|sometimes',
        ];
    }
}