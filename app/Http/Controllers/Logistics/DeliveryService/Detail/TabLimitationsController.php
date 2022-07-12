<?php

namespace App\Http\Controllers\Logistics\DeliveryService\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Services\ListsService\ListsService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class TabLimitationsController
 * @package App\Http\Controllers\Logistics\DeliveryService\Detail
 */
class TabLimitationsController extends Controller
{
    public function save($id, ListsService $listsService): Response|Application|ResponseFactory
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_LOGISTICS);

        $data = $this->validate(request(), [
            'do_dangerous_products_delivery' => ['required', 'boolean'],
            'max_shipments_per_day' => ['nullable', 'integer', 'min:0'],
        ]);

        $deliveryService = new DeliveryService();
        $deliveryService->do_dangerous_products_delivery = $data['do_dangerous_products_delivery'];
        $deliveryService->max_shipments_per_day = $data['max_shipments_per_day'];

        $listsService->updateDeliveryService($id, $deliveryService);

        return response('', 204);
    }
}
