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
        $regexWithoutQueryBody = '((https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6}))?(([\/\w \.-]*)*)?';
        $regexQueryBody = '((?:&?[^=&]*=[^=&]*)*)?';

        $regexFrom = "/^$regexWithoutQueryBody$/i";
        $regexTo = "/^{$regexWithoutQueryBody}{$regexQueryBody}$/i";
        $defaultRules = 'string|required';

        return [
            'id' => 'nullable|int',
            'from' => "$defaultRules|regex:{$regexFrom}",
            'to' => "$defaultRules|different:from|regex:{$regexTo}",
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
