<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantIntegration\IntegrationRequest;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
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
    protected const MERCHANT_SETTING_ORGANIZATION_CODE = 'moy_sklad_order_organization';
    protected const MERCHANT_SETTING_AGENT_CODE = 'moy_sklad_order_agent';
    protected const MERCHANT_SETTING_OWNER_CODE = 'moy_sklad_order_owner';

    private MerchantIntegrationService $merchantIntegrationService;
    private MerchantService $merchantService;

    public function __construct(
        MerchantIntegrationService $merchantIntegrationService,
        MerchantService $merchantService
    ) {
        $this->merchantIntegrationService = $merchantIntegrationService;
        $this->merchantService = $merchantService;
    }

    public function load(int $merchantId): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $restQuery = $this->merchantIntegrationService->newQuery()->setFilter('merchant_id', $merchantId);
        $extSystem = $this->merchantIntegrationService->extSystems($restQuery)->first();
        $merchantPriceSetting = $this->merchantService
            ->getSetting($merchantId, self::MERCHANT_SETTING_PRICE_NAME)->first();
        $merchantOrganizationSetting = $this->merchantService
            ->getSetting($merchantId, self::MERCHANT_SETTING_ORGANIZATION_CODE)->first();
        $merchantAgentSetting = $this->merchantService
            ->getSetting($merchantId, self::MERCHANT_SETTING_AGENT_CODE)->first();
        $merchantOwnerSetting = $this->merchantService
            ->getSetting($merchantId, self::MERCHANT_SETTING_OWNER_CODE)->first();
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
            $integration = $this->merchantIntegrationService->integrations($extSystem->id);
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
                'merchantAgentSetting' => $merchantAgentSetting->value ?? null,
                'merchantOwnerSetting' => $merchantOwnerSetting->value ?? null,
            ],
        ]);
    }

    public function create(int $merchantId, IntegrationRequest $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $request->all();

        $merchant = $this->merchantService->merchant($merchantId);
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

        $extSystemId = $this->merchantIntegrationService->createExtSystem($extSystem);
        $this->saveIntegrations($data, collect(), $extSystemId);
        $this->saveSettings($data);

        return response()->json([]);
    }

    public function update(
        int $extSystemId,
        IntegrationRequest $request,
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
            'connection_params' => $connectionParams ?? [],
        ]);

        $merchantIntegrationService->updateExtSystem($extSystemId, $extSystem);

        $integrations = $merchantIntegrationService->integrations($extSystemId);
        $this->saveIntegrations($data, $integrations, $extSystemId);
        $this->saveSettings($data);

        return response()->json([]);
    }

    private function saveIntegrations(array $data, Collection $integrations, int $extSystemId): void
    {
        switch ((int) $data['driver']) {
            case ExtSystemDriver::DRIVER_MOY_SKLAD:
                foreach ($this->moyskladIntegrationsData($data['integrationParams']) as $integrationData) {
                    $integrationDto = new IntegrationDto($integrationData);

                    $this->saveIntegration($integrationDto, $integrations, $extSystemId);
                }
                return;
            case ExtSystemDriver::DRIVER_FILE_SHARING:
                foreach ($this->fileSharingIntegrationsData($data['integrationParams']) as $integrationData) {
                    $integrationDto = new IntegrationDto($integrationData);

                    $this->saveIntegration($integrationDto, $integrations, $extSystemId);
                }
                return;
            default:
                //
        }
    }

    private function saveIntegration(IntegrationDto $integrationDto, Collection $integrations, int $extSystemId): void
    {
        $integration = $integrations->firstWhere('type', $integrationDto->type);

        if ($integration) {
            $this->merchantIntegrationService->updateIntegration($integration->id, $integrationDto);
        } else {
            $this->merchantIntegrationService->createIntegration($extSystemId, $integrationDto);
        }
    }

    private function saveSettings(array $data): void
    {
        switch ((int) $data['driver']) {
            case ExtSystemDriver::DRIVER_MOY_SKLAD:
                $this->merchantService->setSetting(
                    $data['merchantId'],
                    self::MERCHANT_SETTING_PRICE_NAME,
                    $data['settingPriceValue'] ?? ''
                );
                $this->merchantService->setSetting(
                    $data['merchantId'],
                    self::MERCHANT_SETTING_ORGANIZATION_CODE,
                    $data['settingOrganizationValue'] ?? ''
                );
                $this->merchantService->setSetting(
                    $data['merchantId'],
                    self::MERCHANT_SETTING_AGENT_CODE,
                    $data['settingAgentValue'] ?? ''
                );
                $this->merchantService->setSetting(
                    $data['merchantId'],
                    self::MERCHANT_SETTING_OWNER_CODE,
                    $data['settingOwnerValue'] ?? ''
                );
                return;
            default:
                //
        }
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
