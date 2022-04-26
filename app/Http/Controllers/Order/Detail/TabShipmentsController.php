<?php

namespace App\Http\Controllers\Order\Detail;

use App\Http\Controllers\Order\OrderDetailController;
use Closure;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Dto\Delivery\ShipmentPackageDto;
use Greensight\Oms\Dto\Delivery\ShipmentPackageItemDto;
use Greensight\Oms\Dto\Delivery\ShipmentCancelItemDto;
use Greensight\Oms\Dto\Delivery\ShipmentStatus;
use Greensight\Oms\Dto\Document\DocumentDto;
use Greensight\Oms\Services\ShipmentPackageService\ShipmentPackageService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Greensight\Store\Services\PackageService\PackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class TabShipmentsController
 * @package App\Http\Controllers\Order\Detail
 */
class TabShipmentsController extends OrderDetailController
{
    /**
     * Обновить отправление
     * @throws Exception
     */
    public function save(int $orderId, int $shipmentId, ShipmentService $shipmentService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $data = $this->validate(request(), [
            'delivery_service_zero_mile' => Rule::in(array_keys(DeliveryService::allServices())),
            'psd' => ['required', 'date'],
            'fsd' => ['sometimes', 'nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(ShipmentStatus::allStatuses()))],
        ]);

        $shipmentDto = new ShipmentDto();
        $shipmentDto->delivery_service_zero_mile = $data['delivery_service_zero_mile'] ?? null;
        $shipmentDto->psd = $data['psd'];
        $shipmentDto->fsd = $data['fsd'] ?? null;
        $shipmentDto->status = $data['status'];
        $shipmentService->updateShipment($shipmentId, $shipmentDto);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }

    /**
     * Изменить статус отправления
     * @throws Exception
     */
    public function changeShipmentStatus(int $orderId, int $shipmentId, ShipmentService $shipmentService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $data = $this->validate(request(), [
            'status' => ['required', Rule::in(array_keys(ShipmentStatus::allStatuses()))],
        ]);

        $shipmentDto = new ShipmentDto();
        $shipmentDto->status = $data['status'];
        $shipmentService->updateShipment($shipmentId, $shipmentDto);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }

    /**
     * Получить штрихкоды для отправления
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function barcodes(int $orderId, int $shipmentId, ShipmentService $shipmentService): StreamedResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $barcodes = $shipmentService->shipmentBarcodes($shipmentId);

        return response()->streamDownload(function () use ($barcodes) {
            echo file_get_contents($barcodes->absolute_url);
        }, $barcodes->original_name);
    }

    /**
     * Получить квитанцию cdek для отправления
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function cdekReceipt(int $orderId, int $shipmentId, ShipmentService $shipmentService): StreamedResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        $cdekReceipt = $shipmentService->shipmentCdekReceipt($shipmentId);

        return response()->streamDownload(function () use ($cdekReceipt) {
            echo file_get_contents($cdekReceipt->absolute_url);
        }, $cdekReceipt->original_name);
    }

    /**
     * Получить документ "Акт приема-передачи по отправлению"
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function acceptanceAct(int $orderId, int $shipmentId, ShipmentService $shipmentService): StreamedResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        return $this->getDocumentResponse($shipmentService->shipmentAcceptanceAct($shipmentId));
    }

    /**
     * Получить документ "Карточка сборки отправления"
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function assemblingCard(int $orderId, int $shipmentId, ShipmentService $shipmentService): StreamedResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        return $this->getDocumentResponse($shipmentService->shipmentAssemblingCard($shipmentId));
    }

    /**
     * Получить документ "Опись отправления заказа"
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function inventory(int $orderId, int $shipmentId, ShipmentService $shipmentService): StreamedResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_ORDERS);

        return $this->getDocumentResponse($shipmentService->shipmentInventory($shipmentId));
    }

    protected function getDocumentResponse(DocumentDto $documentDto): StreamedResponse
    {
        return response()->streamDownload(function () use ($documentDto) {
            echo file_get_contents($documentDto->absolute_url);
        }, $documentDto->original_name);
    }

    /**
     * Пометить отправление как непроблемное
     * @throws Exception
     */
    public function markAsNonProblemShipment(
        int $orderId,
        int $shipmentId,
        ShipmentService $shipmentService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $shipmentService->markAsNonProblemShipment($shipmentId);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }

    /**
     * Отменить отправление
     * @throws Exception
     */
    public function cancelShipment(
        int $orderId,
        int $shipmentId,
        Request $request,
        ShipmentService $shipmentService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        $data = $this->validate($request, [
            'orderReturnReason' => 'required|int',
        ]);

        $shipmentService->cancelShipment($shipmentId, $data['orderReturnReason']);

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }

    /**
     * Добавить коробку для отправления
     * @throws Exception
     */
    public function addShipmentPackage(
        int $orderId,
        int $shipmentId,
        Request $request,
        ShipmentPackageService $shipmentPackageService,
        PackageService $packageService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        return $this->abstractAction($orderId, function () use ($shipmentId, $request, $shipmentPackageService, $packageService) {
            $data = $this->validate($request, [
                'package_id' => ['required', 'integer'],
            ]);
            /**
             * todo Использовать \App\Services\DeliveryService::createShipmentPackage из oms-ms,
             * для использования которого добавить новый endpoint в API
             */
            $shipmentPackage = new ShipmentPackageDto();
            $shipmentPackage->package_id = $data['package_id'];
            $package = $packageService->package($shipmentPackage->package_id);
            $shipmentPackage->wrapper_weight = $package->weight;
            $shipmentPackage->width = $package->width;
            $shipmentPackage->height = $package->height;
            $shipmentPackage->length = $package->length;

            $shipmentPackageService->createShipmentPackage($shipmentId, $shipmentPackage);
        });
    }

    /**
     * Удалить коробку для отправления
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function deleteShipmentPackage(
        int $orderId,
        int $shipmentId,
        int $shipmentPackageId,
        ShipmentPackageService $shipmentPackageService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        return $this->abstractAction($orderId, function () use ($shipmentPackageId, $shipmentPackageService) {
            $shipmentPackageService->deleteShipmentPackage($shipmentPackageId);
        });
    }

    /**
     * Добавить товары в коробку отправления
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function addShipmentPackageItems(
        int $orderId,
        int $shipmentId,
        int $shipmentPackageId,
        Request $request
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        return $this->abstractAction($orderId, function () use ($shipmentPackageId, $request) {
            $shipmentPackageService = resolve(ShipmentPackageService::class);
            $requestInitiator = resolve(RequestInitiator::class);

            $data = $this->validate($request, [
                'basketItems' => 'required|array',
                'basketItems.*' => 'integer',
            ]);
            foreach ($data['basketItems'] as $basketItemId => $qty) {
                /**
                 * todo Использовать \App\Services\DeliveryService::setShipmentPackageItem из oms-ms,
                 * для использования которого добавить новый endpoint в API
                 */
                try {
                    $shipmentPackageItem = $shipmentPackageService->shipmentPackageItem(
                        $shipmentPackageId,
                        $basketItemId
                    );
                } catch (\Throwable $e) {
                    $shipmentPackageItem = new ShipmentPackageItemDto();
                }
                $shipmentPackageItem->basket_item_id = $basketItemId;
                $shipmentPackageItem->shipment_package_id = $shipmentPackageId;
                $shipmentPackageItem->qty += $qty;
                $shipmentPackageItem->set_by = $requestInitiator->userId();

                $shipmentPackageService->setShipmentPackageItem($shipmentPackageItem);
            }
        });
    }

    /**
     * Отредактировать кол-ва товара в коробке отправления
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function editShipmentPackageItem(
        int $orderId,
        int $shipmentId,
        int $shipmentPackageId,
        int $basketItemId,
        Request $request
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        return $this->abstractAction($orderId, function () use ($shipmentPackageId, $basketItemId, $request) {
            $shipmentPackageService = resolve(ShipmentPackageService::class);
            $requestInitiator = resolve(RequestInitiator::class);

            $data = $this->validate($request, [
                'qty' => ['required', 'integer'],
            ]);
            /**
             * todo Использовать \App\Services\DeliveryService::setShipmentPackageItem из oms-ms,
             * для использования которого добавить новый endpoint в API
             */
            $shipmentPackageItem = new ShipmentPackageItemDto($data);
            $shipmentPackageItem->shipment_package_id = $shipmentPackageId;
            $shipmentPackageItem->basket_item_id = $basketItemId;
            $shipmentPackageItem->set_by = $requestInitiator->userId();

            $shipmentPackageService->setShipmentPackageItem($shipmentPackageItem);
        });
    }

    /**
     * Отменить поштучно товар из отправления
     * @throws Exception
     */
    public function cancelShipmentItem(int $orderId, int $shipmentId, int $basketItemId, Request $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_ORDERS);

        return $this->abstractAction($orderId, function () use ($shipmentId, $basketItemId, $request) {
            $shipmentService = resolve(ShipmentService::class);
            $requestInitiator = resolve(RequestInitiator::class);
            $data = $this->validate($request, [
                'qty' => ['required', 'integer'],
                'return_reason_id' => ['required', 'integer'],
            ]);
            $shipmentCancelItem = new ShipmentCancelItemDto($data);
            $shipmentCancelItem->shipment_id = $shipmentId;
            $shipmentCancelItem->basket_item_id = $basketItemId;
            $shipmentCancelItem->canceled_by = $requestInitiator->userId();

            $shipmentService->shipmentCancelItem($shipmentCancelItem);
        });
    }

    /**
     * Удалить товар в полном кол-ве из коробки отправления
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function deleteShipmentPackageItem(
        int $orderId,
        int $shipmentId,
        int $shipmentPackageId,
        int $basketItemId
    ): JsonResponse {
        return $this->abstractAction($orderId, function () use ($shipmentPackageId, $basketItemId) {
            $shipmentPackageService = resolve(ShipmentPackageService::class);
            $requestInitiator = resolve(RequestInitiator::class);

            /**
             * todo Использовать \App\Services\DeliveryService::setShipmentPackageItem из oms-ms,
             * для использования которого добавить новый endpoint в API
             */
            $shipmentPackageItem = new ShipmentPackageItemDto();
            $shipmentPackageItem->shipment_package_id = $shipmentPackageId;
            $shipmentPackageItem->basket_item_id = $basketItemId;
            $shipmentPackageItem->qty = 0;
            $shipmentPackageItem->set_by = $requestInitiator->userId();

            $shipmentPackageService->setShipmentPackageItem($shipmentPackageItem);
        });
    }

    /**
     * @throws Exception
     */
    protected function abstractAction(int $orderId, Closure $action): JsonResponse
    {
        $action();

        return response()->json([
            'order' => $this->getOrder($orderId),
        ]);
    }
}
