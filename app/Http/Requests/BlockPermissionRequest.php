<?php

namespace App\Http\Requests;

use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\PermissionDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $role_id
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
            'role_id' => [
                'required_without_all:block_id, permission_id',
                'integer',
            ],
            'block_id' => [
                'required_without:role_id',
                'integer',
                Rule::in(array_keys(BlockDto::allBlocks())),
            ],
            'permission_id' => [
                'required_without:role_id',
                'integer',
                Rule::in(array_keys(PermissionDto::allPermissions())),
            ],
        ];
    }
}
