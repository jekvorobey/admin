<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\MerchantStatus;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
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
}