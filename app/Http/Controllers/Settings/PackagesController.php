<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
//use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Store\Dto\Package\PackageDto;
use Greensight\Store\Dto\Package\PackageType;
use Greensight\Store\Services\PackageService\PackageService;
use Illuminate\Http\JsonResponse;

/**
 * Class PackagesController
 * @package App\Http\Controllers\Settings
 */
class PackagesController extends Controller
{
    public function list(PackageService $packageService): JsonResponse
    {
        // Нужен доступ при редактировании заказа
        // $this->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        return response()->json([
            'packages' => $packageService->packages($packageService->newQuery()
                ->setFilter('type', PackageType::TYPE_BOX)
                ->addFields(PackageDto::entity(), 'id', 'name'))->keyBy('id'),
        ]);
    }
}
