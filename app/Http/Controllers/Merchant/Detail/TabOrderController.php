<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Core\CustomerHelper;
use App\Core\UserHelper;
use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
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
     * AJAX подгрузка информации для фильтрации отправлений
     *
     * @throws Exception
     */
    public function loadOrdersData(int $merchantId, Request $request): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $restQuery = $this->makeRestQuery($request, $merchantId);

        return response()->json([
            'shipmentStatuses' => ShipmentStatus::allStatuses(),
            'customerFullNames' => $this->getCustomersNames($restQuery),
            'deliveryTypes' => DeliveryType::allTypes(),
            'deliveryMethods' => DeliveryMethod::allMethods(),
            'deliveryServices' => DeliveryService::allServices(),
            'stores' => $this->loadStores($merchantId),
        ]);
    }

    /**
     * AJAX пагинация списка заказов
     *
     * @throws Exception
     */
    public function page(int $merchantId, Request $request, ShipmentService $shipmentService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

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

    protected function getCustomersNames(DataQuery $restQuery): array
    {
        /** @var ShipmentService $shipmentService */
        $shipmentService = resolve(ShipmentService::class);

        /** @var OrderService $orderService */
        $orderService = resolve(OrderService::class);

        $restQuery->addFields(ShipmentDto::entity(), 'id')
            ->include('delivery');

        $shipments = $shipmentService->shipments($restQuery);
        if ($shipments->isEmpty()) {
            return [];
        }

        $orderIds = $shipments->pluck('delivery.order_id')->all();
        $orders = $orderService->orders(
            (new RestQuery())
                ->addFields(OrderDto::class, 'id', 'customer_id')
                ->setFilter('id', $orderIds)
        )->keyBy('id');

        $customerIds = $orders->pluck('customer_id')->all();
        $customers = CustomerHelper::getCustomersByIds($customerIds, ['id', 'user_id']);

        $userIds = $customers->pluck('user_id')->all();
        $users = UserHelper::getUsersByIds($userIds, ['id', 'full_name']);

        return array_filter($users->map(function (UserDto $user) {
            return $user->full_name;
        })->all());
    }

    /**
     * TODO пересмотреть область видимости метода
     * @return Collection|StoreDto[]
     */
    public function loadStores(int $merchantId): array|Collection
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        /** @var Collection|StoreDto[] $stores */
        static $stores = null;

        if (is_null($stores)) {
            /** @var StoreService $storeService */
            $storeService = resolve(StoreService::class);

            $stores = $storeService->stores(
                (new RestQuery())->addFields(StoreDto::entity(), 'id', 'name'),
                $merchantId
            )->keyBy('id');
        }

        return $stores;
    }

    /**
     * @throws Exception
     */
    protected function makeRestQuery(Request $request, int $merchantId): DataQuery
    {
        $page = $request->get('page', 1);
        $restQuery = (new RestQuery())->setFilter('merchant_id', $merchantId)
            ->setFilter('is_canceled', false)
            ->pageNumber($page, 20);

        $filter = $this->getFilter();
        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'customer_full_name':
                        /** @var UserService $userService */
                        $userService = resolve(UserService::class);

                        /** @var CustomerService $customerService */
                        $customerService = resolve(CustomerService::class);

                        $userIds = $userService->users(
                            (new RestQuery())->addFields(UserDto::class, 'id')
                                ->setFilter('full_name', $value)
                        )->pluck('id')
                            ->all();

                        if (!$userIds) {
                            $restQuery->setFilter('customer_id', -1);
                            break;
                        }

                        $customerIds = $customerService->customers(
                            (new RestQuery())->addFields(CustomerDto::class, 'id')
                                ->setFilter('user_id', $userIds)
                        )->pluck('id')
                            ->all();

                        $existCustomerIds = $restQuery->getFilter('customer_id') ? $restQuery->getFilter('customer_id')[0][1] : [];
                        if ($existCustomerIds) {
                            $customerIds = array_values(array_intersect($existCustomerIds, $customerIds));
                        }
                        $customerIds = $customerIds ?? -1;
                        $restQuery->setFilter('customer_id', $customerIds);
                        break;
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
                    case 'is_problem':
                        $restQuery->setFilter($key, to_boolean($value));
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
     * @return array
     */
    protected function getFilter(): array
    {
        $allDeliveryServices = DeliveryService::allServices();
        return Validator::make(
            request('filter') ?? [],
            [
                'order_number' => 'string|someone',
                'number' => 'string|someone',
                'status' => 'array|someone',
                'status.' => Rule::in(array_keys(ShipmentStatus::allStatuses())),
                'is_problem' => 'string|someone',
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
                'required_shipping_at.' => 'date',
                'delivery_at' => 'array|someone',
                'delivery_at.' => 'date',
            ]
        )->attributes();
    }

    protected function loadShipments(DataQuery $restQuery): Collection
    {
        /** @var ShipmentService $shipmentService */
        $shipmentService = resolve(ShipmentService::class);

        /** @var OrderService $orderService */
        $orderService = resolve(OrderService::class);

        /** @var StoreService $storeService */
        $storeService = resolve(StoreService::class);

        $shipments = $shipmentService->shipments(
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
            )->include('delivery')
        );

        if ($shipments->isEmpty()) {
            return collect();
        }

        $orderIds = $shipments->pluck('delivery.order_id')->all();
        $orders = $orderService->orders(
            (new RestQuery())
                ->addFields(OrderDto::class, 'id', 'customer_id', 'delivery_type')
                ->setFilter('id', $orderIds)
        )->keyBy('id');

        $customerIds = $orders->pluck('customer_id')->all();
        $customers = CustomerHelper::getCustomersByIds($customerIds, ['id', 'user_id']);

        $userIds = $customers->pluck('user_id')->all();
        $users = UserHelper::getUsersByIds($userIds, ['id', 'full_name', 'phone']);

        $storesIds = $shipments->pluck('store_id')->all();
        $stores = $storeService->stores(
            (new RestQuery())->addFields(StoreDto::class, 'id', 'name')
                ->setFilter('id', $storesIds)
        )->keyBy('id')
            ->all();

        $orders = $orders->all();
        $customers = $customers->all();
        $users = $users->all();

        return $shipments->map(function (ShipmentDto $shipment) use ($orders, $customers, $users, $stores) {
            $delivery = $shipment->delivery;

            $data['order'] = [
                'id' => $delivery->order_id,
                'number' => $orders[$delivery->order_id]->number,
            ];

            $data['shipment'] = [
                'id' => $shipment->id,
                'number' => $shipment->number,
            ];

            $customerId = array_key_exists($orders[$delivery->order_id]->customer_id, $customers) ?
                $orders[$delivery->order_id]->customer_id :
                'N/A';
            $userFullName = array_key_exists($customerId, $customers) &&
                    array_key_exists($customers[$customerId]->user_id, $users) &&
                    $users[$customers[$customerId]->user_id]->full_name ?
                $users[$customers[$customerId]->user_id]->full_name :
                'N/A';
            $data['customer'] = [
                'id' => $customerId,
                'full_name' => $userFullName,
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
    }
}
