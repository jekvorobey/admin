<?php

namespace App\Http\Requests;

use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\PermissionDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property array[]|array $blockPermissions
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
            'blockPermissions' => 'required|array',
            'blockPermissions.*.block_id' => [
                'required',
                'integer',
                Rule::in(array_keys(BlockDto::allBlocks())),
            ],
            'blockPermissions.*.permission_id' => [
                'nullable',
                'integer',
                Rule::in(array_keys(PermissionDto::allPermissions())),
            ],
        ];
    }
}
