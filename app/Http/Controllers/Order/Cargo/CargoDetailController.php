<?php

namespace App\Http\Controllers\Order\Cargo;

use App\Http\Controllers\Controller;
use App\Pages\CargoPage;
use Closure;
use Greensight\Oms\Dto\Delivery\CargoDto;
use Greensight\Oms\Dto\Delivery\CargoStatus;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Services\CargoService\CargoService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class CargoDetailController
 * @package App\Http\Controllers\Order\Cargo
 */
class CargoDetailController extends Controller
{
    /**
     * @return mixed
     * @throws \Exception
     */
    public function index(int $id)
    {
        $this->loadCargoStatuses = true;
        $page = new CargoPage($id);

        return $this->render('Order/Cargo/Detail', [
            'iCargo' => $page->loadCargo()['cargo'],
        ]);
    }

    /**
     * Изменить статус груза
     */
    public function changeStatus(int $id, Request $request, CargoService $cargoService): JsonResponse
    {
        $result = 'ok';
        $error = '';
        $systemError = '';
        $data = $this->validate($request, [
            'status' => ['required', Rule::in(array_keys(CargoStatus::allStatuses()))],
        ]);

        try {
            $cargo = new CargoDto();
            $cargo->status = $data['status'];
            $cargoService->updateCargo($id, $cargo);
        } catch (\Throwable $e) {
            $result = 'fail';
            if ($data['status'] == CargoStatus::SHIPPED) {
                $error = 'Груз не содержит заказов';
            }
            $systemError = $e->getMessage();
        }

        $page = new CargoPage($id);
        $cargo = $page->loadCargo()['cargo'];

        return response()->json(['result' => $result, 'cargo' => $cargo, 'error' => $error, 'systemErrors' => $systemError]);
    }

    /**
     * Создать задание на забор груза (заявку на вызов курьера)
     */
    public function createCourierCall(int $id, CargoService $cargoService): JsonResponse
    {
        return $this->abstractAction($id, function () use ($id, $cargoService) {
            $cargoService->createCourierCall($id);
        });
    }

    /**
     * Отменить задание на забор груза (заявку на вызов курьера)
     */
    public function cancelCourierCall(int $id, CargoService $cargoService): JsonResponse
    {
        return $this->abstractAction($id, function () use ($id, $cargoService) {
            $cargoService->cancelCourierCall($id);
        });
    }

    /**
     * Проверить заявку на вызов курьера во внешнем сервисе на наличие ошибок
     */
    public function checkCourierCallStatus(int $id, CargoService $cargoService): JsonResponse
    {
        return $this->abstractAction($id, function () use ($id, $cargoService) {
            $cargoService->checkCourierCallStatus($id);
        });
    }

    /**
     * Отменить груз
     */
    public function cancel(int $id, CargoService $cargoService): JsonResponse
    {
        return $this->abstractAction($id, function () use ($id, $cargoService) {
            $cargoService->cancelCargo($id);
        });
    }

    /**
     * Добавить отправление в груз
     */
    public function addShipment2Cargo(
        int $id,
        Request $request,
        ShipmentService $shipmentService
    ): JsonResponse {
        return $this->abstractAction($id, function () use ($id, $request, $shipmentService) {
            $data = $this->validate($request, [
                'shipment_id' => ['required', 'array'],
                'shipment_id.*' => ['required', 'integer'],
            ]);

            foreach ($data['shipment_id'] as $shipmentId) {
                $shipment = new ShipmentDto();
                $shipment->cargo_id = $id;

                $shipmentService->updateShipment($shipmentId, $shipment);
            }
        });
    }

    /**
     * Удалить отправление из груза
     */
    public function deleteShipmentFromCargo(
        int $id,
        int $shipmentId,
        ShipmentService $shipmentService
    ): JsonResponse {
        return $this->abstractAction($id, function () use ($shipmentId, $shipmentService) {
            $shipment = new ShipmentDto();
            $shipment->cargo_id = null;

            $shipmentService->updateShipment($shipmentId, $shipment);
        });
    }

    /**
     * @param Request $request
     */
    protected function abstractAction(int $id, Closure $action): JsonResponse
    {
        $result = 'ok';
        $systemError = '';
        try {
            $action();
        } catch (\Throwable $e) {
            $result = 'fail';
            $systemError = $e->getMessage();
        }

        $page = new CargoPage($id);
        $cargo = $page->loadCargo()['cargo'];

        return response()->json(['result' => $result, 'cargo' => $cargo, 'systemErrors' => $systemError]);
    }

    protected function getUnshippedShipments(int $id, ShipmentService $shipmentService): JsonResponse
    {
        $shipments = $shipmentService->similarUnshippedShipments($id);

        return response()->json(['shipments' => $shipments]);
    }
}
