<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PaymentMethod\UpdateRequest;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Oms\Dto\Payment\PaymentMethod;
use Greensight\Oms\Services\PaymentService\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PaymentMethodsController extends Controller
{
    /**
     * Информация о доступных способах оплаты и их настройки
     * @return mixed
     */
    public function list(PaymentService $paymentService): View
    {
        $this->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        $paymentMethods = $paymentService->getPaymentMethods()->keyBy('id');

        $this->title = 'Управление способами оплаты';

        return $this->render('Settings/PaymentMethodList', [
            'iMethods' => $paymentMethods,
        ]);
    }

    /** Страница редактирования */
    public function edit(int $id, PaymentService $paymentService): View
    {
        $this->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        $paymentMethod = $paymentService->getPaymentMethod($id);

        return $this->render('Settings/PaymentMethodDetail', [
            'iPaymentMethod' => $paymentMethod,
        ]);
    }

    /**
     * Изменить параметры способа оплаты
     */
    public function update(int $id, UpdateRequest $request, PaymentService $paymentService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $paymentService->updatePaymentMethod($id, new PaymentMethod($request->validated()));

        return response()->json([
            'paymentMethod' => $paymentService->getPaymentMethod($id),
        ]);
    }
}
