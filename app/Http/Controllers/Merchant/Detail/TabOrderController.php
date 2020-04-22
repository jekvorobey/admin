<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Dto\Delivery\ShipmentStatus;
use Greensight\Oms\Dto\DeliveryType;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StoreService\StoreService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class TabOrderController extends Controller
{
    /**
     * @return array
     */
    protected function getCustomersNames(DataQuery $restQuery): array
    {
        /** @var ShipmentService $shipmentService */
        $shipmentService = resolve(ShipmentService::class);

        /** @var OrderService $orderService */
        $orderService = resolve(OrderService::class);

        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);

        /** @var UserService $userService */
        $userService = resolve(UserService::class);

        $restQuery->addFields(ShipmentDto::entity(), 'id')
            ->include('delivery');

        $shipments = $shipmentService->shipments($restQuery);

        $orderIds = $shipments->pluck('delivery.order_id')->all();
        $orders = $orderService->orders(
            (new RestQuery())
                ->addFields(OrderDto::class, 'id', 'customer_id')
                ->setFilter('id', $orderIds)
        )->keyBy('id');

        $customerIds = $orders->pluck('customer_id')->all();
        $customers = $customerService->customers(
            (new RestQuery())
                ->addFields(CustomerDto::class, 'id', 'user_id')
                ->setFilter('id', $customerIds)
        )->keyBy('id');

        $userIds = $customers->pluck('user_id')->all();
        $users = $userService->users(
            (new RestQuery())
                ->addFields(UserDto::class, 'id', 'full_name')
                ->setFilter('id', $userIds)
        )->keyBy('id');

        return array_filter($users->map(function (UserDto $user) {
            return $user->full_name;
        })->all());
    }

    /**
     * AJAX подгрузка информации для фильтрации скидок
     *
     * @param int $merchantId
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function loadOrdersData(int $merchantId, Request $request)
    {
        $restQuery = $this->makeRestQuery($request, $merchantId);

        return response()->json([
            'shipmentStatuses' => ShipmentStatus::allStatuses(),
            'customerFullNames' => $this->getCustomersNames($restQuery),
            'deliveryTypes' => DeliveryType::allTypes(),
            'deliveryMethods' => DeliveryMethod::allMethods(),
            'deliveryServices' => DeliveryService::allServices(),
        ]);
    }

    /**
     * @return array
     */
    protected function getFilter(): array
    {
        $allDeliveryServices = DeliveryService::allServices();
        $filter = Validator::make(request('filter') ?? [],
            [
                'order_number' => 'string|someone',
                'number' => 'string|someone',
                'status' => 'array|someone',
                'status.' => Rule::in(array_keys(ShipmentStatus::allStatuses())),
                'is_problem' => 'boolean|someone',
                'customer_id' => 'numeric|someone',
                'customer_full_name' => 'string|someone',
                'package_qty_from' => 'numeric|someone',
                'package_qty_to' => 'numeric|someone',
                'weight_from' => 'numeric|someone',
                'weight_to' => 'numeric|someone',
                'cost_from' => 'numeric|someone',
                'cost_to' => 'numeric|someone',
                'delivery_type' => 'array|someone',
                'delivery_type.' => Rule::in(array_keys(DeliveryType::allTypes())),
                'delivery_method' => 'array|someone',
                'delivery_method.' => Rule::in(array_keys(DeliveryMethod::allMethods())),
                'delivery_service' => 'array|someone',
                'delivery_service.' => Rule::in(array_keys($allDeliveryServices)),
                'delivery_service_zero_mile' => 'array|someone',
                'delivery_service_zero_mile.' => Rule::in(array_keys($allDeliveryServices)),
                'delivery_address_post_index' => 'string|someone',
                'delivery_address_region' => 'string|someone',
                'delivery_address_city' => 'string|someone',
                'delivery_address_street' => 'string|someone',
                'delivery_address_porch' => 'string|someone',
                'delivery_address_house' => 'string|someone',
                'delivery_address_floor' => 'string|someone',
                'delivery_address_flat' => 'string|someone',
                'required_shipping_at' => 'array|someone',
                'required_shipping_at.'  => 'date',
                'delivery_at' => 'array|someone',
                'delivery_at.'  => 'date',
            ]
        )->attributes();

        return $filter;
    }

    /**
     * @param  Request  $request
     * @return DataQuery
     * @throws \Exception
     */
    protected function makeRestQuery(
        Request $request,
        int $merchantId
    ): DataQuery {
        /** @var ShipmentService $shipmentService */
        $shipmentService = resolve(ShipmentService::class);

        /** @var RestQuery $restQuery */
        $restQuery = $shipmentService->newQuery();
        $restQuery->setFilter('merchant_id', $merchantId)
            ->setFilter('is_canceled', false)
            ->setFilter('user_exist', intval(true));

        $page = $request->get('page', 1);
        $restQuery->pageNumber($page, 20);

        $filter = $this->getFilter();
        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'package_qty_from':
                        $restQuery->setFilter('package_qty', '>=', $value);
                        break;
                    case 'package_qty_to':
                        $restQuery->setFilter('package_qty', '<=', $value);
                        break;
                    case 'weight_from':
                        $restQuery->setFilter('weight', '>=', $value);
                        break;
                    case 'weight_to':
                        $restQuery->setFilter('weight', '<=', $value);
                        break;
                    case 'cost_from':
                        $restQuery->setFilter('cost', '>=', $value);
                        break;
                    case 'cost_to':
                        $restQuery->setFilter('cost', '<=', $value);
                        break;
                    case 'required_shipping_at':
                    case 'delivery_at':
                        $value = array_filter($value);
                        if ($value) {
                            $restQuery->setFilter($key, '>=', $value[0]);
                            $restQuery->setFilter($key, '<=', (new Carbon($value[1]))->modify('+1 day')->format('Y-m-d'));
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

        $orderIds = $shipments->pluck('delivery.order_id')->all();
        $orders = $orderService->orders(
            (new RestQuery())
                ->addFields(OrderDto::class, 'id', 'customer_id', 'delivery_type')
                ->setFilter('id', $orderIds)
        )->keyBy('id');

        $customerIds = $orders->pluck('customer_id')->all();
        $customers = $customerService->customers(
            (new RestQuery())
                ->addFields(CustomerDto::class, 'id', 'user_id')
                ->setFilter('id', $customerIds)
        )->keyBy('id');

        $userIds = $customers->pluck('user_id')->all();
        $users = $userService->users(
            (new RestQuery())
                ->addFields(UserDto::class, 'id', 'full_name', 'phone')
                ->setFilter('id', $userIds)
        )->keyBy('id');

        $storesIds = $shipments->pluck('store_id')->all();
        $stores = $storeService->stores(
            (new RestQuery)->addFields(StoreDto::class, 'id', 'name')
                ->setFilter('id', $storesIds)
        )->keyBy('id');

        $shipments = $shipments->map(function (ShipmentDto $shipment) use ($orders, $customers, $users, $stores) {
            $delivery = $shipment->delivery();

            $data['order'] = [
                'id' => $delivery->order_id,
                'number' => $orders[$delivery->order_id]->number,
            ];

            $data['shipment'] = [
                'id' => $shipment->id,
                'number' => $shipment->number,
            ];

            $customerId = $orders[$delivery->order_id]->customer_id;
            $user = $users[$customers[$customerId]->user_id];
            $data['customer'] = [
                'id' => $customerId,
                'full_name' => $user->full_name,
            ];

            $data['status'] = ShipmentStatus::statusById($shipment->status);
            $data['package_qty'] = $shipment->package_qty;
            $data['weight'] = $shipment->weight;
            $data['is_problem'] = $shipment->is_problem;
            $data['cost'] = $shipment->cost;

            $deliveryType = DeliveryType::typeById($orders[$delivery->order_id]->delivery_type);
            $data['delivery_type'] = [
                'id' => $deliveryType->id,
                'name' => $deliveryType->name,
            ];

            $deliveryMethod = DeliveryMethod::methodById($delivery->delivery_method);
            $data['delivery_method'] = [
                'id' => $deliveryMethod->id,
                'name' => $deliveryMethod->name,
            ];

            $deliveryServiceLastMile = DeliveryService::serviceById($delivery->delivery_service);
            $data['delivery_service_last_mile'] = [
                'id' => $deliveryServiceLastMile->id,
                'name' => $deliveryServiceLastMile->name,
            ];

            if ($shipment->delivery_service_zero_mile) {
                $deliveryServiceZeroMile = DeliveryService::serviceById(
                    $shipment->delivery_service_zero_mile
                );
                $data['delivery_service_zero_mile'] = [
                    'id' => $deliveryServiceZeroMile->id,
                    'name' => $deliveryServiceZeroMile->name,
                ];
            } else {
                $data['delivery_service_zero_mile'] = $data['delivery_service_last_mile'];
            }

            $store = $stores[$shipment->store_id];
            $data['store'] = [
                'id' => $store['id'],
                'name' => $store['name'],
            ];

            $data['delivery_address'] = $delivery->getAddressString();
            $data['required_shipping_at'] = (new Carbon($shipment->required_shipping_at))->format('H:i:s Y-m-d');
            $data['delivery_at'] = (new Carbon($delivery->delivery_at))->format('H:i:s Y-m-d');

            return $data;
        });

        return $shipments;
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
        $result = [
            'shipments' => $shipments,
        ];
        if ($request->get('page') == 1) {
            $result['pager'] = $shipmentService->shipmentsCount($restQuery);
        }

        return response()->json($result);
    }
}