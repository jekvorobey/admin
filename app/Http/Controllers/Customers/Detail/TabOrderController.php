<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Logistics\Dto\Lists\DeliveryService as DeliveryServiceDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;

class TabOrderController extends Controller
{
    public function load($id, OrderService $orderService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $ordersQuery = $orderService->newQuery()
            ->include('payments', 'paymentMethod', 'deliveries')
            ->setFilter('customer_id', $id)
            ->addSort('created_at', 'desc');
        $orders = $orderService->orders($ordersQuery);

        if ($orders->isEmpty()) {
            return response()->json([
                'orders' => [],
            ]);
        }

        $result = $orders->map(function (OrderDto $order) {
            $data = $order->toArray();
            $data['status'] = $order->status();
            $data['isPayed'] = $order->isPayed();
            $data['deliveryType'] = $order->deliveryType();
            $data['paymentMethod'] = $order->paymentMethod->name;
            $data['deliveryMethod'] = $order->deliveries->map(function (DeliveryDto $delivery) {
                return DeliveryMethod::methodById($delivery->delivery_method)->name;
            })->unique()->join(', ');
            $data['deliverySystems'] = $order->deliveries->map(function (DeliveryDto $delivery) {
                return DeliveryServiceDto::serviceById($delivery->delivery_service)->name;
            })->unique()->join(', ');
            $data['deliveryCount'] = $order->deliveries->count();
            $data['deliveryDate'] = $order->deliveries->map(function (DeliveryDto $delivery) {
                return explode(' ', $delivery->delivery_at)[0];
            })->unique()->join(', ');

            return $data;
        });

        return response()->json([
            'orders' => $result,
        ]);
    }
}
