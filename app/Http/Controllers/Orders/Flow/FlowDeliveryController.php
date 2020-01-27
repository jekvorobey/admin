<?php
namespace App\Http\Controllers\Orders\Flow;

use App\Http\Controllers\Controller;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\Delivery\ShipmentStatus;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Greensight\Logistics\Dto\Lists\DeliveryService as DeliveryServiceDto;
use Greensight\Oms\Dto\Delivery\DeliveryStatus;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MerchantManagement\Services\MerchantService\MerchantService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;

/**
 * Class FlowDeliveryController
 * @package App\Http\Controllers\Orders\Flow
 */
class FlowDeliveryController extends Controller
{
    /**
     * @param int $orderId
     * @param int $deliveryId
     * @return mixed
     */
    public function detail(int $orderId, int $deliveryId)
    {
        $delivery = $this->loadDelivery($deliveryId);
        $shipments = $this->loadShipments($deliveryId);

        if (!$delivery) {
            throw new NotFoundHttpException();
        }

        if ($shipments) {
            $merchantsIds = $shipments->pluck('merchant_id')->unique()->toArray();
            $merchants = $this->loadMerchants($merchantsIds);
        }

        $this->title = 'Доставка '.$delivery->number;

        // Статусы отправления, когда уже нельзя изменять доставку для нулевой мили
        $shipmentNotEditableStatuses = [
            ShipmentStatus::STATUS_ASSEMBLED,
            ShipmentStatus::STATUS_CANCEL,
        ];

        return $this->render('Orders/Flow/Delivery', [
            'iDelivery' => $delivery,
            'iShipments' => $shipments,
            'iMerchants' => $merchants ?? null,
            'deliveryStatuses' => DeliveryStatus::allStatuses(),
            'deliveryServices' => DeliveryServiceDto::allServices(),
            'shipmentStatuses' => ShipmentStatus::allStatuses(),
            'shipmentNotEditableStatuses' => $shipmentNotEditableStatuses,

        ]);
    }

    /**
     * @param int $orderId
     * @param int $deliveryId
     * @param Request $request
     * @param DeliveryService $deliveryService
     * @return JsonResponse
     */
    public function editDelivery(int $orderId, int $deliveryId, Request $request, DeliveryService $deliveryService): JsonResponse
    {
        $result = 'ok';

        return response()->json(['result' => $result]);
    }

    /**
     * @param int $orderId
     * @param int $deliveryId
     * @param Request $request
     * @param ShipmentService $shipmentService
     * @return JsonResponse
     */
    public function editShipment(int $orderId, int $deliveryId, Request $request, ShipmentService $shipmentService): JsonResponse
    {
        $result = 'ok';

        return response()->json(['result' => $result]);
    }


    /**
     * Получить доставку
     * @param int $deliveryId ID доставки
     * @return DeliveryDto|null
     */
    protected function loadDelivery(int $deliveryId):? DeliveryDto
    {
        $deliveryService = resolve(DeliveryService::class);
        try {
            $delivery = $deliveryService->delivery($deliveryId);
        } catch (Exception $e) {}

        return $delivery ?? null;
    }

    /**
     * Получить отправления доставки
     * @param int $deliveryId ID доставки
     * @return Collection|null
     */
    protected function loadShipments(int $deliveryId):? Collection
    {
        $shipmentService = resolve(ShipmentService::class);
        $restQuery = $shipmentService
            ->newQuery()
            ->setFilter('delivery_id', $deliveryId)
            ->include('basketItems', 'items', 'packages.items', 'cargo');

        try {
            $shipments = $shipmentService->shipments($restQuery);
        } catch (Exception $e) {}

        return $shipments ?? null;
    }

    /**
     * Получить мерчантов
     * @param array $merchantIds
     * @return Collection|null
     */
    protected function loadMerchants(array $merchantIds):? Collection
    {
        $merchantService = resolve(MerchantService::class);
        $restQuery = $merchantService
            ->newQuery()
            ->setFilter('id', $merchantIds);

        try {
            $merchants = $merchantService->merchants($restQuery);
        } catch (Exception $e) {}

        return isset($merchants) ? $merchants->keyBy('id') : null;
    }
}
