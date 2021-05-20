<?php

namespace App\Http\Controllers\Order\Shipment;

use App\Http\Controllers\Controller;
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
use Validator;

class ShipmentListController extends Controller
{
    protected $shipments;
    protected $merchants;
    protected $stores;
    protected $orders;
    protected $customers;
    protected $users;

    protected $pager;
    protected $filter;

    /**
     * Отобразить список отправлений
     * @param ShipmentService $shipmentService
     * @return mixed
     * @throws ValidationException
     */
    public function index(ShipmentService $shipmentService)
    {
        $this->loadShipmentStatuses = true;
        $this->loadDeliveryMethods = true;
        $this->loadDeliveryTypes = true;
        $this->loadDeliveryServices = true;

        $restQuery = $this->createQuery($shipmentService, true);

        [
            $this->shipments,
            $this->merchants,
            $this->stores,
            $this->orders,
            $this->customers,
            $this->users,
        ] = $this->loadData($restQuery);

        $this->pager = $shipmentService->shipmentsCount($restQuery);

        $this->title = 'Список отправлений';
        return $this->render('Order/Shipment', $this->getOutputData());
    }

    /**
     * Получить конкретную страницу списка отправлений
     * @param ShipmentService $shipmentService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function page(ShipmentService $shipmentService): JsonResponse
    {
        $restQuery = $this->createQuery($shipmentService, /** todo */true);
        [
            $this->shipments,
            $this->merchants,
            $this->stores,
            $this->orders,
            $this->customers,
            $this->users,
        ] = $this->loadData($restQuery);

        $result = $this->getOutputData();
        if ($this->getPage() == 1) {
            $result['iPager'] = $shipmentService->shipmentsCount($restQuery);
        }

        return response()->json($result);
    }

    /**
     * Получить номер страницы
     * @return int
     */
    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    /**
     * Сконструировать запрос, исходя из переданных условий
     * @uses Request
     * @param ShipmentService $shipmentService
     * @param bool $withDefaultFilter
     * @return DataQuery
     * @throws ValidationException
     */
    protected function createQuery(ShipmentService $shipmentService, bool $withDefaultFilter = false): DataQuery
    {
        $restQuery = $shipmentService->newQuery()
            /*->addFields(ShipmentDto::entity(),
                'id',
                'delivery_id',
                'merchant_id',
                'psd',
                'fsd',
                'store_id',
                'cargo_id',
                'status',
                'number',
                'required_shipping_at',
                'assembly_problem_comment',
                'delivery_service_zero_mile'
            )*/
            ->include('delivery', 'packages');

        $page = $this->getPage();
        $restQuery->pageNumber($page, 20);

        $filter = $this->getFilter($withDefaultFilter);
        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
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
     * @param bool $withDefault
     * @return array
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
                'created_at' => 'string',
                'created_between' => 'array|sometimes',
                'created_between.*' => 'string',
                //'status.*' => Rule::in(array_keys(OrderStatus::allStatuses())),
                'price_from' => 'numeric|sometimes',
                'price_to' => 'numeric|sometimes',
                'offer_xml_id' => 'string|sometimes',
                'product_vendor_code' => 'string|sometimes',
                'brands' => 'array|sometimes',
                'brands.*' => 'integer',
                'merchants' => 'array|sometimes',
                'merchants.*' => 'integer',
                //'payment_method.*' => Rule::in(array_keys(PaymentMethod::allMethods())),
                'stores' => 'array|sometimes',
                'stores.*' => 'integer',
                //'delivery_type.*' => Rule::in(array_keys(DeliveryType::allTypes())),
                //'delivery_service.*' => Rule::in(array_keys(DeliveryService::allServices())),
                'delivery_city' => 'string|sometimes',
                'delivery_city_string' => 'string|sometimes',
                'psd' => 'array|sometimes',
                'psd.*' => 'string|nullable',
                'pdd' => 'array|sometimes',
                'pdd.*' => 'string|nullable',
                'is_canceled' => 'boolean|sometimes',
                'is_problem' => 'boolean|sometimes',
                'is_require_check' => 'boolean|sometimes',
                //'confirmation_type.*' => Rule::in(array_keys(OrderConfirmationType::allTypes())),
                'manager_comment' => 'string|sometimes',
            ]
        );
    }

    /**
     * Получить порцию данных, соответствующую запросу
     * @param DataQuery $restQuery
     * @return array
     */
    protected function loadData(DataQuery $restQuery): array
    {
        $shipmentService = resolve(ShipmentService::class);
        $storeService = resolve(StoreService::class);
        $orderService = resolve(OrderService::class);
        $customerService = resolve(CustomerService::class);
        $userService = resolve(UserService::class);

        $shipments = $shipmentService->shipments($restQuery);

        $merchants = $this->getMerchants($shipments->pluck('merchant_id')->toArray());

        $stores = $storeService
            ->stores((new RestQuery())->addFields(StoreDto::entity(), 'id', 'name'))
            ->keyBy('id');

        $orders = $orderService
            ->orders((new RestQuery())
                ->setFilter('id', $shipments->pluck('delivery.order_id')->toArray()))
            ->keyBy('id');

        $customers = $customerService
            ->customers((new RestQuery())
                ->addFields(CustomerDto::entity(), 'id', 'user_id')
                ->setFilter('id', $orders->pluck('customer_id')->toArray()))
            ->keyBy('id');

        $users = $userService
            ->users((new RestQuery())
                ->addFields(UserDto::entity(), 'id')
                ->setFilter('id', $customers->pluck('user_id')->toArray()))
            ->keyBy('id');

        return [$shipments, $merchants, $stores, $orders, $customers, $users];
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
            'iPager' => $this->pager,
            'iCurrentPage' => $this->getPage(),
            'iFilter' => $this->getFilter(true),
        ];
    }
}
