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
use MerchantManagement\Dto\MerchantSettingDto;
use MerchantManagement\Services\MerchantIntegrationService\MerchantIntegrationService;
use MerchantManagement\Services\MerchantService\MerchantService;

class TabExtSystemsController extends Controller
{
    private MerchantIntegrationService $merchantIntegrationService;
    private MerchantService $merchantService;
    private ?ExtSystemDto $extSystem;

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

        $this->loadExtSystem($merchantId);

        $extSystemsOptions = [
            ExtSystemDriver::driverById(ExtSystemDriver::DRIVER_1C),
            ExtSystemDriver::driverById(ExtSystemDriver::DRIVER_MOY_SKLAD),
            ExtSystemDriver::driverById(ExtSystemDriver::DRIVER_FILE_SHARING),
        ];

        return response()->json([
            'extSystem' => $this->extSystem,
            'extSystemsOptions' => $extSystemsOptions,
            'paramOptions' => $this->loadParamsOptions($merchantId),
        ]);
    }

    public function create(int $merchantId, IntegrationRequest $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $request->all();
        $extSystem = new ExtSystemDto($this->loadExtSystemAttributes($merchantId, $data));
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
        $extSystem = new ExtSystemDto($this->loadExtSystemAttributes($data['merchantId'], $data));
        $merchantIntegrationService->updateExtSystem($extSystemId, $extSystem);

        $integrations = $merchantIntegrationService->integrations($extSystemId);
        $this->saveIntegrations($data, $integrations, $extSystemId);
        $this->saveSettings($data);

        return response()->json([]);
    }

    private function loadExtSystem(int $merchantId): void
    {
        $restQuery = $this->merchantIntegrationService->newQuery()->setFilter('merchant_id', $merchantId);
        $extSystem = $this->merchantIntegrationService->extSystems($restQuery)->first();
        $this->extSystem = $extSystem;
    }

    private function loadParamsOptions(int $merchantId): array
    {
        $merchantSettings = $this->merchantService->getSettings($merchantId)->keyBy('name');
        $merchantPriceSetting = $merchantSettings->get(MerchantSettingDto::MOYSKLAD_PRICE_NAME);
        $merchantOrganizationSetting = $merchantSettings->get(MerchantSettingDto::MOYSKLAD_ORGANIZATION_CODE);
        $merchantAgentSetting = $merchantSettings->get(MerchantSettingDto::MOYSKLAD_AGENT_CODE);
        $merchantOwnerSetting = $merchantSettings->get(MerchantSettingDto::MOYSKLAD_OWNER_CODE);
        $host = $extSystem->connection_params['host'] ?? '';
        $paramPrice = null;
        $paramStock = null;
        $paramOrder = null;
        $paramPriceStock = null;
        if ($this->extSystem) {
            $integration = $this->merchantIntegrationService->integrations($this->extSystem->id)->keyBy('type');
            $driver = (int) $this->extSystem->driver;
            switch ($driver) {
                case ExtSystemDriver::DRIVER_1C:
                    $host = config('common-lib.integration1CHost');
                    break;
                case ExtSystemDriver::DRIVER_MOY_SKLAD:
                    $host = config('common-lib.integrationMoyskladHost');
                    $paramPrice = $integration->get(IntegrationType::TYPE_PRICE_IMPORT);
                    $paramStock = $integration->get(IntegrationType::TYPE_STOCK_IMPORT);
                    $paramOrder = $integration->get(IntegrationType::TYPE_ORDER_EXPORT);
                    break;
                case ExtSystemDriver::DRIVER_FILE_SHARING:
                    $paramPriceStock = $integration->get(IntegrationType::TYPE_PRICE_STOCK_IMPORT);
            }
        }

        return [
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
        ];
    }

    private function loadExtSystemAttributes(int $merchantId, array $data): array
    {
        $merchant = $this->merchantService->merchant($merchantId);
        $connectionParams = [];
        switch ((int) $data['driver']) {
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

        return [
            'merchant_id' => $merchantId,
            'code' => $code,
            'name' => $name,
            'driver' => $data['driver'],
            'connection_params' => $connectionParams,
        ];
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
                $settings = [
                    new MerchantSettingDto(['name' => MerchantSettingDto::MOYSKLAD_PRICE_NAME, 'value' => $data['settingPriceValue'] ?? '']),
                    new MerchantSettingDto(['name' => MerchantSettingDto::MOYSKLAD_ORGANIZATION_CODE, 'value' => $data['settingOrganizationValue'] ?? '']),
                    new MerchantSettingDto(['name' => MerchantSettingDto::MOYSKLAD_AGENT_CODE, 'value' => $data['settingAgentValue'] ?? '']),
                    new MerchantSettingDto(['name' => MerchantSettingDto::MOYSKLAD_OWNER_CODE, 'value' => $data['settingOwnerValue'] ?? '']),
                ];
                $this->merchantService->setSettings($data['merchantId'], $settings);
                return;
            default:
                //
        }
    }

    private function moyskladIntegrationsData(array $params): array
    {
        return [
            [
                'name' => 'Импорт цен',
                'active' => $params['paramActivePrice'] ?? false,
                'type' => IntegrationType::TYPE_PRICE_IMPORT,
                'params' => [
                    'period' => $params['paramPeriodPrice'] ?? '10',
                ],
            ],
            [
                'name' => 'Импорт остатков',
                'active' => $params['paramActiveStock'] ?? false,
                'type' => IntegrationType::TYPE_STOCK_IMPORT,
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
