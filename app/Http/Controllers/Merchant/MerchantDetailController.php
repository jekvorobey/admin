<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\MerchantStatus;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MerchantDetailController extends Controller
{
    public function index(
        int $id,
        Request $request,
        MerchantService $merchantService,
        OperatorService $operatorService
    )
    {
        /** @var MerchantDto $merchant */
        $merchant = $merchantService
            ->newQuery()
            ->setFilter('id', $id)
            ->merchants()
            ->first();
        if (!$merchant) {
            throw new NotFoundHttpException();
        }

        $isRegistration = $request->routeIs('merchant.registrationDetail');
        $this->breadcrumbs = $isRegistration ? ['merchant.registrationList.detail', $merchant->id] : '';
        $this->title = $isRegistration ? "Заявка {$merchant->id}" : '';

        /** @var Collection|OperatorDto $operators */
        $operators = $operatorService->newQuery()->setFilter('merchant_id', $merchant->id)->operators();

        return $this->render('Merchant/MerchantDetail', [
            'iMerchant' => $merchant,
            'iOperators' => $operators,
            'options' => [
                'statuses' => MerchantStatus::allStatuses()
            ]
        ]);
    }

    public function updateMerchant(
        int $id,
        Request $request,
        MerchantService $merchantService
    )
    {
        $merchant = $merchantService->newQuery()->setFilter('id', $id)->merchants()->first();
        if (!$merchant) {
            throw new NotFoundHttpException('merchant not found');
        }
        $availableStatuses = array_keys(MerchantStatus::allStatuses());
        $data = $request->all();
        $fields = [
            'display_name' => 'nullable',
            'legal_name' => 'nullable',
            'legal_address' => 'nullable',
            'inn' => 'nullable|numeric',
            'kpp' => 'nullable',
            'payment_account' => 'nullable|numeric',
            'payment_account_bank' => 'nullable',
            'correspondent_account' => 'nullable|numeric',
            'correspondent_account_bank' => 'nullable',
            'status' => ['nullable', Rule::in($availableStatuses)]
        ];
        $validator = Validator::make(collect($data)->only(array_keys($fields))->all(), $fields);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }
        $editedMerchant = new MerchantDto($validator->getData());
        $editedMerchant->id = $id;
        $merchantService->update($editedMerchant);
        /** @var MerchantDto $newMerchant */
        $newMerchant = $merchantService->newQuery()->setFilter('id', $id)->merchants()->first();
        return response()->json([
            'merchant' => $newMerchant
        ]);
    }
}