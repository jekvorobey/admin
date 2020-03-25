<?php

namespace App\Http\Controllers\Logistics\DeliveryService;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\AbstractDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Dto\Lists\DeliveryServiceStatus;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DeliveryServiceDetailController
 * @package App\Http\Controllers\Logistics\DeliveryService
 */
class DeliveryServiceDetailController extends Controller
{
    /**
     * @param $id
     * @param  ListsService  $listsService
     * @param  ShipmentService  $shipmentService
     * @return mixed
     */
    public function index(
        $id,
        ListsService $listsService,
        ShipmentService $shipmentService
    ) {
        /** @var DeliveryService $deliveryService */
        $deliveryServiceQuery = $listsService->newQuery()
            ->addFields(
                DeliveryService::entity(),
                'id',
                'name',
                'registered_at',
                'status',
                'priority'
            );
        $deliveryService = $listsService->deliveryService($id, $deliveryServiceQuery);
        if (!$deliveryService) {
            throw new NotFoundHttpException();
        }

        $shipmentQuery = $shipmentService->newQuery()
            ->setFilter('delivery_service', $id)
            ->addFields(ShipmentDto::entity(), 'status', 'cost');
        $shipments = $shipmentService->shipments($shipmentQuery);


        return $this->render('Logistics/DeliveryService/Detail', [
            'iDeliveryService' => [
                'id' => $deliveryService->id,
                'name' => $deliveryService->name,
                'registered_at' => $deliveryService->registered_at->format(AbstractDto::DATE_FORMAT),
                'status' => $deliveryService->status,
                'priority' => $deliveryService->priority,
            ],
            'deliveryServiceStatuses' => DeliveryServiceStatus::allStatuses(),
            'shipmentsInfo' => [
                'allCount' => $shipments->count(),
                'allPrice' => number_format($shipments->sum('price'), 2, '.', ' '),
                //todo Добавить подсчет отправлений по кол-ву и деньгам в нужных статусах
            ],
        ]);
    }

    /**
     * @param $id
     * @param  ListsService  $listsService
     * @param  RequestInitiator  $requestInitiator
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function save($id, ListsService $listsService, RequestInitiator $requestInitiator)
    {
        $data =$this->validate(request(), [
            'name' => ['required', 'string'],
            'registered_at' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(DeliveryServiceStatus::allStatuses()))],
            'priority' => ['required', 'integer', 'min:1'],
        ]);
        $deliveryService = new DeliveryService();
        $deliveryService->name = $data['name'];
        $deliveryService->registered_at = $data['registered_at'];
        $deliveryService->status = $data['status'];
        $deliveryService->priority = $data['priority'];
        $listsService->updateDeliveryService($id, $deliveryService);

        return  response('', 204);
    }
}