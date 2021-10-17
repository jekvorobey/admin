<?php

namespace App\Traits;

use Greensight\CommonMsa\Dto\PermissionDto;
use Greensight\CommonMsa\Services\TokenBuilder\TokenBuilder;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use Illuminate\Support\Collection;

trait UserPermissions
{
    private function blockPermissions(): Collection
    {
        $token = resolve(TokenStore::class)->token();

        return collect(resolve(TokenBuilder::class)->decodeJwt($token)->blockPermissions);
    }

    public function canView($blockId): bool
    {
        $blockPermissions = $this->blockPermissions();
        $viewDisallow = $blockPermissions->where('block_id', $blockId)->where('permission_id', PermissionDto::PERMISSION_VIEW)->isEmpty();
        $updateDisallow = $blockPermissions->where('block_id', $blockId)->where('permission_id', PermissionDto::PERMISSION_VIEW)->isEmpty();

        if ($viewDisallow || $updateDisallow) {
            abort(403);
        }

        return true;
    }

    public function canUpdate($blockId): bool
    {
        $blockPermissions = $this->blockPermissions();

        if ($blockPermissions->where('block_id', $blockId)->where('permission_id', PermissionDto::PERMISSION_UPDATE)->isEmpty()) {
            abort(403);
        }

        return true;
    }
}
