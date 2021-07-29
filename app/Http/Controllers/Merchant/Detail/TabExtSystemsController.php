<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use MerchantManagement\Dto\Integration\ExtSystemDriver;
use MerchantManagement\Dto\Integration\ExtSystemDto;
use MerchantManagement\Services\MerchantIntegrationService\MerchantIntegrationService;
use MerchantManagement\Services\MerchantService\MerchantService;

class TabExtSystemsController extends Controller
{
    public function load(int $merchantId, MerchantIntegrationService $merchantIntegrationService): JsonResponse
    {
        $restQuery = $merchantIntegrationService->newQuery()
            ->setFilter('merchant_id', $merchantId)
            ->setFilter('driver', ExtSystemDriver::DRIVER_1C);

        $extSystem = $merchantIntegrationService->extSystems($restQuery)->first();

        return response()->json([
            'extSystem' => $extSystem,
            'host' => config('common-lib.integration1CHost'),
        ]);
    }

    public function create(
        int $merchantId,
        MerchantService $merchantService,
        MerchantIntegrationService $merchantIntegrationService
    ): JsonResponse {
        $merchant = $merchantService->merchant($merchantId);

        $extSystem = new ExtSystemDto([
            'merchant_id' => $merchantId,
            'code' => $merchant->code,
            'name' => $merchant->legal_name,
            'driver' => ExtSystemDriver::DRIVER_1C,
            'connection_params' => [
                'login' => $merchant->code . '_merchant',
                'password' => Str::random(10),
            ],
        ]);

        $merchantIntegrationService->createExtSystem($extSystem);

        return response()->json([]);
    }
}
