<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int[] $ids
 */
class BanUsersRequest extends FormRequest
{
    /**
     * @return array<int>
     */
    public function rules(): array
    {
        return [
            'ids' => 'required|array',
            'ids.*' => [
                'required',
                'integer',
            ],
        ];
    }
}
