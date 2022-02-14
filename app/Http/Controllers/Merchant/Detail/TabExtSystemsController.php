<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\Integration\ExtSystemDriver;
use MerchantManagement\Dto\Integration\ExtSystemDto;
use MerchantManagement\Dto\Integration\IntegrationDto;
use MerchantManagement\Dto\Integration\IntegrationType;
use MerchantManagement\Services\MerchantIntegrationService\MerchantIntegrationService;
use MerchantManagement\Services\MerchantService\MerchantService;

class TabExtSystemsController extends Controller
{
    public function load(
        int $merchantId,
        MerchantService $merchantService,
        MerchantIntegrationService $merchantIntegrationService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $restQuery = $merchantIntegrationService->newQuery()->setFilter('merchant_id', $merchantId);
        $extSystem = $merchantIntegrationService->extSystems($restQuery)->first();
        $merchantSetting = $merchantService->getSetting($merchantId, 'moy_sklad_import_price_type_name')->first();
        $extSystemsOptions = [
            ExtSystemDriver::driverById(ExtSystemDriver::DRIVER_1C),
            ExtSystemDriver::driverById(ExtSystemDriver::DRIVER_MOY_SKLAD),
        ];
        $host = '';
        if ($extSystem) {
            $driver = (int) $extSystem->driver;
            switch ($driver) {
                case ExtSystemDriver::DRIVER_1C:
                    $host = config('common-lib.integration1CHost');
                    break;
                case ExtSystemDriver::DRIVER_MOY_SKLAD:
                    $host = config('common-lib.integrationMoyskladHost');
            }
        }

        return response()->json([
            'extSystem' => $extSystem,
            'merchantSetting' => $merchantSetting,
            'extSystemsOptions' => $extSystemsOptions,
            'host' => $host,
        ]);
    }

    public function create(
        int $merchantId,
        Request $request,
        MerchantService $merchantService,
        MerchantIntegrationService $merchantIntegrationService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $this->validate($request, [
            'token' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->input('driver') === ExtSystemDriver::DRIVER_MOY_SKLAD && !$request->input('login');
                }),
            ],
            'login' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->input('driver') === ExtSystemDriver::DRIVER_MOY_SKLAD && !$request->input('token');
                }),
            ],
            'password' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->input('driver') === ExtSystemDriver::DRIVER_MOY_SKLAD && $request->input('login');
                }),
            ],
            'settingValue' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->input('driver') === ExtSystemDriver::DRIVER_MOY_SKLAD;
                }),
            ],
            'driver' => [
                'required',
                'integer',
                Rule::in(array_keys(ExtSystemDriver::allDrivers())),
            ],
        ]);

        $merchant = $merchantService->merchant($merchantId);
        $connectionParams = [];
        switch ($data['driver']) {
            case ExtSystemDriver::DRIVER_1C:
                $code = $merchant->code;
                $name = $merchant->legal_name;
                $connectionParams = [
                    'login' => $merchant->code . '_merchant',
                    'password' => Str::random(10),
                ];
                break;
            case ExtSystemDriver::DRIVER_MOY_SKLAD:
                $code = 'moysklad';
                $name = 'МойСклад';
                $connectionParams = [
                    'token' => $data['token'],
                    'login' => $data['login'],
                    'password' => $data['password'],
                ];
                break;
            default:
                $code = $merchant->code;
                $name = $merchant->legal_name;
        }

        $extSystem = new ExtSystemDto([
            'merchant_id' => $merchantId,
            'code' => $code,
            'name' => $name,
            'driver' => $data['driver'],
            'connection_params' => $connectionParams,
        ]);

        $extSystemId = $merchantIntegrationService->createExtSystem($extSystem);
        if ($data['driver'] === ExtSystemDriver::DRIVER_MOY_SKLAD) {
            foreach ($this->moyskladIntegrationsData() as $integrationData) {
                $integrationDto = new IntegrationDto($integrationData);
                $merchantIntegrationService->createIntegration($extSystemId, $integrationDto);
            }
            $merchantService->setSetting($merchantId, 'moy_sklad_import_price_type_name', $data['settingValue']);
        }

        return response()->json([]);
    }

    public function update(
        int $extSystemId,
        Request $request,
        MerchantService $merchantService,
        MerchantIntegrationService $merchantIntegrationService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $this->validate($request, [
            'merchantId' => 'required|int',
            'token' => 'required_without:login',
            'login' => 'required_without:token',
            'password' => 'required_with:login',
            'settingValue' => 'required|string',
        ]);
        $connectionParams = [
            'token' => $data['token'],
            'login' => $data['login'],
            'password' => $data['password'],
        ];
        $extSystem = new ExtSystemDto([
            'merchant_id' => $data['merchantId'],
            'connection_params' => $connectionParams,
        ]);

        $merchantIntegrationService->updateExtSystem($extSystemId, $extSystem);
        $merchantService->setSetting($data['merchantId'], 'moy_sklad_import_price_type_name', $data['settingValue']);

        return response()->json([]);
    }

    private function moyskladIntegrationsData(): array
    {
        return [
            [
                'name' => 'Импорт остатков',
                'active' => true,
                'type' => IntegrationType::TYPE_STOCK_IMPORT,
                'params' => [
                    'type' => 'changes',
                    'period' => '1',
                    'logsLifetime' => '5',
                ],
            ],
            [
                'name' => 'Импорт цен',
                'active' => true,
                'type' => IntegrationType::TYPE_PRICE_IMPORT,
                'params' => [
                    'type' => 'changes',
                    'period' => '1',
                    'logsLifetime' => '5',
                ],
            ],
        ];
    }
}
