<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedirectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Эти правила дублируются в ibt-cms (RedirectRequest).
     * Поэтому при добавлении обработки CMS исключений отсюда можно будет удалить.
     *
     * @return array
     */
    public function rules()
    {
        $regex = '/^([\/\w \.-]*)*\/?$/i';
        $defaultRules = "string|required|regex:{$regex}";
        return [
            'id' => 'nullable|int',
            'from' => $defaultRules,
            'to' => "$defaultRules|different:from",
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
