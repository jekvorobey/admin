<?php

namespace App\Http\Controllers\Order\Shipment;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Pim\Dto\BrandDto;
use Pim\Services\BrandService\BrandService;
use Validator;

class ShipmentListController extends Controller
{
    protected $shipments;
    protected $merchants;
    protected $stores;
    protected $orders;
    protected $customers;
    protected $users;
    protected $brands;

    protected $pager;
    protected $filter;

    /**
     * Отобразить список отправлений
     * @return mixed
     * @throws ValidationException
     */
    public function index(ShipmentService $shipmentService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $this->loadShipmentStatuses = true;
        $this->loadDeliveryTypes = true;
        $this->loadDeliveryServices = true;
        $this->loadCargoStatuses = true;

        $restQuery = $this->createQuery($shipmentService, true);

        [
            $this->shipments,
            $this->merchants,
            $this->stores,
            $this->orders,
            $this->customers,
            $this->users,
            $this->brands,
        ] = $this->loadData($restQuery);

        $this->pager = $shipmentService->shipmentsCount($restQuery);

        $this->title = 'Список отправлений';
        return $this->render('Order/Shipment', $this->getOutputData());
    }

    /**
     * Получить конкретную страницу списка отправлений
     * @throws ValidationException
     */
    public function page(ShipmentService $shipmentService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $restQuery = $this->createQuery($shipmentService, true);
        [
            $this->shipments,
            $this->merchants,
            $this->stores,
            $this->orders,
            $this->customers,
            $this->users,
            $this->brands,
        ] = $this->loadData($restQuery);

        $result = $this->getOutputData();
        if ($this->getPage() === 1) {
            $result['iPager'] = $shipmentService->shipmentsCount($restQuery);
        }

        return response()->json($result);
    }

    /**
     * Получить номер страницы
     */
    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    /**
     * Сконструировать запрос, исходя из переданных условий
     * @uses Request
     * @throws ValidationException
     */
    protected function createQuery(ShipmentService $shipmentService, bool $withDefaultFilter = false): DataQuery
    {
        $restQuery = $shipmentService->newQuery()
            ->include('delivery', 'packages', 'cargo');

        $page = $this->getPage();
        $restQuery->pageNumber($page, 20);

        $filter = $this->getFilter($withDefaultFilter);
        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'customer':
                        if ($value) {
                            $restQuery->setFilter('receiver_name', 'like', "%$value%");
                        }
                        break;
                    case 'price_from':
                        if ($value) {
                            $restQuery->setFilter('cost', '>=', $value);
                        }
                        break;
                    case 'price_to':
                        if ($value) {
                            $restQuery->setFilter('cost', '<=', $value);
                        }
                        break;
                    case 'merchants':
                        if ($value) {
                            $restQuery->setFilter('merchant_id', $value);
                        }
                        break;
                    case 'stores':
                        if ($value) {
                            $restQuery->setFilter('store_id', $value);
                        }
                        break;
                    case 'psd':
                        if ($value) {
                            $restQuery->setFilter('psd', '>=', $value[0]);
                            $restQuery->setFilter('psd', '<=', $value[1]);
                        }
                        break;
                    case 'pdd':
                        if ($value) {
                            $restQuery->setFilter('pdd', '>=', $value[0]);
                            $restQuery->setFilter('pdd', '<=', $value[1]);
                        }
                        break;
                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
        }
        $restQuery->addSort('created_at', 'desc');

        return $restQuery;
    }

    /**
     * Прочитать и проверить переданные правила фильтрации
     * @throws ValidationException
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::validate(
            request('filter') ?? [],
            [
                'number' => 'string|sometimes',
                'customer' => 'string|sometimes',
                'status' => 'array|sometimes',
                'status.*' => 'integer',
                'price_from' => 'numeric|sometimes',
                'price_to' => 'numeric|sometimes',
                'product_vendor_code' => 'string|sometimes',
                'brands' => 'array|sometimes',
                'brands.*' => 'integer',
                'merchants' => 'array|sometimes',
                'merchants.*' => 'integer',
                'stores' => 'array|sometimes',
                'stores.*' => 'integer',
                'delivery_type' => 'array|sometimes',
                'delivery_type.*' => 'integer',
                'delivery_service' => 'array|sometimes',
                'delivery_service.*' => 'integer',
                'delivery_address_city' => 'string|sometimes',
                'psd' => 'array|sometimes',
                'psd.*' => 'string|nullable',
                'pdd' => 'array|sometimes',
                'pdd.*' => 'string|nullable',
                'cargo_id' => 'string|sometimes',
                'cargo_xml_id' => 'string|sometimes',
            ]
        );
    }

    /**
     * Получить порцию данных, соответствующую запросу
     */
    protected function loadData(DataQuery $restQuery): array
    {
        $shipmentService = resolve(ShipmentService::class);
        $storeService = resolve(StoreService::class);
        $orderService = resolve(OrderService::class);
        $customerService = resolve(CustomerService::class);
        $userService = resolve(UserService::class);
        $brandService = resolve(BrandService::class);

        $shipments = $shipmentService->shipments($restQuery);

        $merchants = $this->getMerchants($shipments->pluck('merchant_id')->toArray());

        $stores = $storeService
            ->stores((new RestQuery())->addFields(StoreDto::entity(), 'id', 'name', 'address'))
            ->keyBy('id');

        $orders = collect();
        $customers = collect();
        $users = collect();

        if ($orderIds = $shipments->pluck('delivery.order_id')->toArray()) {
            $orders = $orderService
                ->orders((new RestQuery())
                    ->setFilter('id', $orderIds))
                ->keyBy('id');

            if ($customerIds = $orders->pluck('customer_id')->toArray()) {
                $customers = $customerService
                    ->customers((new RestQuery())
                        ->addFields(CustomerDto::entity(), 'id', 'user_id')
                        ->setFilter('id', $customerIds))
                    ->keyBy('id');

                if ($userIds = $customers->pluck('user_id')->toArray()) {
                    $users = $userService
                        ->users((new RestQuery())
                            ->addFields(UserDto::entity(), 'id')
                            ->setFilter('id',$userIds))
                        ->keyBy('id');
                }
            }
        }

        $brands = $brandService->brands((new RestQuery())
            ->addFields(BrandDto::entity(), 'id', 'name'))->keyBy('id');

        return [$shipments, $merchants, $stores, $orders, $customers, $users, $brands];
    }

    /**
     * Возвращает упорядоченный массив подготовленных данных
     * @return array
     * @throws ValidationException
     */
    protected function getOutputData(): array
    {
        return [
            'iShipments' => $this->shipments,
            'iMerchants' => $this->merchants,
            'iCustomers' => $this->customers,
            'iUsers' => $this->users,
            'iStores' => $this->stores,
            'iOrders' => $this->orders,
            'iBrands' => $this->brands,
            'iPager' => $this->pager,
            'iCurrentPage' => $this->getPage(),
            'iFilter' => $this->getFilter(true),
        ];
    }
}
