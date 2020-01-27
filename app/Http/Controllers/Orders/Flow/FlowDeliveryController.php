<?php
namespace App\Http\Controllers\Orders\Flow;

use App\Http\Controllers\Controller;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Greensight\Logistics\Dto\Lists\DeliveryService as DeliveryServiceDto;
use Greensight\Oms\Dto\Delivery\DeliveryStatus;

use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Illuminate\Support\Collection;
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

        $this->title = 'Доставка '.$delivery->number;

        return $this->render('Orders/Flow/Delivery', [
            'iDelivery' => $delivery,
            'iShipments' => $shipments,
            'deliveryStatuses' => DeliveryStatus::allStatuses(),
            'deliveryServices' => DeliveryServiceDto::allServices(),
        ]);
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

        if(isset($delivery)) {
            return $delivery;
        }

        return null;
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

        if(isset($shipments)) {
            return $shipments;
        }

        return null;
    }
}
