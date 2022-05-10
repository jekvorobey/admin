<?php

namespace App\Http\Controllers\Order\Detail;

use App\Http\Controllers\Order\OrderDetailController;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Dto\Lists\PointDto;
use Greensight\Logistics\Dto\Lists\TariffDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\Delivery\DeliveryStatus;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TabDeliveriesController
 * @package App\Http\Controllers\Order\Detail
 */
class TabDeliveriesController extends OrderDetailController
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function load(
        int $orderId,
        int $deliveryId,
        DeliveryService $deliveryService,
        ListsService $listsService
    ): JsonResponse {
        $delivery = $deliveryService->delivery($deliveryId);
        if (!$delivery) {
            throw new NotFoundHttpException();
        }

        $points = collect();
        if ($delivery->delivery_method == DeliveryMethod::METHOD_PICKUP) {
            $pointQuery = $listsService->newQuery()
                ->setFilter('id', $delivery->point_id)
                ->addFields(PointDto::entity(), 'id', 'type', 'name', 'has_payment_card', 'address', 'phone', 'timetable', 'city_guid', 'active');
            /** @var PointDto $point */
            $point = $listsService->points($pointQuery)->first();
            $pointsQuery = $listsService->newQuery()
                ->setFilter('active', true)
                ->setFilter('city_guid', $point->city_guid)
                ->setFilter('delivery_service', $delivery->delivery_service)
                ->addFields(PointDto::entity(), 'id', 'type', 'name', 'has_payment_card', 'address', 'phone', 'timetable', 'active');
            $points = $listsService->points($pointsQuery)->keyBy('id');
            if (!$points->has($point->id)) {
                $points->put($point->id, $point);
            }
            $points = $points->map(function (PointDto $point) {
                $point->type = $point->type();

                return $point;
            });
        }

        $tariffQuery = $listsService->newQuery()
            ->setFilter('delivery_service', $delivery->delivery_service)
            ->setFilter('delivery_method', $delivery->delivery_method)
            ->addFields(TariffDto::entity(), 'id', 'name', 'xml_id');
        $tariffs = $listsService->tariffs($tariffQuery);

        return response()->json([
            'points' => $points,
            'tariffs' => $tariffs,
        ]);
    }

    /**
     * Обновить доставку
     * @throws Exception
     */
    public function save(int $orderId, int $deliveryId, DeliveryService $deliveryService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $requiredIfDeliveryAddressExist = function () {
            return count(array_filter(request()->get('delivery_address'))) > 0;
        };
        $data = $this->validate(request(), [
            'status' => ['required', Rule::in(array_keys(DeliveryStatus::allStatuses()))],
            'tariff_id' => ['required', 'integer'],
            'pdd' => ['required', 'date'],
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
            'point_id' => [Rule::requiredIf(function () {
                return !count(array_filter(request()->get('delivery_address')));
            }), 'integer',
            ],
        ]);

        $deliveryDto = new DeliveryDto();
        $deliveryDto->status = $data['status'];
        $deliveryDto->tariff_id = $data['tariff_id'];
        $deliveryDto->pdd = $data['pdd'];
        $deliveryDto->receiver_name = $data['receiver_name'];
        $deliveryDto->receiver_phone = $data['receiver_phone'];
        $deliveryDto->receiver_email = $data['receiver_email'];
        if ($requiredIfDeliveryAddressExist()) {
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
            $deliveryDto->delivery_address = $deliveryAddress;
        } else {
            $deliveryDto->point_id = $data['point_id'];
        }
        $deliveryService->updateDelivery($deliveryId, $deliveryDto);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }

    /**
     * Создать/обновить заказ на доставку у службы доставки
     * @throws Exception
     */
    public function saveDeliveryOrder(int $orderId, int $deliveryId, DeliveryService $deliveryService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $deliveryService->saveDeliveryOrder($deliveryId);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }

    /**
     * Отменить заказ на доставку у службы доставки
     * @throws Exception
     */
    public function cancelDeliveryOrder(int $orderId, int $deliveryId, DeliveryService $deliveryService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $deliveryService->cancelDeliveryOrder($deliveryId);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }

    /**
     * Отменить доставку
     * @throws Exception
     */
    public function cancelDelivery(
        int $orderId,
        int $deliveryId,
        Request $request,
        DeliveryService $deliveryService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $data = $this->validate($request, [
            'orderReturnReason' => 'required|int',
        ]);
        $deliveryService->cancelDelivery($deliveryId, $data['orderReturnReason']);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }

    /**
     * Изменить статус доставки
     * @throws Exception
     */
    public function changeDeliveryStatus(int $orderId, int $deliveryId, DeliveryService $deliveryService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $data = $this->validate(request(), [
            'status' => ['required', Rule::in(array_keys(DeliveryStatus::allStatuses()))],
        ]);

        $deliveryDto = new DeliveryDto();
        $deliveryDto->status = $data['status'];
        $deliveryService->updateDelivery($deliveryId, $deliveryDto);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }
}
