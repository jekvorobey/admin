<?php

namespace App\Http\Controllers\Logistics\DeliveryService\Detail;

use App\Http\Controllers\Controller;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Services\ListsService\ListsService;
use Illuminate\Http\Response;

/**
 * Class TabSettingsController
 * @package App\Http\Controllers\Logistics\DeliveryService\Detail
 */
class TabSettingsController extends Controller
{
    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function save($id, ListsService $listsService): Response
    {
        $data = $this->validate(request(), [
            'do_consolidation' => ['required', 'boolean'],
            'do_deconsolidation' => ['required', 'boolean'],
            'do_zero_mile' => ['required', 'boolean'],
            'do_express_delivery' => ['required', 'boolean'],
            'do_return' => ['required', 'boolean'],
            'max_cargo_export_time' => ['nullable', 'date_format:H:i'],
            'add_partial_reject_service' => ['required', 'boolean'],
            'add_insurance_service' => ['required', 'boolean'],
            'add_fitting_service' => ['required', 'boolean'],
            'add_return_service' => ['required', 'boolean'],
            'add_open_service' => ['required', 'boolean'],
        ]);

        $deliveryService = new DeliveryService();
        $deliveryService->do_consolidation = $data['do_consolidation'];
        $deliveryService->do_deconsolidation = $data['do_deconsolidation'];
        $deliveryService->do_zero_mile = $data['do_zero_mile'];
        $deliveryService->do_express_delivery = $data['do_express_delivery'];
        $deliveryService->do_return = $data['do_return'];
        $deliveryService->max_cargo_export_time = $data['max_cargo_export_time'];
        $deliveryService->add_partial_reject_service = $data['add_partial_reject_service'];
        $deliveryService->add_insurance_service = $data['add_insurance_service'];
        $deliveryService->add_fitting_service = $data['add_fitting_service'];
        $deliveryService->add_return_service = $data['add_return_service'];
        $deliveryService->add_open_service = $data['add_open_service'];

        $listsService->updateDeliveryService($id, $deliveryService);

        return response('', 204);
    }
}
