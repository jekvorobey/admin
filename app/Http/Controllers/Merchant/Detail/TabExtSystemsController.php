<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantIntegration\IntegrationRequest;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use MerchantManagement\Dto\Integration\ExtSystemDriver;
use MerchantManagement\Dto\Integration\ExtSystemDto;
use MerchantManagement\Dto\Integration\IntegrationDto;
use MerchantManagement\Dto\Integration\IntegrationType;
use MerchantManagement\Services\MerchantIntegrationService\MerchantIntegrationService;
use MerchantManagement\Services\MerchantService\MerchantService;

class TabExtSystemsController extends Controller
{
    protected const MERCHANT_SETTING_PRICE_NAME = 'moy_sklad_import_price_type_name';
    protected const MERCHANT_SETTING_ORGANIZATION_NAME = 'moy_sklad_order_organization';

    public function load(
        int $merchantId,
        MerchantService $merchantService,
        MerchantIntegrationService $merchantIntegrationService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $restQuery = $merchantIntegrationService->newQuery()->setFilter('merchant_id', $merchantId);
        $extSystem = $merchantIntegrationService->extSystems($restQuery)->first();
        $merchantPriceSetting = $merchantService->getSetting($merchantId, self::MERCHANT_SETTING_PRICE_NAME)->first();
        $merchantOrganizationSetting = $merchantService->getSetting($merchantId, self::MERCHANT_SETTING_ORGANIZATION_NAME)->first();
        $extSystemsOptions = [
            ExtSystemDriver::driverById(ExtSystemDriver::DRIVER_1C),
            ExtSystemDriver::driverById(ExtSystemDriver::DRIVER_MOY_SKLAD),
            ExtSystemDriver::driverById(ExtSystemDriver::DRIVER_FILE_SHARING),
        ];
        $host = $extSystem->connection_params['host'] ?? '';
        $paramPrice = null;
        $paramStock = null;
        $paramOrder = null;
        $paramPriceStock = null;
        if ($extSystem) {
            $integration = $merchantIntegrationService->integrations($extSystem->id);
            $driver = (int) $extSystem->driver;
            switch ($driver) {
                case ExtSystemDriver::DRIVER_1C:
                    $host = config('common-lib.integration1CHost');
                    break;
                case ExtSystemDriver::DRIVER_MOY_SKLAD:
                    $host = config('common-lib.integrationMoyskladHost');
                    $paramPrice = $integration->where('type', IntegrationType::TYPE_PRICE_IMPORT)->first();
                    $paramStock = $integration->where('type', IntegrationType::TYPE_STOCK_IMPORT)->first();
                    $paramOrder = $integration->where('type', IntegrationType::TYPE_ORDER_EXPORT)->first();
                    break;
                case ExtSystemDriver::DRIVER_FILE_SHARING:
                    $paramPriceStock = $integration->where('type', IntegrationType::TYPE_PRICE_STOCK_IMPORT)->first();
            }
        }

        return response()->json([
            'extSystem' => $extSystem,
            'extSystemsOptions' => $extSystemsOptions,
            'paramOptions' => [
                'host' => $host,
                'port' => $extSystem->connection_params['port'] ?? '',
                'paramPrice' => $paramPrice,
                'paramStock' => $paramStock,
                'paramOrder' => $paramOrder,
                'paramPriceStock' => $paramPriceStock,
                'fileName' => $extSystem->connection_params['fileName'] ?? '',
                'merchantPriceSetting' => $merchantPriceSetting->value ?? null,
                'merchantOrganizationSetting' => $merchantOrganizationSetting->value ?? null,
            ],
        ]);
    }

    public function create(
        int $merchantId,
        IntegrationRequest $request,
        MerchantService $merchantService,
        MerchantIntegrationService $merchantIntegrationService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $request->all();

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
                    'token' => $data['token'] ?? '',
                    'login' => $data['login'] ?? '',
                    'password' => $data['password'] ?? '',
                ];
                break;
            case ExtSystemDriver::DRIVER_FILE_SHARING:
                $code = 'filesharing';
                $name = 'Файловый обмен';
                $connectionParams = [
                    'login' => $data['login'],
                    'password' => $data['password'],
                    'host' => $data['host'],
                    'port' => $data['port'],
                    'fileName' => $data['fileName'],
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
            foreach ($this->moyskladIntegrationsData($data['integrationParams']) as $integrationData) {
                $integrationDto = new IntegrationDto($integrationData);
                $merchantIntegrationService->createIntegration($extSystemId, $integrationDto);
            }
            $merchantService->setSetting($merchantId, self::MERCHANT_SETTING_PRICE_NAME, $data['settingPriceValue']);
            $merchantService->setSetting($merchantId, self::MERCHANT_SETTING_ORGANIZATION_NAME, $data['settingOrganizationValue']);
        }
        if ($data['driver'] === ExtSystemDriver::DRIVER_FILE_SHARING) {
            foreach ($this->fileSharingIntegrationsData($data['integrationParams']) as $integrationData) {
                $integrationDto = new IntegrationDto($integrationData);
                $merchantIntegrationService->createIntegration($extSystemId, $integrationDto);
            }
        }

        return response()->json([]);
    }

    public function update(
        int $extSystemId,
        IntegrationRequest $request,
        MerchantService $merchantService,
        MerchantIntegrationService $merchantIntegrationService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $request->all();
        switch ((int) $data['driver']) {
            case ExtSystemDriver::DRIVER_MOY_SKLAD:
                $connectionParams = [
                    'token' => $data['token'] ?? '',
                    'login' => $data['login'] ?? '',
                    'password' => $data['password'] ?? '',
                ];
                break;
            case ExtSystemDriver::DRIVER_FILE_SHARING:
                $connectionParams = [
                    'login' => $data['login'],
                    'password' => $data['password'],
                    'host' => $data['host'],
                    'port' => $data['port'],
                    'fileName' => $data['fileName'],
                ];
                break;
        }
        $extSystem = new ExtSystemDto([
            'merchant_id' => $data['merchantId'],
            'connection_params' => $connectionParams,
        ]);

        $merchantIntegrationService->updateExtSystem($extSystemId, $extSystem);
        if ((int) $data['driver'] === ExtSystemDriver::DRIVER_MOY_SKLAD) {
            $merchantService->setSetting($data['merchantId'], self::MERCHANT_SETTING_PRICE_NAME, $data['settingPriceValue']);
            $merchantService->setSetting($data['merchantId'], self::MERCHANT_SETTING_ORGANIZATION_NAME, $data['settingOrganizationValue']);
            $integrations = $merchantIntegrationService->integrations($extSystemId);
            foreach ($this->moyskladIntegrationsData($data['integrationParams']) as $integrationData) {
                $integrationDto = new IntegrationDto($integrationData);
                $integration = $integrations->where('type', $integrationData['type'])->first();
                $merchantIntegrationService->updateIntegration($integration->id, $integrationDto);
            }
        }
        if ((int) $data['driver'] === ExtSystemDriver::DRIVER_FILE_SHARING) {
            $integrations = $merchantIntegrationService->integrations($extSystemId);
            foreach ($this->fileSharingIntegrationsData($data['integrationParams']) as $integrationData) {
                $integrationDto = new IntegrationDto($integrationData);
                $integration = $integrations->where('type', $integrationData['type'])->first();
                $merchantIntegrationService->updateIntegration($integration->id, $integrationDto);
            }
        }

        return response()->json([]);
    }

    private function moyskladIntegrationsData(array $params): array
    {
        return [
            [
                'name' => 'Импорт остатков',
                'active' => $params['paramActivePrice'] ?? false,
                'type' => IntegrationType::TYPE_STOCK_IMPORT,
                'params' => [
                    'period' => $params['paramPeriodPrice'] ?? '10',
                ],
            ],
            [
                'name' => 'Импорт цен',
                'active' => $params['paramActiveStock'] ?? false,
                'type' => IntegrationType::TYPE_PRICE_IMPORT,
                'params' => [
                    'period' => $params['paramPeriodStock'] ?? '10',
                ],
            ],
            [
                'name' => 'Экспорт заказов',
                'active' => $params['paramActiveOrder'] ?? false,
                'type' => IntegrationType::TYPE_ORDER_EXPORT,
                'params' => [
                    'period' => $params['paramPeriodOrder'] ?? '10',
                ],
            ],
        ];
    }

    private function fileSharingIntegrationsData(array $params): array
    {
        return [
            [
                'name' => 'Импорт цен и остатков',
                'active' => $params['paramActivePriceStock'] ?? false,
                'type' => IntegrationType::TYPE_PRICE_STOCK_IMPORT,
                'params' => [
                    'period' => $params['paramPeriodPriceStock'] ?? '10',
                ],
            ],
        ];
    }
}
