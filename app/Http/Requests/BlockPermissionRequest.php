<?php

namespace App\Http\Requests;

use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\PermissionDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'block_id' => [
                'required',
                'integer',
                Rule::in(array_keys(BlockDto::allBlocks())),
            ],
            'permission_id' => [
                'required',
                'integer',
                Rule::in(array_keys(PermissionDto::allPermissions())),
            ],
        ];
    }
}
