<?php

namespace App\Http\Controllers\Order\Detail;


use App\Http\Controllers\Controller;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Dto\Lists\PointDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TabMainController
 * @package App\Http\Controllers\Order\Detail
 */
class TabMainController extends Controller
{
    /**
     * @param  int  $id
     * @param  OrderService  $orderService
     * @param  ListsService  $listsService
     * @return \Illuminate\Http\JsonResponse
     */
    public function load(
        int $id,
        OrderService $orderService,
        ListsService $listsService
    ) {
        $restQuery = $orderService
            ->newQuery()
            ->setFilter('id', $id)
            ->include('deliveries');
        $orders = $orderService->orders($restQuery);
        if (!$orders->count()) {
            throw new NotFoundHttpException();
        }

        /** @var OrderDto $order */
        $order = $orders->first();
        $pickupDelivery = null;
        foreach ($order->deliveries as $delivery) {
            if($delivery->delivery_method == DeliveryMethod::METHOD_PICKUP && is_null($pickupDelivery)) {
                $pickupDelivery = $delivery;
            }
        }

        $points = collect();
        if ($pickupDelivery) {
            $pointQuery = $listsService->newQuery()
                ->setFilter('id', $pickupDelivery->point_id)
                ->addFields(PointDto::entity(), 'id', 'city_guid');
            /** @var PointDto $point */
            $point = $listsService->points($pointQuery)->first();
            $pointsQuery = $listsService->newQuery()
                ->setFilter('city_guid', $point->city_guid)
                ->setFilter('delivery_service', $pickupDelivery->delivery_service)
                ->addFields(PointDto::entity(), 'id', 'type', 'name', 'has_payment_card', 'address', 'phone', 'timetable');
            $points = $listsService->points($pointsQuery)->keyBy('id')->map(function(PointDto $point) {
                $point->type = $point->type();

                return $point;
            });
        }

        return response()->json([
            'points' => $points,
        ]);
    }

    /**
     * @param $id
     * @param  OrderService  $orderService
     * @return Response
     */
    public function save($id, OrderService $orderService): Response
    {
        /*$data = $this->validate(request(), [
            'do_dangerous_products_delivery' => ['required', 'boolean'],
            'max_shipments_per_day' => ['nullable', 'integer', 'min:0'],
        ]);

        $deliveryService = new DeliveryService();
        $deliveryService->do_dangerous_products_delivery = $data['do_dangerous_products_delivery'];
        $deliveryService->max_shipments_per_day = $data['max_shipments_per_day'];*/

        return  response('', 204);
    }
}