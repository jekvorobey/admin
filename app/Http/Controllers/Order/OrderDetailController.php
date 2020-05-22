<?php

namespace App\Http\Controllers\Order;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Dto\Lists\PointDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\BasketItemDto;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Dto\Payment\PaymentDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OrderDetailController
 * @package App\Http\Controllers\Order
 */
class OrderDetailController extends Controller
{
    /**
     * @param  int  $id
     * @return mixed
     * @throws \Exception
     */
    public function detail(int $id)
    {
        $order = $this->getOrder($id);
        $this->title = 'Заказ '.$order->number.' от '.$order->created_at;

        return $this->render('Order/Detail', [
            'iOrder' => $order,
            'kpis' => $order ? $this->getKpis($order) : [],
        ]);
    }

    /**
     * Изменить статус заказа
     * @param  int  $id
     * @param  Request  $request
     * @param  ShipmentService  $shipmentService
     * @return JsonResponse
     */
    public function changeStatus(int $id, Request $request, OrderService $orderService): JsonResponse
    {
        $data = $this->validate($request, [
            'status' => Rule::in(array_keys(OrderStatus::allStatuses())),
        ]);
        $order = new OrderDto();
        $order->status = $data['status'];
        $orderService->updateOrder($id, $order);

        return response()->json([
            'status' => OrderStatus::allStatuses()[$data['status']]->toArray(),
        ]);
    }

    /**
     * Вручную оплатить заказ
     * Примечание: оплата по заказам автоматически должна поступать от платежной системы!
     * @param  int  $id
     * @param  ShipmentService  $shipmentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function pay(int $id, OrderService $orderService): JsonResponse
    {
        $orderService->payOrder($id);

        return response()->json([
            'order' => $this->getOrder($id),
        ]);
    }

    /**
     * Отменить заказ
     * @param  int  $id
     * @param  ShipmentService  $shipmentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function cancel(int $id, OrderService $orderService): JsonResponse
    {
        $orderService->cancelOrder($id);

        return response()->json([
            'order' => $this->getOrder($id),
        ]);
    }

    /**
     * todo Разбить метод на несколько мелких методов
     * @param  int  $id
     * @return OrderDto
     * @throws \Exception
     */
    protected function getOrder(int $id): OrderDto
    {
        /** @var OrderService $orderService */
        $orderService = resolve(OrderService::class);

        $restQuery = $orderService
            ->newQuery()
            ->setFilter('id', $id)
            ->include('all');
        $orders = $orderService->orders($restQuery);
        if (!$orders->count()) {
            throw new NotFoundHttpException();
        }

        /** @var OrderDto $order */
        $order = $orders->first();

        $this->addOrderUserInfo($order);
        $this->addOrderDeliveryInfo($order);
        $this->addOrderCommonInfo($order);
        $this->addOrderProductInfo($order);

        return $order;
    }

    protected function addOrderUserInfo(OrderDto $order): void
    {
        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);
        /** @var UserService $userService */
        $userService = resolve(UserService::class);

        //Получаем реферальных партнеров заказов
        $referralIds = $order->basket->items->pluck('referrer_id')->filter()
            ->merge($order->promoCodes->pluck('owner_id')->filter())
            ->unique();
        // Получаем покупателя заказа
        $customerQuery = $customerService->newQuery()
            ->setFilter('id', $order->customer_id);
        /** @var CustomerDto $customer */
        $customer = $customerService->customers($customerQuery)->first();

        // Получаем самих пользователей
        $userIds = collect($customer->user_id)
            ->merge($referralIds)
            ->unique()
            ->values()
            ->all();
        $users = collect();
        if ($userIds) {
            $userQuery = $userService->newQuery()
                ->setFilter('id', $userIds);
            /** @var Collection|UserDto[] $users */
            $users = $userService->users($userQuery)->keyBy('id');
        }

        if ($customer && $users->has($customer->user_id)) {
            $customer['user'] = $users[$customer->user_id];
            $order['customer'] = $customer;
        }

        $referrals = collect();
        $referralIds = $order->basket->items->pluck('referrer_id')->filter()->unique();
        foreach ($referralIds as $referralId) {
            $referral = [
                'referral_id' => $referralId,
                'user' => $users->has($referralId) ?
                    $users[$referralId] : null,
            ];
            foreach ($order->basket->items as $item) {
                if ($item->referrer_id == $referralId) {
                    $referral['basketItems'][] = $item;
                }
            }
            foreach ($order->promoCodes as $promoCode) {
                if ($promoCode->owner_id == $referralId) {
                    $referral['promoCodes'][] = $promoCode;
                }
            }
            $referrals->push($referral);
        }
        $order['referrals'] = $referrals;
    }

    protected function addOrderDeliveryInfo(OrderDto $order): void
    {
        /** @var ListsService $listsService */
        $listsService = resolve(ListsService::class);
        /** @var StoreService $storeService */
        $storeService = resolve(StoreService::class);

        // Получаем склады заказа
        $storeIds = collect();
        foreach ($order->deliveries as $delivery) {
            $storeIds->merge($delivery->shipments->pluck('store_id'));
        }
        $storeIds = $storeIds->unique();
        $storeQuery = $storeService->newQuery()
            ->setFilter('id', $storeIds);
        $stores = $storeService->stores($storeQuery);

        /** @var Collection|PointDto[] $points */
        $points = collect();
        $pointIds = $order->deliveries->pluck('point_id')->filter()->unique()->values()->all();
        if ($pointIds) {
            $points = $listsService->points($listsService->newQuery()->setFilter('id', $pointIds))
                ->keyBy('id');
        }

        $shipments = collect();
        $merchantIds = collect();
        $cities = collect();
        $courierDelivery = null;
        $pickupDelivery = null;
        foreach ($order->deliveries as $delivery) {
            if ($delivery->delivery_method == DeliveryMethod::METHOD_PICKUP) {
                if ($points->has($delivery->point_id)) {
                    $cities->push($points[$delivery->point_id]->getCityWithType());
                }

                if (is_null($courierDelivery)) {
                    $pickupDelivery = $delivery;
                }
            } else {
                $cities->push($delivery->getCity());
                $deliveryAddress = $delivery->delivery_address;
                $deliveryAddress['address_string'] = join(', ', array_filter([
                    isset($deliveryAddress['post_index']) ? $deliveryAddress['post_index'] : '',
                    isset($deliveryAddress['region']) ? $delivery->delivery_address['region'] : '',
                    isset($deliveryAddress['city']) ? $delivery->delivery_address['city'] : '',
                    isset($deliveryAddress['street']) ? $delivery->delivery_address['street'] : '',
                    isset($deliveryAddress['house']) ? $delivery->delivery_address['house'] : '',
                    isset($deliveryAddress['block']) ? $delivery->delivery_address['block'] : '',
                    isset($deliveryAddress['flat']) ? $delivery->delivery_address['flat'] : '',
                ]));
                $delivery->delivery_address = $deliveryAddress;

                if (is_null($courierDelivery)) {
                    $courierDelivery = $delivery;
                }
            }

            $delivery->status = $delivery->status();
            $delivery->delivery_method = $delivery->deliveryMethod();
            $delivery->delivery_service = $delivery->deliveryService();
            $delivery->payment_status = $delivery->paymentStatus();
            $delivery->delivery_at = date2str(new Carbon($delivery->delivery_at));

            foreach ($delivery->shipments as $shipment) {
                $merchantIds->push($shipment->merchant_id);
                $shipment->status = $shipment->status();
                $shipment->delivery_service_zero_mile = $shipment->deliveryServiceZeroMile();
                $shipment['store'] = $stores->has($shipment->store_id) ? $stores[$shipment->store_id] : null;
                $shipment['cargo'] = $shipment->cargo;
                $shipment->payment_status = $shipment->paymentStatus();
            }
            $shipments = $shipments->merge($delivery->shipments);
        }

        $order['merchants'] = $this->getMerchants($merchantIds->unique()->all())->values();
        $order['firstDelivery'] = $order->deliveries->first();
        $order['courierDelivery'] = $courierDelivery;
        $order['pickupDelivery'] = $pickupDelivery;
        $order['shipments'] = $shipments;
        $order['delivery_cities'] = $cities->unique()->join(', ');
    }

    protected function addOrderCommonInfo(OrderDto $order): void
    {
        $order->confirmation_type = $order->confirmationType();
        $order->status = $order->status();
        $order->status_at = dateTime2str(new Carbon($order->status_at));
        if ($order->is_problem_at) {
            $order->is_problem_at = dateTime2str(new Carbon($order->is_problem_at));
        }
        if ($order->is_canceled_at) {
            $order->is_canceled_at = dateTime2str(new Carbon($order->is_canceled_at));
        }

        $order->delivery_type = $order->deliveryType();
        $order['delivery_services'] = $order->deliveries->map(function (DeliveryDto $delivery) {
            return $delivery->delivery_service;
        })->unique();
        $order['delivery_methods'] = $order->deliveries->map(function (DeliveryDto $delivery) {
            return $delivery->delivery_method;
        })->unique();

        $order->created_at = dateTime2str(new Carbon($order->created_at));
        $order->updated_at = dateTime2str(new Carbon($order->updated_at));

        $order['payment_methods'] = $order->payments->map(function (PaymentDto $payment) {
            return $payment->paymentMethod()->name;
        })->unique()->join(', ');
        $order['discount'] = $order->getDiscount();
        $order['delivery_discount'] = $order->getDeliveryDiscount();
        $order['product_cost'] = $order->cost - $order->delivery_cost;
        $order['product_price'] = $order->price - $order->delivery_price;
        $order['product_discount'] = $order['product_cost'] - $order['product_price'];
        $order['to_pay'] = $order->isPayed() ? 0 : $order->price;
        $order->payment_status = $order->paymentStatus();
        if ($order->payment_status_at) {
            $order->payment_status_at = dateTime2str(new Carbon($order->payment_status_at));
        }

        $order['weight'] = $order->basket->items->isNotEmpty() ? $order->basket->items->reduce(function (
            int $sum,
            BasketItemDto $item
        ) {
            return $sum + $item->getProductWeight();
        }, 0) : 0;
        $order['total_qty'] = $order->basket->items->isNotEmpty() ? $order->basket->items->reduce(function (
            int $sum,
            BasketItemDto $item
        ) {
            return $sum + $item->qty;
        }, 0) : 0;
    }

    protected function addOrderProductInfo(OrderDto $order): void
    {
        /** @var ProductService $productService */
        $productService = resolve(ProductService::class);

        if ($order->basket->items->isNotEmpty()) {
            $offersIds = $order->basket->items->pluck('offer_id')->toArray();
            $restQuery = $productService->newQuery()
                ->addFields(ProductDto::entity(), 'vendor_code')
                ->include(CategoryDto::entity(), BrandDto::entity(), 'mainImage');
            $productsByOffers = $productService->productsByOffers($restQuery, $offersIds);

            $order->basket->items = $order->basket->items->map(function (BasketItemDto $basketItemDto) use (
                $productsByOffers
            ) {
                $product = $basketItemDto->product;
                $basketItemDto['product'] = $productsByOffers->has($basketItemDto->offer_id) ?
                    $productsByOffers[$basketItemDto->offer_id]->product : [];
                foreach ($product as $key => $value) {
                    $basketItemDto['product'][$key] = $value;
                }

                return $basketItemDto;
            });

            foreach ($order->deliveries as $delivery) {
                foreach ($delivery->shipments as $shipment) {
                    foreach ($shipment->basketItems as $basketItem) {
                        $product = $basketItem->product;
                        $basketItem['product'] = $productsByOffers->has($basketItem->offer_id) ?
                            $productsByOffers[$basketItem->offer_id]->product : [];
                        foreach ($product as $key => $value) {
                            $basketItem['product'][$key] = $value;
                        }
                    }

                    foreach ($shipment->packages as $package) {
                        foreach ($package->items as $item) {
                            $product = $item->basketItem->product;
                            $item->basketItem['product'] = $productsByOffers->has($item->basketItem->offer_id) ?
                                $productsByOffers[$item->basketItem->offer_id]->product : [];
                            foreach ($product as $key => $value) {
                                $item->basketItem['product'][$key] = $value;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param  OrderDto  $order
     * @return Collection
     * @throws \Exception
     */
    protected function getKpis(OrderDto $order): Collection
    {
        $kpis = collect([
            OrderStatus::CREATED => [
                'status' => OrderStatus::statusById(OrderStatus::CREATED),
                'status_at' => $order->created_at,
            ]
        ]);
        if ($order->history->isNotEmpty()) {
            foreach ($order->history as $historyDto) {
                $data = $historyDto->data;
                if (mb_strtolower($historyDto->entity_type) == OrderDto::entity() && isset($data['status'])) {
                    $kpis->put($data['status'], [
                        'status' => OrderStatus::statusById($data['status']),
                        'status_at' => dateTime2str(new Carbon($data['status_at'])),
                    ]);
                }
            }
        }

        return $kpis->sortBy('status_at')->values();
    }
}
