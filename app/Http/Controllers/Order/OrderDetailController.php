<?php

namespace App\Http\Controllers\Order;

use App\Core\CustomerHelper;
use App\Core\UserHelper;
use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\RoleDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Dto\Lists\PointDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\BasketItemDto;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Dto\Document\DocumentDto;
use Greensight\Oms\Dto\History\HistoryDto;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Dto\Payment\PaymentCancelReason;
use Greensight\Oms\Dto\Payment\PaymentMethod;
use Greensight\Oms\Dto\Payment\PaymentStatus;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Greensight\Store\Dto\Package\PackageDto;
use Greensight\Store\Dto\Package\PackageType;
use Greensight\Store\Services\PackageService\PackageService;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OrderDetailController
 * @package App\Http\Controllers\Order
 */
class OrderDetailController extends Controller
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function detail(int $id, OrderService $orderService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $this->loadOrderStatuses = true;
        $this->loadBasketTypes = true;
        $this->loadPaymentStatuses = true;
        $this->loadDeliveryStatuses = true;
        $this->loadShipmentStatuses = true;
        $this->loadDeliveryServices = true;
        $this->loadAllPaymentMethods = true;

        $order = $this->getOrder($id);

        $this->title = 'Заказ ' . $order->number . ' от ' . $order->created_at;

        $order['paymentCancelReasons'] = collect(PaymentCancelReason::allReasons())->filter(function ($reason) use ($order) {
            return in_array($reason->code, $order->payments->pluck('cancel_reason')->all());
        });

        return $this->render('Order/Detail', [
            'iOrder' => $order,
            'iOrderInfo' => $orderService->order($id),
            'kpis' => $order ? $this->getKpis($order) : [],
        ]);
    }

    /**
     * Изменить статус заказа
     */
    public function changeStatus(int $id, Request $request, OrderService $orderService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

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
     * Отметить заказ как оплаченный (для рассрочки и по счету-оферте)
     * @throws Exception
     */
    public function markAsPaid(
        int $id,
        Request $request,
        OrderService $orderService,
        ShipmentService $shipmentService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);
        $this->hasRole([RoleDto::ROLE_FINANCIER, RoleDto::ROLE_ADMINISTRATOR]);

        $data = $this->validate($request, [
            'payment_method' => 'required|integer',
        ]);

        $order = new OrderDto();
        $order->payment_status = PaymentStatus::PAID;
        $orderService->updateOrder($id, $order);

        $order = $this->getOrder($id);
        if ($data['payment_method'] === PaymentMethod::BANK_TRANSFER_FOR_LEGAL) {
            //$orderService->generateOrderUPD($id);
            /** @var ShipmentDto $shipment */
            foreach ($order['shipments'] as $shipment) {
                $shipmentService->generateShipmentUPD($shipment->id);
            }
        }

        return response()->json([
            'order' => $order,
        ]);
    }

    /**
     * Отметить заказ как оплаченный и отвязать от Юкассы
     * @throws Exception
     */
    public function markAsPaidForce(int $id, OrderService $orderService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);
        $this->hasRole(RoleDto::ROLE_ADMINISTRATOR);

        $orderService->payOrder($id);

        return response()->json([
            'order' => $this->getOrder($id),
        ]);
    }

    /**
     * Вручную подтвердить платеж
     * @throws Exception
     */
    public function capturePayment(int $id, OrderService $orderService): JsonResponse
    {
        $orderService->capturePayment($id);

        return response()->json([
            'order' => $this->getOrder($id),
        ]);
    }

    /**
     * Отменить заказ
     * @throws Exception
     */
    public function cancel(int $id, Request $request, OrderService $orderService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $data = $this->validate($request, [
            'orderReturnReason' => 'required|int',
        ]);

        $orderService->cancelOrder($id, $data['orderReturnReason']);

        return response()->json([
            'order' => $this->getOrder($id),
        ]);
    }

    /**
     * Возврат выполненного заказа
     * @throws Exception
     */
    public function returnCompletedOrder(int $id, Request $request, OrderService $orderService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $this->validate($request, [
            'basketItemIds' => 'nullable|array',
            'basketItemIds.*' => 'int',
        ]);

        $orderService->returnCompletedOrder($id, $request->get('basketItemIds'));

        return response()->json([
            'order' => $this->getOrder($id),
        ]);
    }

    /**
     * Получить документ "Счет оферта"
     */
    public function invoiceOffer(int $orderId, OrderService $orderService): StreamedResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $invoiceOffer = $orderService->orderInvoiceOffer($orderId);

        return $this->getDocumentResponse($invoiceOffer);
    }

    /**
     * Получить документ "Универсальный передаточный документ"
     */
    public function upd(int $orderId, OrderService $orderService): StreamedResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $upd = $orderService->upd($orderId);

        return $this->getDocumentResponse($upd);
    }

    protected function getDocumentResponse(DocumentDto $documentDto): StreamedResponse
    {
        return response()->streamDownload(function () use ($documentDto) {
            echo file_get_contents($documentDto->absolute_url);
        }, $documentDto->original_name);
    }

    /**
     * @throws Exception
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

        $orderReturnReasons = $orderService->orderReturnReasons($orderService->newQuery());
        $order['orderReturnReasons'] = $orderReturnReasons;

        return $order;
    }

    protected function addOrderUserInfo(OrderDto $order): void
    {
        //Получаем реферальных партнеров заказов
        $referralIds = $order->basket->items->pluck('referrer_id')->filter()
            ->merge($order->promoCodes->pluck('owner_id')->filter())
            ->unique();
        // Получаем покупателя заказа
        $customerIds = collect($order->customer_id)
            ->merge($referralIds)
            ->unique()
            ->values()
            ->all();
        $customers = CustomerHelper::getCustomersByIds($customerIds);
        $customer = $customers->has($order->customer_id) ? $customers[$order->customer_id] : null;

        // Получаем самих пользователей
        $userIds = $customers->pluck('user_id')->all();
        $users = UserHelper::getUsersByIds($userIds);

        if ($customer && $users->has($customer->user_id)) {
            $customer['user'] = $users[$customer->user_id];
            $order['customer'] = $customer;
        }

        $referrals = collect();
        foreach ($referralIds as $referralId) {
            /** @var CustomerDto|null $referralCustomer */
            $referralCustomer = $customers->has($referralId) ? $customers[$referralId] : null;
            $referral = [
                'referral_id' => $referralId,
                'user' => $referralCustomer && $users->has($referralCustomer->user_id) ?
                    $users[$referralCustomer->user_id] : null,
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

    /**
     * @throws Exception
     */
    protected function addOrderDeliveryInfo(OrderDto $order): void
    {
        /** @var ListsService $listsService */
        $listsService = resolve(ListsService::class);
        /** @var StoreService $storeService */
        $storeService = resolve(StoreService::class);
        /** @var PackageService $packageService */
        $packageService = resolve(PackageService::class);

        //Справочник типов коробок
        $packages = $packageService->packages($packageService->newQuery()
            ->setFilter('type', PackageType::TYPE_BOX)
            ->addFields(PackageDto::entity(), 'id', 'name'))->keyBy('id');

        // Получаем склады заказа
        $storeIds = collect();
        foreach ($order->deliveries as $delivery) {
            $storeIds = $storeIds->merge($delivery->shipments->pluck('store_id'));
        }
        $storeIds = $storeIds->unique();
        $storeQuery = $storeService->newQuery()
            ->setFilter('id', $storeIds);
        $stores = $storeService->stores($storeQuery)->keyBy('id');

        /** @var Collection|PointDto[] $points */
        $points = collect();
        $pointIds = $order->deliveries->pluck('point_id')->filter()->unique()->values()->all();
        if ($pointIds) {
            $points = $listsService->points($listsService->newQuery()->setFilter('id', $pointIds))
                ->map(function (PointDto $point) {
                    $point->type = $point->type();

                    return $point;
                })
                ->keyBy('id');
        }

        $tariffs = collect();
        $tariffIds = $order->deliveries->pluck('tariff_id')->filter()->unique()->values()->all();
        if ($tariffIds) {
            $tariffs = $listsService->tariffs($listsService->newQuery()->setFilter('id', $tariffIds))->keyBy('id');
        }

        $shipments = collect();
        $merchantIds = collect();
        $cities = collect();
        $courierDelivery = null;
        $pickupDelivery = null;
        foreach ($order->deliveries as $delivery) {
            if ($tariffs->has($delivery->tariff_id)) {
                $delivery['tariff'] = $tariffs[$delivery->tariff_id];
            }

            if ($delivery->delivery_method == DeliveryMethod::METHOD_PICKUP) {
                if ($points->has($delivery->point_id)) {
                    $delivery['point'] = $points[$delivery->point_id];
                    $cities->push($points[$delivery->point_id]->getCityWithType());
                }

                if (is_null($courierDelivery)) {
                    $pickupDelivery = $delivery;
                }
            } else {
                $cities->push($delivery->getCity());
                $deliveryAddress = $delivery->delivery_address;
                $deliveryAddress['address_string'] = implode(', ', array_filter([
                    $deliveryAddress['post_index'] ?? '',
                    $deliveryAddress['region'] ?? '',
                    $deliveryAddress['city'] ?? '',
                    $deliveryAddress['street'] ?? '',
                    $deliveryAddress['house'] ?? '',
                    $deliveryAddress['block'] ?? '',
                    $deliveryAddress['flat'] ?? '',
                ]));
                $deliveryAddress['full_address_string'] = $delivery->getAddressString();
                $delivery->delivery_address = $deliveryAddress;

                if (is_null($courierDelivery)) {
                    $courierDelivery = $delivery;
                }
            }

            $delivery->status = $delivery->status();
            $delivery->status_at = $delivery->status_at ? date_time2str(new Carbon($delivery->status_at)) : '';
            $delivery->status_xml_id = $delivery->statusXmlId();
            $delivery->status_xml_id_at = $delivery->status_xml_id_at ? date_time2str(new Carbon($delivery->status_xml_id_at)) : '';
            $delivery->delivery_method = $delivery->deliveryMethod();
            $delivery->delivery_service = $delivery->deliveryService();
            $delivery->payment_status = $delivery->paymentStatus();
            $delivery->pdd_original = $delivery->pdd ? $delivery->pdd->format('Y-m-d') : '';
            $delivery->pdd = date2str($delivery->pdd);
            $delivery->delivery_at = date2str(new Carbon($delivery->delivery_at));
            $delivery['product_cost'] = $delivery->shipments->reduce(function (
                int $sum,
                ShipmentDto $shipment
            ) {
                return $sum + $shipment->cost;
            }, 0);

            foreach ($delivery->shipments as $shipment) {
                $merchantIds->push($shipment->merchant_id);
                $shipment->status = $shipment->status();
                $shipment->delivery_service_zero_mile = $shipment->deliveryServiceZeroMile();
                $shipment['delivery_service'] = $delivery->delivery_service;
                $shipment['store'] = $stores->has($shipment->store_id) ? $stores[$shipment->store_id] : null;
                $shipment['cargo'] = $shipment->cargo;
                $shipment->payment_status = $shipment->paymentStatus();
                $shipment['psd_original'] = $shipment->psd ? str_replace(' ', 'T', $shipment->psd->format('Y-m-d H:i')) : '';
                $shipment->psd = date_time2str($shipment->psd);
                $shipment['fsd_original'] = $shipment->fsd ? $shipment->fsd->format('Y-m-d') : '';
                $shipment->fsd = date2str($shipment->fsd);
                $shipment->payment_document_date = $shipment->payment_document_date ? $shipment->payment_document_date->format('Y-m-d') : '';
                $shipment['nonPackedBasketItems'] = $shipment->nonPackedBasketItems()->keyBy('id');
                $shipment['delivery_xml_id'] = !$shipment->is_canceled ? $delivery->xml_id : null;
                $shipment['delivery_status_xml_id'] = $delivery->status_xml_id;
                $shipment['delivery_status_xml_id_at'] = $delivery->status_xml_id_at;
                $shipment['delivery_pdd'] = $delivery->pdd;
                $shipment['product_qty'] = $shipment->basketItems->reduce(function (
                    int $sum,
                    BasketItemDto $item
                ) {
                    return $sum + $item->qty;
                }, 0);

                foreach ($shipment->packages as $package) {
                    $package['package'] = $packages->has($package->package_id) ? $packages[$package->package_id] : null;
                }
            }
            $shipments = $shipments->merge($delivery->shipments);
            $delivery['product_qty'] = $shipments->sum('product_qty');
        }
        $merchants = $this->getMerchants($merchantIds->unique()->all());

        foreach ($order->deliveries as $delivery) {
            $shipmentMerchantIds = $delivery->shipments->pluck('merchant_id')->unique();
            $delivery['merchants'] = $merchants->filter(function (MerchantDto $merchant) use ($shipmentMerchantIds) {
                return $shipmentMerchantIds->search($merchant->id) !== false;
            })->values();

            foreach ($delivery->shipments as $shipment) {
                $shipment['merchant'] = $merchants->has($shipment->merchant_id) ? $merchants[$shipment->merchant_id] : null;
            }
        }

        $order['merchants'] = $merchants->values();
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
        $order->status_at = date_time2str(new Carbon($order->status_at));
        if ($order->is_problem_at) {
            $order->is_problem_at = date_time2str(new Carbon($order->is_problem_at));
        }
        if ($order->is_canceled_at) {
            $order->is_canceled_at = date_time2str(new Carbon($order->is_canceled_at));
        }

        $order->delivery_type = $order->deliveryType();
        $order['delivery_services'] = $order->deliveries->map(function (DeliveryDto $delivery) {
            return $delivery->delivery_service;
        })->unique();
        $order['delivery_methods'] = $order->deliveries->map(function (DeliveryDto $delivery) {
            return $delivery->delivery_method;
        })->unique();

        $order->created_at = date_time2str(new Carbon($order->created_at));
        $order->updated_at = date_time2str(new Carbon($order->updated_at));

        $order['payment_method'] = $order->paymentMethod;
        $order['discount'] = $order->getDiscount();
        $order['delivery_discount'] = $order->getDeliveryDiscount();
        $order['product_cost'] = $order->cost - $order->delivery_cost;
        $order['product_price'] = $order->price - $order->delivery_price;
        $order['product_discount'] = $order['product_cost'] - $order['product_price'];
        $order['to_pay'] = $order->isPayed() ? 0 : $order->price;
        $order->payment_status = $order->paymentStatus();
        if ($order->payment_status_at) {
            $order->payment_status_at = date_time2str(new Carbon($order->payment_status_at));
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

        $order['canMarkAsPaid'] = in_array($order->paymentMethod->id, [PaymentMethod::CREDITPAID, PaymentMethod::BANK_TRANSFER_FOR_LEGAL])
            && $order->payment_status->id === PaymentStatus::WAITING
            && !$order->is_canceled
            && resolve(RequestInitiator::class)->hasRole([RoleDto::ROLE_FINANCIER, RoleDto::ROLE_ADMINISTRATOR]);

        $order['canMarkAsPaidForce'] = in_array($order->paymentMethod->id, [PaymentMethod::PREPAID, PaymentMethod::B2B_SBERBANK])
            && in_array($order->payment_status->id, [PaymentStatus::NOT_PAID, PaymentStatus::WAITING])
            && !$order->is_canceled
            && resolve(RequestInitiator::class)->hasRole(RoleDto::ROLE_ADMINISTRATOR);
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

            $order->basket->items = $order->basket->items->map(function (BasketItemDto $basketItemDto) use ($productsByOffers) {
                $product = $basketItemDto->product;
                $productPim = $productsByOffers->has($basketItemDto->offer_id) ?
                    $productsByOffers[$basketItemDto->offer_id]->product : [];

                foreach ($product as $key => $value) {
                    $productPim[$key] = $value;
                }
                $basketItemDto['product'] = $productPim;

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
     * @throws Exception
     */
    protected function getKpis(OrderDto $order): Collection
    {
        $kpis = collect([
            OrderStatus::CREATED => [
                'status' => OrderStatus::statusById(OrderStatus::CREATED),
                'status_at' => $order->created_at,
            ],
        ]);
        if ($order->history->isNotEmpty()) {
            /** @var HistoryDto $historyDto */
            foreach ($order->history as $historyDto) {
                $data = $historyDto->data;
                if (mb_strtolower($historyDto->entity_type) == OrderDto::entity() && isset($data['status'])) {
                    $kpis->put($data['status'], [
                        'status' => OrderStatus::statusById($data['status']),
                        'status_at' => date_time2str(new Carbon($data['status_at'])),
                    ]);
                }
            }
        }

        return $kpis->sortBy('status_at')->values();
    }
}
