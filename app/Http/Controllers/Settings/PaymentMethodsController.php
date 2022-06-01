<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PaymentMethod\UpdateRequest;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Oms\Dto\Payment\PaymentMethod;
use Greensight\Oms\Services\PaymentService\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentMethodsController extends Controller
{
    /**
     * Информация о доступных способах оплаты и их настройки
     * @return mixed
     */
    public function list(PaymentService $paymentService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        $this->loadAllPaymentMethods = true;

        $paymentMethods = $paymentService->getPaymentMethods()->keyBy('id');

        $this->title = 'Управление способами оплаты';

        return $this->render('Settings/PaymentMethods', [
            'iMethods' => $paymentMethods,
        ]);
    }

    /**
     * Изменить параметры способа оплаты
     */
    public function edit(int $id, UpdateRequest $request, PaymentService $paymentService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $paymentService->updatePaymentMethod($id, new PaymentMethod($request->validated()));

        return response()->json([
            'paymentMethod' => $paymentService->getPaymentMethod($id),
        ]);
    }
}
