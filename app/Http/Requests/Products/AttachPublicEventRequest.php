<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class AttachPublicEventRequest extends FormRequest
{
    public function rules()
    {
        return [
            'public_events' => 'array|required',
            'public_events.*' => 'integer',
        ];
    }
}
