<?php

namespace App\Http\Controllers\Order\Cargo;

use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Oms\Dto\Delivery\CargoDto;
use Greensight\Oms\Dto\Delivery\CargoStatus;
use Greensight\Oms\Services\CargoService\CargoService;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;

/**
 * Class CargoListController
 * @package App\Http\Controllers\Order\Cargo
 */
class CargoListController extends Controller
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request, CargoService $cargoService, MerchantService $merchantService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $this->title = 'Грузы';
        $this->loadCargoStatuses = true;
        $this->loadDeliveryServices = true;
        $restQuery = $this->makeRestQuery($cargoService, $request, true);
        $pager = $cargoService->cargosCount($restQuery);
        $cargos = $this->loadCargos($restQuery, $cargoService);

        return $this->render('Order/Cargo/List', [
            'iCargos' => $cargos,
            'iCurrentPage' => $request->get('page', 1),
            'iPager' => $pager,
            'stores' => $this->loadStores(),
            'iFilter' => $this->getFilter(true),
            'iSort' => $request->get('sort', 'created_at'),
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'legal_name')->merchants(),
        ]);
    }

    /**
     * @throws Exception
     */
    public function page(Request $request, CargoService $cargoService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $restQuery = $this->makeRestQuery($cargoService, $request);
        $cargos = $this->loadCargos($restQuery, $cargoService);
        $result = [
            'cargos' => $cargos,
        ];
        if ($request->get('page') == 1) {
            $result['pager'] = $cargoService->cargosCount($restQuery);
        }

        return response()->json($result);
    }

    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::make(
            request('filter') ??
            ($withDefault ?
                [
                    'status' => [
                        CargoStatus::CREATED,
                    ],
                    'is_canceled' => 0,
                ] : []),
            [
                'id' => 'integer|someone',
                'merchant_id' => 'array|someone',
                'status' => Rule::in(array_keys(CargoStatus::allStatuses())),
                'delivery_service' => Rule::in(array_keys(DeliveryService::allServices())),
                'store_id' => 'array|someone',
                'shipment_number' => 'integer|someone',
                'created_at' => 'array|someone',
                'is_canceled' => 'boolean|someone',
            ]
        )->attributes();
    }

    /**
     * @return Collection|CargoDto[]
     */
    protected function loadCargos(DataQuery $restQuery, CargoService $cargoService): Collection
    {
        $restQuery->addFields(
            CargoDto::entity(),
            'id',
            'store_id',
            'status',
            'delivery_service',
            'package_qty',
            'created_at',
            'shipping_problem_comment'
        );
        $cargos = $cargoService->cargos($restQuery);

        $merchantIds = $cargos->pluck('merchant_id')->unique()->all();
        $merchants = $this->getMerchants($merchantIds);

        $stores = $this->loadStores();
        $deliveryServices = DeliveryService::allServices();
        $cargos = $cargos->map(function (CargoDto $cargo) use ($merchants, $stores, $deliveryServices) {
            $data = $cargo->toArray();

            $data['merchant'] = $merchants->has($cargo->merchant_id) ? $merchants[$cargo->merchant_id] : [];
            $data['status'] = $cargo->status()->toArray();
            $data['created_at'] = (new Carbon($cargo->created_at))->format('H:i:s Y-m-d');
            $data['store'] = $stores[$cargo->store_id];
            $data['delivery_service'] = $deliveryServices[$cargo->delivery_service];

            // TODO: Проверку заявки на вызов курьера поддерживает только CDEK //
            if ($data['delivery_service']['id'] == DeliveryService::SERVICE_CDEK) {
                $data['delivery_service']['support_courier_check'] = true;
            }

            return $data;
        });

        return $cargos;
    }

    /**
     * @throws Exception
     */
    protected function makeRestQuery(
        CargoService $cargoService,
        Request $request,
        bool $withDefaultFilter = false
    ): DataQuery {
        /** @var RestQuery $restQuery */
        $restQuery = $cargoService->newQuery()->addSort('created_at', 'desc');

        $page = $request->get('page', 1);
        $restQuery->pageNumber($page, 20);

        $filter = $this->getFilter($withDefaultFilter);
        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'created_at':
                        $value = array_filter($value);
                        if ($value) {
                            $restQuery->setFilter($key, '>=', $value[0]);
                            $restQuery->setFilter($key, '<=', $value[1]);
                        }
                        break;

                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
        }

        return $restQuery;
    }

    /**
     * TODO пересмотреть область видимости метода
     * @return Collection|StoreDto[]
     */
    public function loadStores(): Collection
    {
        /** @var Collection|StoreDto[] $stores */
        static $stores = null;

        if (is_null($stores)) {
            /** @var StoreService $storeService */
            $storeService = resolve(StoreService::class);

            $restQuery = $storeService->newQuery();
            $restQuery->addFields(StoreDto::entity(), 'id', 'name', 'merchant_id');
            $stores = $storeService->stores($restQuery)->keyBy('id');

            $merchantIds = $stores->pluck('merchant_id')->unique()->all();
            $merchants = $this->getMerchants($merchantIds);

            $stores = $stores->map(function (StoreDto $store) use ($merchants) {
                $data = $store->toArray();

                $data['merchant'] = $merchants->has($store->merchant_id) ? $merchants[$store->merchant_id] : [];

                return $data;
            });
        }

        return $stores;
    }
}
