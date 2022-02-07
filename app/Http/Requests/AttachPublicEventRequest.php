<?php

namespace App\Http\Requests;

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
