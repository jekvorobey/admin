<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
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
    /**
     * @param  PackageService  $packageService
     * @return JsonResponse
     */
    public function list(PackageService $packageService): JsonResponse
    {
        return response()->json([
            'packages' => $packageService->packages($packageService->newQuery()
                ->setFilter('type', PackageType::TYPE_BOX)
                ->addFields(PackageDto::entity(), 'id', 'name'))->keyBy('id')
        ]);
    }
}
