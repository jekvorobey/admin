<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedirectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'nullable|int',
            'from' => 'string|required|regex:/^([\/\w \.-]*)*\/?$/i',
            'to' => 'string|required|regex:/^([\/\w \.-]*)*\/?$/i|different:from',
        ];
    }

    public function messages()
    {
        return [
            'from.regex' => 'Ссылка не корректна',
            'to.regex' => 'Ссылка не корректна',
            'to.different' => 'Поля должны различаться',
        ];
    }
}
