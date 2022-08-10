<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Logistics\Dto\Lists\DeliveryService as DeliveryServiceDto;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\Delivery\DeliveryStatus;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Dto\Delivery\ShipmentStatus;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use MerchantManagement\Services\MerchantService\MerchantService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class FlowDeliveryController
 * @package App\Http\Controllers\Order
 */
class FlowDeliveryController extends Controller
{
    /**
     * @return mixed
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function detail(int $orderId, int $deliveryId)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $delivery = $this->loadDelivery($deliveryId);
        $shipments = $this->loadShipments($deliveryId);

        if (!$delivery) {
            throw new NotFoundHttpException();
        }

        if ($shipments) {
            $merchantsIds = $shipments->pluck('merchant_id')->unique()->toArray();
            $merchants = $this->loadMerchants($merchantsIds);
        }

        $this->title = 'Доставка ' . $delivery->number;

        return $this->render('Order/Flow/Delivery', [
            'iDelivery' => $delivery,
            'iShipments' => $shipments,
            'iMerchants' => $merchants ?? null,
            'deliveryStatuses' => DeliveryStatus::allStatuses(),
            'deliveryServices' => DeliveryServiceDto::allServices(),
            'shipmentStatuses' => ShipmentStatus::allStatuses(),
        ]);
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter */
    public function editDelivery(int $deliveryId, Request $request, DeliveryService $deliveryService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $validatedData = $request->validate([
            'id' => 'integer|required',
            'order_id' => 'integer|required',
            'status' => ['required', Rule::in(array_keys(DeliveryStatus::allStatuses()))],
            'delivery_method' => 'integer|nullable',
            'delivery_service' => 'integer|nullable',

            'xml_id' => 'string|nullable',
            'status_xml_id' => 'string|nullable',
            'tariff_id' => 'integer|nullable',
            'point_id' => 'integer|nullable',
            'number' => 'string|required',

            'cost' => 'numeric|nullable',
            'height' => 'numeric|nullable',
            'length' => 'numeric|nullable',
            'weight' => 'numeric|nullable',

            'receiver_name' => 'string|nullable',
            'receiver_phone' => 'string|nullable',
            'receiver_email' => 'string|nullable',
            'delivery_address' => 'string|nullable',

            'delivery_at' => 'string|nullable',
            'status_at' => 'string|nullable',
            'status_xml_id_at' => 'string|nullable',
        ]);

        $deliveryDto = new DeliveryDto($validatedData);

        $result = true;
        try {
            $deliveryService->updateDelivery($validatedData['id'], $deliveryDto);
        } catch (\Throwable) {
            $result = false;
        }

        return response()->json(['status' => $result ? 'ok' : 'fail']);
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter */
    public function editShipment(int $deliveryId, Request $request, ShipmentService $shipmentService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $validatedData = $request->validate([
            'id' => 'integer|required',
            'delivery_id' => 'integer|nullable',
            'merchant_id' => 'integer|required',
            'delivery_service_zero_mile' => 'integer|nullable',
            'store_id' => 'integer|nullable',
            'status' => ['required', Rule::in(array_keys(ShipmentStatus::allStatuses()))],
            'cargo_id' => 'integer|nullable',

            'number' => 'string|required',
            'cost' => 'numeric|nullable',
            'height' => 'numeric|nullable',
            'length' => 'numeric|nullable',
            'weight' => 'numeric|nullable',
            'required_shipping_at' => 'string|nullable',
            'assembly_problem_comment' => 'string|nullable',

            'package_qty' => 'integer|nullable',
        ]);

        $shipmentDto = new ShipmentDto($validatedData);

        $result = true;
        try {
            $shipmentService->updateShipment($validatedData['id'], $shipmentDto);
        } catch (\Throwable) {
            $result = false;
        }

        return response()->json(['status' => $result ? 'ok' : 'fail']);
    }

    /**
     * Получить доставку
     * @param int $deliveryId ID доставки
     */
    protected function loadDelivery(int $deliveryId): ?DeliveryDto
    {
        $deliveryService = resolve(DeliveryService::class);
        try {
            $delivery = $deliveryService->delivery($deliveryId);
        } catch (\Throwable) {
            //
        }

        return $delivery ?? null;
    }

    /**
     * Получить отправления доставки
     * @param int $deliveryId ID доставки
     */
    protected function loadShipments(int $deliveryId): ?Collection
    {
        $shipmentService = resolve(ShipmentService::class);
        $restQuery = $shipmentService
            ->newQuery()
            ->setFilter('delivery_id', $deliveryId)
            ->include('basketItems', 'items', 'packages.items', 'cargo');

        try {
            $shipments = $shipmentService->shipments($restQuery);
        } catch (\Throwable) {
            //
        }

        return $shipments ?? null;
    }

    /**
     * Получить мерчантов
     */
    protected function loadMerchants(array $merchantIds): ?Collection
    {
        $merchantService = resolve(MerchantService::class);
        $restQuery = $merchantService
            ->newQuery()
            ->setFilter('id', $merchantIds);

        try {
            $merchants = $merchantService->merchants($restQuery);
        } catch (\Throwable) {
            //
        }

        return isset($merchants) ? $merchants->keyBy('id') : null;
    }
}
