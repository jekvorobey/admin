<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Core\DiscountHelper;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Dto\Delivery\ShipmentStatus;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\Request;
use Greensight\CommonMsa\Dto\AbstractDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Dto\PaymentMethod;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Services\BrandService\BrandService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\Oms\Dto\DeliveryType;
use Greensight\Oms\Dto\Delivery\DeliveryService as DeliveryServiceDto;


class TabOrderController extends Controller
{
    /**
     * AJAX подгрузка информации для фильтрации скидок
     *
     * @param int $merchantId
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function loadShipmentData(int $merchantId, Request $request)
    {

    }

    /**
     * @param bool $withDefault
     * @return array
     */
    protected function getFilter(bool $withDefault = false): array
    {
        $filter = Validator::make(request('filter') ??
            ($withDefault ?
                [
                    'status' => [
                        ShipmentStatus::AWAITING_CONFIRMATION,
                        ShipmentStatus::ASSEMBLING,
                    ],
                ] :
                [
                    'status' => array_filter(array_keys(ShipmentStatus::allStatuses()), function (int $statusId) {
                        return !in_array($statusId, [
                            ShipmentStatus::PRE_ORDER,
                            ShipmentStatus::CREATED,
                            ShipmentStatus::AWAITING_CHECK,
                            ShipmentStatus::CHECKING,
                        ]);
                    }),
                ]),
            [
                'number' => 'string|someone',
                'created_at' => 'array|someone',
                'required_shipping_at' => 'array|someone',
                'store_id' => 'array|someone',
                'status' => Rule::in(array_keys(ShipmentStatus::allStatuses())),
                'cost' => 'array|someone',
                'offer_xml_id' => 'string|someone',
                'product_vendor_code' => 'string|someone',
                'brands' => 'array|someone',
            ]
        )->attributes();
        $filter['is_problem'] = false;

        return $filter;
    }

    /**
     * @param  Request  $request
     * @param  bool $withDefaultFilter
     * @return DataQuery
     * @throws \Exception
     */
    protected function makeRestQuery(
        Request $request,
        int $merchantId,
        bool $withDefaultFilter = false
    ): DataQuery {
        /** @var ShipmentService $shipmentService */
        $shipmentService = resolve(ShipmentService::class);

        /** @var RestQuery $restQuery */
        $restQuery = $shipmentService->newQuery();
        $restQuery->setFilter('merchant_id', $merchantId)
            ->setFilter('is_canceled', false)
            ->setFilter('status', '>=', ShipmentStatus::AWAITING_CONFIRMATION);

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
                    case 'required_shipping_at':
                        $restQuery->setFilter($key, '>=', $value);
                        $restQuery->setFilter(
                            $key,
                            '<=',
                            (new Carbon($value))
                                ->modify('+1 day')
                                ->format('Y-m-d')
                        );
                        break;
                    case 'cost':
                        if (isset($value[0]) && $value[0]) {
                            $restQuery->setFilter($key, '>=', $value[0]);
                        }
                        if (isset($value[1]) && $value[1]) {
                            $restQuery->setFilter($key, '<=', $value[1]);
                        }
                        break;

                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
        }
        $restQuery->addSort('id', 'desc');

        return $restQuery;
    }

    /**
     * @param int $merchantId
     * @return Collection|StoreDto[]
     */
    public function loadStores(int $merchantId): Collection
    {
        /** @var Collection|StoreDto[] $stores */
        static $stores = null;

        if (is_null($stores)) {
            /** @var StoreService $storeService */
            $storeService = resolve(StoreService::class);

            $restQuery = $storeService->newQuery();
            $restQuery->addFields(StoreDto::entity(), 'id', 'name');
            $stores = $storeService->stores($restQuery, $merchantId)->keyBy('id');
        }

        return $stores;
    }

    /**
     * @param DataQuery $restQuery
     * @return Collection
     */
    protected function loadShipments(DataQuery $restQuery): Collection
    {
        /** @var ShipmentService $shipmentService */
        $shipmentService = resolve(ShipmentService::class);

        /** @var OrderService $orderService */
        $orderService = resolve(OrderService::class);

        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);

        /** @var UserService $userService */
        $userService = resolve(UserService::class);

        /** @var StoreService $storeService */
        $storeService = resolve(StoreService::class);

        $restQuery->addFields(
            ShipmentDto::entity(),
            'id',
            'status',
            'package_qty',
            'weight',
            'is_problem',
            'cost',
            'delivery_service_zero_mile',
            'store_id',
            'required_shipping_at'
        );

        $restQuery->include('delivery');

        $shipments = $shipmentService->shipments($restQuery);

        $orderIds = [];
        $shipments->each(function (ShipmentDto $shipment) use (&$orderIds) {
            array_push($orderIds, $shipment->delivery()->order_id);
        });
        $orders = $orderService->orders(
            (new RestQuery())
                ->addFields(OrderDto::class, 'id', 'customer_id', 'delivery_type')
                ->setFilter('id', $orderIds)
        );

        $customerIds = [];
        $orders->each(function (OrderDto $order) use (&$customerIds) {
            array_push($customerIds, $order->customer_id);
        });
        $customers = $customerService->customers(
            (new RestQuery())
                ->addFields(CustomerDto::class, 'id', 'user_id')
                ->setFilter('id', $customerIds)
        );

        $userIds = [];
        $customers->each(function (CustomerDto $customer) use (&$userIds) {
            array_push($userIds, $customer->user_id);
        });
        $users = $userService->users(
            (new RestQuery())
                ->addFields(UserDto::class, 'id', 'full_name', 'phone')
                ->setFilter('id', $userIds)
        );

        $storesIds = [];
        $shipments->each(function (ShipmentDto $shipment) use (&$storesIds) {
            array_push($storesIds, $shipment->store_id);
        });
        $stores = $storeService->stores(
            (new RestQuery)->addFields(StoreDto::class, 'id', 'name')
                ->setFilter('id', $storesIds)
        );

//        $orders, $customers, $users, $stores
//            ->keyBy('id');

//        $shipments = $shipments->map(function (ShipmentDto $shipment) use ($stores) {
        $shipments = $shipments->map(function (ShipmentDto $shipment) use ($orders, $customers, $users, $stores) {
//            $shipments = $shipments->map(function (ShipmentDto $shipment) {
            $data['order_id'] = $shipment->delivery()->order_id;
            $data['order_number'] = $shipment->delivery()->number;
            $data['shipment_id'] = $shipment->id;
            $data['shipment_number'] = $shipment->number;
            $user = $users->where(
                'id',
                $customers->firstWhere(
                    'id',
                    $orders->firstWhere(
                        'id',
                        $shipment->delivery()->order_id
                    )['customer_id']
                )['user_id']
            )->values()[0];
            $data['user_id'] = $user->id;
            $data['user_full_name'] = $user->getTitle();
            $data['status'] = $shipment->status;
            $data['package_qty'] = $shipment->package_qty;
            $data['weight'] = $shipment->weight;
            $data['is_problem'] = $shipment->is_problem;
            $data['cost'] = $shipment->cost;
            $data['delivery_type'] = DeliveryType::typeById(
                $orders->firstWhere(
                    'id',
                    $shipment->delivery()->order_id
                )['delivery_type']
            );
            $data['delivery_method'] = $shipment->delivery()->delivery_method;
            $data['delivery_service_last_mile'] = DeliveryServiceDto::serviceById(
                $shipment->delivery()->delivery_service
            );
            $data['delivery_service_zero_mile'] = DeliveryServiceDto::serviceById(
                $shipment->delivery_service_zero_mile ??
                    $shipment->delivery()->delivery_service
            );
            $store = $stores->firstWhere('id', $shipment->store_id);
            $data['store_id'] = $store['id'];
            $data['store_name'] = $store['name'];
            $data['delivery_service_zero_mile'] = $shipment->delivery()->delivery_adress;
            $data['required_shipping_at'] = $shipment->required_shipping_at;
            $data['delivery_at'] = $shipment->delivery()->delivery_at;
//            $data['order_id'] = $shipment->status();


//            $data['status'] = $shipment->status()->toArray();
//            $data['created_at'] = (new Carbon($shipment->created_at))->format('H:i:s Y-m-d');
//            $data['required_shipping_at'] = (new Carbon($shipment->required_shipping_at))->format('H:i:s Y-m-d');
//            $data['store'] = $stores->has($shipment->store_id) ? $stores[$shipment->store_id] : [];

            return $data;
        });

        return $shipments;
//        return $users;
    }

    /**
     * AJAX пагинация списка заказов
     *
     * @param int $merchantId
     * @param Request $request
     * @param  ShipmentService $shipmentService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function page(int $merchantId, Request $request, ShipmentService $shipmentService): JsonResponse
    {
        $restQuery = $this->makeRestQuery($request, $merchantId);
        $shipments = $this->loadShipments($restQuery);
//        $result = [
//            'shipments' => $shipments,
//        ];
//        if ($request->get('page') == 1) {
//            $result['pager'] = $shipmentService->shipmentsCount($restQuery);
//        }
//
//        return response()->json($result);
        return response()->json([
            'test' => $shipments[0],
        ]);
    }
}