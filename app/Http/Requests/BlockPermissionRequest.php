<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $block_id
 * @property int $permission_id
 */
class BlockPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'block_id' => 'required|integer',
            'permission_id' => 'required|integer',
        ];
    }
}
