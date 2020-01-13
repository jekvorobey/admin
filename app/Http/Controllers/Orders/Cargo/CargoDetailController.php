<?php

namespace App\Http\Controllers\Orders\Cargo;

use App\Http\Controllers\Controller;
use App\Models\CargoPage;
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
 * @package App\Http\Controllers\Orders\Cargo
 */
class CargoDetailController extends Controller
{
    /**
     * @param  int  $id
     * @return mixed
     * @throws \Exception
     */
    public function index(int $id)
    {
        $page = new CargoPage($id);
        
        return $this->render('Shipment/CargoDetail', [
            'iCargo' => $page->loadCargo()['cargo'],
        ]);
    }
    
    /**
     * Изменить статус груза
     * @param  int  $id
     * @param  Request  $request
     * @param  CargoService  $cargoService
     * @return JsonResponse
     */
    public function changeStatus(int $id, Request $request, CargoService $cargoService): JsonResponse
    {
        $result = 'ok';
        $cargo = [];
        $error = '';
        $systemError = '';
        try {
            $data = $this->validate($request, [
                'status' => Rule::in(array_keys(CargoStatus::allStatuses())),
            ]);
            $cargo = new CargoDto($data);
            $cargoService->updateCargo($id, $cargo);
    
            $page = new CargoPage($id);
            $cargo = $page->loadCargo()['cargo'];
        } catch (\Exception $e) {
            $result = 'fail';
            if ($request->get('status') == CargoStatus::STATUS_SHIPPED) {
                $error = 'Груз не содержит заказов';
            }
            $systemError = $e->getMessage();
        }
    
        return response()->json(['result' => $result, 'cargo' => $cargo, 'error' => $error,'systemErrors' => $systemError]);
    }
    
    /**
     * Сообщить о проблеме с отгрузкой груза
     * @param  int  $id
     * @param  Request  $request
     * @param  CargoService $cargoService
     * @return JsonResponse
     */
    public function reportProblem(
        int $id,
        Request $request,
        CargoService $cargoService
    ): JsonResponse
    {
        return $this->abstractAction($id, function () use ($id, $request, $cargoService) {
            $data = $this->validate($request, [
                'shipping_problem_comment' => ['required'],
            ]);
            $cargo = new CargoDto($data);
            $cargo->status = CargoStatus::STATUS_SHIPPING_PROBLEM;
            
            $cargoService->updateCargo($id, $cargo);
        });
    }
    
    /**
     * Добавить отправление в груз
     * @param  int  $id
     * @param  Request  $request
     * @param  ShipmentService  $shipmentService
     * @return JsonResponse
     */
    public function addShipment2Cargo(
        int $id,
        Request $request,
        ShipmentService $shipmentService
    ): JsonResponse
    {
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
     * @param  int  $id
     * @param  int  $shipmentId
     * @param  Request  $request
     * @param  ShipmentService  $shipmentService
     * @return JsonResponse
     */
    public function deleteShipmentFromCargo(
        int $id,
        int $shipmentId,
        Request $request,
        ShipmentService $shipmentService
    ): JsonResponse
    {
        return $this->abstractAction($id, function () use ($shipmentId, $request, $shipmentService) {
            $shipment = new ShipmentDto();
            $shipment->cargo_id = null;
            
            $shipmentService->updateShipment($shipmentId, $shipment);
        });
    }
    
    /**
     * @param  int  $id
     * @param  Request  $request
     * @param  Closure  $action
     * @return JsonResponse
     */
    protected function abstractAction(int $id, Closure $action): JsonResponse
    {
        $result = 'ok';
        $cargo = [];
        $systemError = '';
        try {
            $action();
        
            $page = new CargoPage($id);
            $cargo = $page->loadCargo()['cargo'];
        } catch (\Exception $e) {
            $result = 'fail';
            $systemError = $e->getMessage();
        }
    
        return response()->json(['result' => $result, 'cargo' => $cargo, 'systemErrors' => $systemError]);
    }
    
    /**
     * @param  int  $id
     * @param  ShipmentService $shipmentService
     * @return JsonResponse
     */
    protected function getUnshippedShipments(int $id, ShipmentService $shipmentService): JsonResponse
    {
        $shipments = $shipmentService->similarUnshippedShipments($id);
        
        return response()->json(['shipments' => $shipments]);
    }
}
