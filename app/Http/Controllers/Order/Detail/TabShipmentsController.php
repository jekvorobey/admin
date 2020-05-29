<?php

namespace App\Http\Controllers\Order\Detail;


use App\Http\Controllers\Order\OrderDetailController;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Dto\Delivery\ShipmentStatus;
use Greensight\Oms\Dto\Document\DocumentDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class TabShipmentsController
 * @package App\Http\Controllers\Order\Detail
 */
class TabShipmentsController extends OrderDetailController
{
    /**
     * @return JsonResponse
     */
    public function load() {
        return response()->json([
            'shipmentStatuses' => ShipmentStatus::allStatuses(),
            'deliveryServices' => DeliveryService::allServices(),
        ]);
    }

    /**
     * Обновить отправление
     * @param int $orderId
     * @param int $shipmentId
     * @param  OrderService  $orderService
     * @param  ShipmentService  $shipmentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function save(int $orderId, int $shipmentId, OrderService $orderService, ShipmentService $shipmentService): JsonResponse
    {
        $data = $this->validate(request(), [
            'delivery_service_zero_mile' => Rule::in(array_keys(DeliveryService::allServices())),
            'psd' => ['required', 'date'],
            'fsd' => ['sometimes', 'nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(ShipmentStatus::allStatuses()))],
        ]);

        $shipmentDto = new ShipmentDto();
        $shipmentDto->delivery_service_zero_mile = isset($data['delivery_service_zero_mile']) ? $data['delivery_service_zero_mile'] : null;
        $shipmentDto->psd = $data['psd'];
        $shipmentDto->fsd = isset($data['fsd']) ? $data['fsd'] : null;
        $shipmentDto->status = $data['status'];
        $shipmentService->updateShipment($shipmentId, $shipmentDto);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }

    /**
     * Получить штрихкоды для отправления
     * @param int $orderId
     * @param int $shipmentId
     * @param  ShipmentService  $shipmentService
     * @return StreamedResponse
     */
    public function barcodes(int $orderId, int $shipmentId, ShipmentService $shipmentService): StreamedResponse
    {
        $barcodes = $shipmentService->shipmentBarcodes($shipmentId);

        return response()->streamDownload(function () use ($barcodes) {
            echo file_get_contents($barcodes->absolute_url);
        }, $barcodes->original_name);
    }

    /**
     * Получить квитанцию cdek для отправления
     * @param int $orderId
     * @param int $shipmentId
     * @param  ShipmentService  $shipmentService
     * @return StreamedResponse
     */
    public function cdekReceipt(int $orderId, int $shipmentId, ShipmentService $shipmentService): StreamedResponse
    {
        $cdekReceipt = $shipmentService->shipmentCdekReceipt($shipmentId);

        return response()->streamDownload(function () use ($cdekReceipt) {
            echo file_get_contents($cdekReceipt->absolute_url);
        }, $cdekReceipt->original_name);
    }

    /**
     * Получить документ "Акт приема-передачи по отправлению"
     * @param int $orderId
     * @param int $shipmentId
     * @param  ShipmentService  $shipmentService
     * @return StreamedResponse
     */
    public function acceptanceAct(int $orderId, int $shipmentId, ShipmentService $shipmentService): StreamedResponse
    {
        return $this->getDocumentResponse($shipmentService->shipmentAcceptanceAct($shipmentId));
    }

    /**
     * Получить документ "Карточка сборки отправления"
     * @param int $orderId
     * @param int $shipmentId
     * @param  ShipmentService  $shipmentService
     * @return StreamedResponse
     */
    public function assemblingCard(int $orderId, int $shipmentId, ShipmentService $shipmentService): StreamedResponse
    {
        return $this->getDocumentResponse($shipmentService->shipmentAssemblingCard($shipmentId));
    }

    /**
     * Получить документ "Опись отправления заказа"
     * @param int $orderId
     * @param int $shipmentId
     * @param  ShipmentService  $shipmentService
     * @return StreamedResponse
     */
    public function inventory(int $orderId, int $shipmentId, ShipmentService $shipmentService): StreamedResponse
    {
        return $this->getDocumentResponse($shipmentService->shipmentInventory($shipmentId));
    }

    /**
     * @param  DocumentDto  $documentDto
     * @return StreamedResponse
     */
    protected function getDocumentResponse(DocumentDto $documentDto): StreamedResponse
    {
        return response()->streamDownload(function () use ($documentDto) {
            echo file_get_contents($documentDto->absolute_url);
        }, $documentDto->original_name);
    }

    /**
     * Пометить отправление как непроблемное
     * @param  int  $orderId
     * @param  int  $shipmentId
     * @param  ShipmentService $shipmentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function markAsNonProblemShipment(int $orderId, int $shipmentId, ShipmentService $shipmentService): JsonResponse
    {
        $shipmentService->markAsNonProblemShipment($shipmentId);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }

    /**
     * Отменить отправление
     * @param  int  $orderId
     * @param  int  $shipmentId
     * @param  ShipmentService $shipmentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function cancelShipment(int $orderId, int $shipmentId, ShipmentService $shipmentService): JsonResponse
    {
        $shipmentService->cancelShipment($shipmentId);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }
}