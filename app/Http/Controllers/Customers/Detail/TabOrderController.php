<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\Payment\PaymentDto;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\PaymentService\PaymentService;
use Illuminate\Support\Collection;

class TabOrderController extends Controller
{
    public function load(
        $id,
        OrderService $orderService,
        PaymentService $paymentService,
        DeliveryService $deliveryService
    ) {
        $orders = $orderService->orders((new RestQuery())->setFilter('customer_id', $id));
        if ($orders->count()) {
            $orderIds = $orders->pluck('id')->all();
            $payments = $paymentService->payments($orderIds)->groupBy('order_id');

            $deliveries = $deliveryService
                ->deliveries((new RestQuery())->setFilter('order_id', $orderIds))
                ->groupBy('order_id');

            $orders = $orders->map(function (OrderDto $order) use ($payments, $deliveries) {
                /** @var Collection|PaymentDto[] $ps */
                $ps = $payments->get($order->id, collect());
                /** @var Collection|DeliveryDto[] $ds */
                $ds = $deliveries->get($order->id, collect());
                $ar = $order->toArray();
                $ar['status'] = $order->status();
                $ar['isPayed'] = $order->isPayed();
                $ar['deliveryType'] = $order->deliveryType();
                $ar['paymentMethod'] = $ps->map(function (PaymentDto $payment) {
                    return $payment->paymentMethod()->name;
                })->unique()->join(', ');
                $ar['deliveryMethod'] = $ds->map(function (DeliveryDto $delivery) {
                    return DeliveryMethod::methodById($delivery->delivery_method)->name;
                })->unique()->join(', ');
                $ar['deliverySystems'] = $ds->map(function (DeliveryDto $delivery) {
                    return \Greensight\Logistics\Dto\Lists\DeliveryService::serviceById($delivery->delivery_service)->name;
                })->unique()->join(', ');
                $ar['deliveryCount'] = $ds->count();
                $ar['deliveryDate'] = $ds->map(function (DeliveryDto $delivery) {
                    return explode(' ', $delivery->delivery_at)[0];
                })->unique()->join(', ');
                return $ar;
            });
        }
        return response()->json([
            'orders' => $orders,
        ]);
    }
}
