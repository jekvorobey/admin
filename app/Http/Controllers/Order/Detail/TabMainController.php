<?php

namespace App\Http\Controllers\Order\Detail;

use App\Http\Controllers\Order\OrderDetailController;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Dto\Lists\PointDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\Delivery\DeliveryStatus;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TabMainController
 * @package App\Http\Controllers\Order\Detail
 */
class TabMainController extends OrderDetailController
{
    /**
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
            if ($delivery->delivery_method == DeliveryMethod::METHOD_PICKUP && is_null($pickupDelivery)) {
                $pickupDelivery = $delivery;
            }
        }

        $points = collect();
        if ($pickupDelivery) {
            $pointQuery = $listsService->newQuery()
                ->setFilter('id', $pickupDelivery->point_id)
                ->addFields(PointDto::entity(), 'id', 'type', 'name', 'has_payment_card', 'address', 'phone', 'timetable', 'city_guid');
            /** @var PointDto $point */
            $point = $listsService->points($pointQuery)->first();
            $pointsQuery = $listsService->newQuery()
                ->setFilter('city_guid', $point->city_guid)
                ->setFilter('delivery_service', $pickupDelivery->delivery_service)
                ->addFields(PointDto::entity(), 'id', 'type', 'name', 'has_payment_card', 'address', 'phone', 'timetable');
            $points = $listsService->points($pointsQuery)->keyBy('id');
            if (!$points->has($point->id)) {
                $points->put($point->id, $point);
            }
            $points = $points->map(function (PointDto $point) {
                $point->type = $point->type();

                return $point;
            });
        }

        return response()->json([
            'points' => $points,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function save(int $id, OrderService $orderService, DeliveryService $deliveryService): JsonResponse
    {
        $requiredIfDeliveryAddressExist = function () {
            return count(array_filter(request()->get('delivery_address'))) > 0;
        };
        $data = $this->validate(request(), [
            'receiver_name' => ['required', 'string'],
            'receiver_phone' => ['required', 'regex:/\+\d\(\d\d\d\)\s\d\d\d-\d\d-\d\d/'],
            'receiver_email' => ['nullable', 'email'],
            'delivery_address' => ['nullable', 'array'],
            'delivery_address.country_code' => [Rule::requiredIf($requiredIfDeliveryAddressExist), 'string', 'nullable'],
            'delivery_address.post_index' => [Rule::requiredIf($requiredIfDeliveryAddressExist), 'string', 'nullable'],
            'delivery_address.region' => [Rule::requiredIf($requiredIfDeliveryAddressExist), 'string', 'nullable'],
            'delivery_address.region_guid' => [Rule::requiredIf($requiredIfDeliveryAddressExist), 'string', 'nullable'],
            'delivery_address.city' => [Rule::requiredIf($requiredIfDeliveryAddressExist), 'string', 'nullable'],
            'delivery_address.city_guid' => [Rule::requiredIf($requiredIfDeliveryAddressExist), 'string', 'nullable'],
            'delivery_address.street' => ['sometimes', 'string', 'nullable'],
            'delivery_address.house' => ['sometimes', 'string', 'nullable'],
            'delivery_address.block' => ['sometimes', 'string', 'nullable'],
            'delivery_address.flat' => ['sometimes', 'string', 'nullable'],
            'delivery_address.porch' => ['sometimes', 'string', 'nullable'],
            'delivery_address.floor' => ['sometimes', 'string', 'nullable'],
            'delivery_address.intercom' => ['sometimes', 'string', 'nullable'],
            'delivery_address.comment' => ['sometimes', 'string', 'nullable'],
            'point_id' => [
                Rule::requiredIf(function () {
                    return !count(array_filter(request()->get('delivery_address')));
                }),
                'integer',
            ],
            'manager_comment' => ['sometimes', 'string', 'nullable'],
        ]);

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
        $newOrderDto = new OrderDto();
        $newOrderDto->manager_comment = $data['manager_comment'];
        $orderService->updateOrder($id, $newOrderDto);

        foreach ($order->deliveries as $delivery) {
            if ($delivery->status > DeliveryStatus::ASSEMBLED) {
                continue;
            }

            $newDeliveryDto = new DeliveryDto();
            $newDeliveryDto->receiver_name = $data['receiver_name'];
            $newDeliveryDto->receiver_phone = $data['receiver_phone'];
            $newDeliveryDto->receiver_email = $data['receiver_email'];
            if ($delivery->delivery_method == DeliveryMethod::METHOD_DELIVERY) {
                $dataDeliveryAddress = $data['delivery_address'];
                $deliveryAddress['country_code'] = $dataDeliveryAddress['country_code'];
                $deliveryAddress['post_index'] = $dataDeliveryAddress['post_index'];
                $deliveryAddress['region'] = $dataDeliveryAddress['region'];
                $deliveryAddress['region_guid'] = $dataDeliveryAddress['region_guid'];
                $deliveryAddress['city'] = $dataDeliveryAddress['city'];
                $deliveryAddress['city_guid'] = $dataDeliveryAddress['city_guid'];
                $deliveryAddress['street'] = $dataDeliveryAddress['street'] ?? '';
                $deliveryAddress['house'] = $dataDeliveryAddress['house'] ?? '';
                $deliveryAddress['block'] = $dataDeliveryAddress['block'] ?? '';
                $deliveryAddress['flat'] = $dataDeliveryAddress['flat'] ?? '';
                $deliveryAddress['porch'] = $dataDeliveryAddress['porch'] ?? '';
                $deliveryAddress['floor'] = $dataDeliveryAddress['floor'] ?? '';
                $deliveryAddress['intercom'] = $dataDeliveryAddress['intercom'] ?? '';
                $deliveryAddress['comment'] = $dataDeliveryAddress['comment'] ?? '';
                $newDeliveryDto->delivery_address = $deliveryAddress;
            } elseif ($delivery->delivery_method == DeliveryMethod::METHOD_PICKUP) {
                $newDeliveryDto->point_id = $data['point_id'];
            }

            $deliveryService->updateDelivery($delivery->id, $newDeliveryDto);
        }

        return response()->json([
            'order' => $this->getOrder($id),
        ]);
    }
}
