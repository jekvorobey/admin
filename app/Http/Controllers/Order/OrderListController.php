<?php

namespace App\Http\Controllers\Order;

use App\Core\CustomerHelper;
use App\Core\UserHelper;
use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\AbstractDto;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Dto\Lists\PointDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\DeliveryType;
use Greensight\Oms\Dto\Order\OrderConfirmationType;
use Greensight\Oms\Dto\Order\OrderType;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\PaymentService\PaymentService;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Pim\Dto\BrandDto;
use Pim\Services\BrandService\BrandService;

/**
 * Class OrderListController
 * @package App\Http\Controllers\Order
 */
class OrderListController extends Controller
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(
        Request $request,
        OrderService $orderService,
        BrandService $brandService,
        StoreService $storeService,
        PaymentService $paymentService
    ) {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $this->title = 'Список заказов';
        $this->loadOrderStatuses = true;
        $this->loadBasketTypes = true;
        $this->loadPaymentStatuses = true;
        $this->loadDeliveryTypes = true;
        $this->loadDeliveryMethods = true;
        $this->loadDeliveryServices = true;

        $restQuery = $this->makeRestQuery($orderService, true);
        $pager = $orderService->ordersCount($restQuery);
        $orders = $this->loadOrders($restQuery);

        return $this->render('Order/List', [
            'iOrders' => $orders,
            'iCurrentPage' => $this->getPage(),
            'iPager' => $pager,
            'merchants' => $this->getMerchants(),
            'confirmationTypes' => OrderConfirmationType::allTypes(),
            'paymentMethods' => $paymentService->getPaymentMethods(),
            'orderTypes' => OrderType::allTypes(),
            'stores' => $storeService->newQuery()->addFields(StoreDto::entity(), 'id', 'address')->stores(),
            'brands' => $brandService->newQuery()->addFields(BrandDto::entity(), 'id', 'name')->brands(),
            'iFilter' => $this->getFilter(true),
            'iSort' => $request->get('sort', 'created_at'),
        ]);
    }

    public function byOffers(OrderService $orderService, Request $request): array
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        return $orderService->ordersByOffers(['offersIds' => $request->input('offersIds'), 'page' => $request->input('page')]);
    }

    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    /**
     * @throws Exception
     */
    public function page(OrderService $orderService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $restQuery = $this->makeRestQuery($orderService);
        $orders = $this->loadOrders($restQuery);
        $result = [
            'orders' => $orders,
        ];
        if ($this->getPage() == 1) {
            $result['pager'] = $orderService->ordersCount($restQuery);
        }

        return response()->json($result);
    }

    /**
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
                'status.*' => Rule::in(array_keys(OrderStatus::allStatuses())),
                'price_from' => 'numeric|sometimes',
                'price_to' => 'numeric|sometimes',
                'offer_xml_id' => 'string|sometimes',
                'product_vendor_code' => 'string|sometimes',
                'brands' => 'array|sometimes',
                'brands.*' => 'integer',
                'merchants' => 'array|sometimes',
                'merchants.*' => 'integer',
                'payment_method_id' => 'array',
                'payment_method_id.*' => 'integer',
                'stores' => 'array|sometimes',
                'stores.*' => 'integer',
                'delivery_type.*' => Rule::in(array_keys(DeliveryType::allTypes())),
                'delivery_service.*' => Rule::in(array_keys(DeliveryService::allServices())),
                'delivery_city' => 'string|sometimes',
                'delivery_city_string' => 'string|sometimes',
                'psd' => 'array|sometimes',
                'psd.*' => 'string|nullable',
                'pdd' => 'array|sometimes',
                'pdd.*' => 'string|nullable',
                'is_canceled' => 'string|sometimes',
                'is_problem' => 'string|sometimes',
                'is_require_check' => 'string|sometimes',
                'confirmation_type.*' => Rule::in(array_keys(OrderConfirmationType::allTypes())),
                'manager_comment' => 'string|sometimes',
                'type.*' => Rule::in(array_keys(OrderType::allTypes())),
                'delivery_xml_id' => 'string|sometimes',
            ]
        );
    }

    protected function loadOrders(DataQuery $restQuery): Collection
    {
        /** @var OrderService $orderService */
        $orderService = resolve(OrderService::class);
        /** @var ListsService $listsService */
        $listsService = resolve(ListsService::class);

        $orders = $orderService->orders($restQuery);
        if ($orders->isEmpty()) {
            return collect();
        }

        //Получаем реферальных партнеров заказов
        $referralIds = collect();
        foreach ($orders as $order) {
            $referralIds->merge($order->basket->items->pluck('referrer_id')->filter()->unique());
            $referralIds->merge($order->promoCodes->pluck('owner_id')->filter()->unique());
        }
        $referralIds = $referralIds->unique();

        // Получаем покупателей и реферальных партнеров заказов
        $customerIds = $orders->pluck('customer_id')->merge($referralIds)->unique()->all();
        $customers = CustomerHelper::getCustomersByIds($customerIds);

        // Получаем самих пользователей
        $userIds = $customers->pluck('user_id')->all();
        $users = UserHelper::getUsersByIds($userIds);

        /** @var Collection|PointDto[] $points */
        $points = collect();
        $deliveries = collect();
        foreach ($orders as $order) {
            $deliveries = $deliveries->merge($order->deliveries);
        }
        $pointIds = $deliveries->pluck('point_id')->filter()->unique()->values()->all();
        if ($pointIds) {
            $points = $listsService->points($listsService->newQuery()->setFilter('id', $pointIds))
                ->keyBy('id');
        }

        return $orders->map(function (OrderDto $order) use ($users, $customers, $points) {
            $data = $order->toArray();

            $data['customer'] = $customers->has($order->customer_id) && $users->has($customers[$order->customer_id]->user_id)
                ? $users[$customers[$order->customer_id]->user_id] : null;

            $delivery_dates = collect();
            $cities = collect();
            $psdLast = null;
            $pddLast = null;
            foreach ($order->deliveries as $delivery) {
                $delivery_dates->push(
                    date2str(Carbon::createFromFormat(AbstractDto::DATE_TIME_FORMAT, $delivery->delivery_at))
                );
                if (is_null($pddLast) || $pddLast < $delivery->pdd) {
                    $pddLast = $delivery->pdd;
                }

                if ($delivery->delivery_method == DeliveryMethod::METHOD_PICKUP) {
                    if ($points->has($delivery->point_id)) {
                        $cities->push($points[$delivery->point_id]->getCityWithType());
                    }
                } else {
                    $cities->push($delivery->getCity());
                }

                foreach ($delivery->shipments as $shipment) {
                    if (is_null($psdLast) || $psdLast < $shipment->psd) {
                        $psdLast = $shipment->psd;
                    }
                }
            }

            $data['status'] = $order->status()->toArray();
            $data['confirmation_type'] = $order->confirmationType()->toArray();
            $data['payment_status'] = $order->paymentStatus()->toArray();
            $data['delivery_methods'] = $order->deliveries->map(function (DeliveryDto $delivery) {
                return $delivery->deliveryMethod()->name;
            })->unique()->join(', ');
            $data['delivery_services'] = $order->deliveries->map(function (DeliveryDto $delivery) {
                return DeliveryService::serviceById($delivery->delivery_service)->name;
            })->unique()->join(', ');

            $data['payment_methods'] = $order->paymentMethod->name;

            $data['created_at'] = date_time2str(new Carbon($order->created_at));
            $data['updated_at'] = date_time2str(new Carbon($order->updated_at));
            $data['status_at'] = date_time2str(new Carbon($order->status_at));
            $data['delivery_dates'] = $delivery_dates->unique()->join(', ');
            $data['delivery_cities'] = $cities->unique()->join(', ');
            $data['product_price'] = $order->price - $order->delivery_price;
            $data['shipments_qty'] = $order->deliveries->sum(function (DeliveryDto $delivery) {
                return $delivery->shipments->count();
            });
            $data['psd_last'] = $psdLast ? date_time2str($psdLast) : '';
            $data['pdd_last'] = $pddLast ? date_time2str($pddLast) : '';
            $data['latestHistory'] = $order->latestHistory ? $order->latestHistory->toArray() : null;
            if ($order->latestHistory) {
                $data['latestHistory']['updated_at'] = date_time2str(new Carbon($order->latestHistory->updated_at));
                $data['latestHistory']['user'] = $users->has($order->latestHistory->user_id) ?
                    $users[$order->latestHistory->user_id] : null;
            }
            $sources = [];
            $referralIds = $order->basket->items->pluck('referrer_id')->filter()->unique();
            foreach ($referralIds as $referralId) {
                /** @var CustomerDto|null $referralCustomer */
                $referralCustomer = $customers->has($referralId) ? $customers[$referralId] : null;
                $sources[] = [
                    'user' => $referralCustomer && $users->has($referralCustomer->user_id) ?
                        $users[$referralCustomer->user_id] : null,
                ];
            }
            foreach ($order->promoCodes as $promoCode) {
                /** @var CustomerDto|null $referralCustomer */
                $referralCustomer = $customers->has($promoCode->owner_id) ? $customers[$promoCode->owner_id] : null;
                $sources[] = [
                    'user' => $referralCustomer && $users->has($referralCustomer->user_id) ?
                        $users[$referralCustomer->user_id] : null,
                    'promo_code' => $promoCode->code,
                ];
            }
            $data['sources'] = $sources;

            return $data;
        });
    }

    /**
     * @throws Exception
     */
    protected function makeRestQuery(OrderService $orderService, bool $withDefaultFilter = false): DataQuery
    {
        $restQuery = $orderService->newQuery()->include(
            'payments',
            'paymentMethod',
            'deliveries.shipments',
            //'history',
            'basketitem',
            'promoCodes'
        );

        $page = $this->getPage();
        $restQuery->pageNumber($page, 20);

        $filter = $this->getFilter($withDefaultFilter);
        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'created_between':
                        $value = array_filter($value);
                        if ($value) {
                            $restQuery->setFilter('created_at', '>=', $value[0]);
                            $restQuery->setFilter('created_at', '<=', end_of_day_filter($value[1]));
                        }
                        break;
                    case 'created_at':
                        if ($value) {
                            $restQuery->setFilter($key, 'like', "{$value}%");
                        }
                        break;
                    case 'price_from':
                        if ($value) {
                            $restQuery->setFilter('price', '>=', $value);
                        }
                        break;
                    case 'price_to':
                        if ($value) {
                            $restQuery->setFilter('price', '<=', $value);
                        }
                        break;
                    case 'manager_comment':
                        $restQuery->setFilter($key, 'like', "%{$value}%");
                        break;
                    case 'merchants':
                        $restQuery->setFilter('merchant_id', $value);
                        break;
                    case 'delivery_city_string':
                        break;
                    case 'is_canceled':
                    case 'is_problem':
                    case 'is_require_check':
                        $restQuery->setFilter($key, to_boolean($value));
                        break;
                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
        }
        $restQuery->addSort('created_at', 'desc');

        return $restQuery;
    }
}
