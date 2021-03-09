<?php

namespace App\Http\Controllers\Logistics\DeliveryService;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\AbstractDto;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Dto\Lists\DeliveryServiceStatus;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Illuminate\Http\Response;
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
        $deliveryServiceQuery = $listsService->newQuery();
        $deliveryService = $listsService->deliveryService($id, $deliveryServiceQuery);
        if (!$deliveryService) {
            throw new NotFoundHttpException();
        }

        $shipmentQuery = $shipmentService->newQuery()
            ->setFilter('delivery_service', $id)
            ->addFields(ShipmentDto::entity(), 'id', 'status', 'cost');
        $shipments = $shipmentService->shipments($shipmentQuery);

        return $this->render('Logistics/DeliveryService/Detail', [
            'iDeliveryService' => [
                //Инфопанель
                'id' => $deliveryService->id,
                'name' => $deliveryService->name,
                'registered_at' => $deliveryService->registered_at ? $deliveryService->registered_at->format(AbstractDto::DATE_FORMAT) : '',
                'status' => $deliveryService->status,
                'priority' => $deliveryService->priority,
                'pickup_priority' => $deliveryService->pickup_priority,
                //Вкладка "Настройки"
                'do_consolidation' => $deliveryService->do_consolidation,
                'do_deconsolidation' => $deliveryService->do_deconsolidation,
                'do_zero_mile' => $deliveryService->do_zero_mile,
                'do_express_delivery' => $deliveryService->do_express_delivery,
                'do_return' => $deliveryService->do_return,
                'max_cargo_export_time' => $deliveryService->max_cargo_export_time ?
                    $deliveryService->max_cargo_export_time->format('H:i') : '',
                'add_partial_reject_service' => $deliveryService->add_partial_reject_service,
                'add_insurance_service' => $deliveryService->add_insurance_service,
                'add_fitting_service' => $deliveryService->add_fitting_service,
                'add_return_service' => $deliveryService->add_return_service,
                'add_open_service' => $deliveryService->add_open_service,
                //Вкладка "Ограничения"
                'do_dangerous_products_delivery' => $deliveryService->do_dangerous_products_delivery,
                'max_shipments_per_day' => $deliveryService->max_shipments_per_day,
            ],
            'deliveryServiceStatuses' => DeliveryServiceStatus::allStatuses(),
            'shipmentsInfo' => [
                'allCount' => $shipments->count(),
                'allPrice' => number_format($shipments->sum('cost'), 2, '.', ' '),
                //todo Добавить подсчет отправлений по кол-ву и деньгам в нужных статусах
            ],
        ]);
    }

    /**
     * @param $id
     * @param  ListsService  $listsService
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function save($id, ListsService $listsService): Response
    {
        $data = $this->validate(request(), [
            'name' => ['required', 'string'],
            'registered_at' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(DeliveryServiceStatus::allStatuses()))],
            'priority' => ['required', 'integer', 'min:1'],
            'pickup_priority' => ['required', 'integer', 'min:1'],
        ]);

        $deliveryService = new DeliveryService();
        $deliveryService->name = $data['name'];
        $deliveryService->registered_at = $data['registered_at'];
        $deliveryService->status = $data['status'];
        $deliveryService->priority = $data['priority'];
        $deliveryService->pickup_priority = $data['pickup_priority'];

        $listsService->updateDeliveryService($id, $deliveryService);

        return  response('', 204);
    }
}