<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Dto\Lists\RegionDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\Payment\PaymentMethod;
use Greensight\Oms\Services\PaymentService\PaymentService;
use Illuminate\Http\JsonResponse;
use Pim\Dto\Offer\OfferSaleStatus;

class PaymentMethodsController extends Controller
{
    /**
     * Информация о доступных способах оплаты и их настройки
     * @return mixed
     */
    public function list(PaymentService $paymentService, ListsService $listsService)
    {
        // Способы оплаты //
        $paymentMethods = $paymentService->getPaymentMethods()->keyBy('id');

        // Регионы //
        $restQuery = $listsService->newQuery();
        $restQuery->addFields(RegionDto::entity(), 'id', 'name');
        $regions = $listsService->regions($restQuery)->keyBy('id');

        // Логистические операторы //
        $restQuery = $listsService->newQuery();
        $restQuery->addFields(DeliveryService::entity(), 'id', 'name');
        $deliveryServices = $listsService->deliveryServices($restQuery)->keyBy('id');

        // Статусы офферов //
        $offerStatuses = OfferSaleStatus::allStatuses();

        $this->title = 'Управление способами оплаты';
        return $this->render('Settings/PaymentMethods', [
            'iMethods' => $paymentMethods,
            'regions' => $regions,
            'delivery_services' => $deliveryServices,
            'offer_statuses' => $offerStatuses,
        ]);
    }

    /**
     * Изменить параметры способа оплаты
     * @return JsonResponse
     */
    public function edit(int $id, PaymentService $paymentService)
    {
        $data = $this->validate(request(), [
            'name' => 'required|string',
            'accept_prepaid' => 'required|boolean',
            'accept_virtual' => 'required|boolean',
            'accept_real' => 'required|boolean',
            'accept_postpaid' => 'required|boolean',
            'covers' => 'required|numeric',
            'max_limit' => 'required|numeric',
            'excluded_payment_methods' => 'nullable|json',
            'excluded_regions' => 'nullable|json',
            'excluded_delivery_services' => 'nullable|json',
            'excluded_offer_statuses' => 'nullable|json',
            'excluded_customers' => 'nullable|json',
            'active' => 'required|boolean',
        ]);

        $paymentService->updatePaymentMethod($id, $this->fulfillDto($data));

        return response()->json([
            'payment_method' => $paymentService->getPaymentMethods($id)->first(),
        ]);
    }

    /**
     * Вспомогательный метод, заполняющий Dto полями из Request
     * @param array $data
     */
    private function fulfillDto(array $data): PaymentMethod
    {
        $Dto = new PaymentMethod();

        $Dto->name = $data['name'];
        $Dto->accept_prepaid = $data['accept_prepaid'];
        $Dto->accept_virtual = $data['accept_virtual'];
        $Dto->accept_real = $data['accept_real'];
        $Dto->accept_postpaid = $data['accept_postpaid'];
        $Dto->covers = $data['covers'];
        $Dto->max_limit = $data['max_limit'];
        $Dto->excluded_payment_methods = $data['excluded_payment_methods'] ?? null;
        $Dto->excluded_regions = $data['excluded_regions'] ?? null;
        $Dto->excluded_delivery_services = $data['excluded_delivery_services'] ?? null;
        $Dto->excluded_offer_statuses = $data['excluded_offer_statuses'] ?? null;
        $Dto->excluded_customers = $data['excluded_customers'] ?? null;
        $Dto->active = $data['active'];

        return $Dto;
    }
}
