<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\MerchantStatus;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Dto\RatingDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MerchantDetailController extends Controller
{
    public function index(
        int $id,
        MerchantService $merchantService,
        OperatorService $operatorService,
        UserService $userService
    ) {
        $this->loadMerchantStatuses = true;
        $this->loadMerchantCommissionTypes = true;
        /** @var MerchantDto $merchant */
        $merchant = $merchantService->merchants((new RestQuery())->setFilter('id', $id))->first();
        if (!$merchant) {
            throw new NotFoundHttpException();
        }

        $this->title = $merchant->legal_name;

        $isRequest = in_array($merchant->status, array_keys(MerchantStatus::statusesByMode(true)));

        /** @var OperatorDto $operatorMain */
        $operatorMain = $operatorService->operators(
            (new RestQuery())->setFilter('merchant_id', $merchant->id)->setFilter('is_main', true)
        )->first();
        if (is_null($operatorMain)) {
            /**
             * Если не найден оператор с флагом is_main, то берем первого попавшегося оператора
             */
            $operatorMain = $operatorService->operators(
                (new RestQuery())->setFilter('merchant_id', $merchant->id)
            )->first();
        }
        $userMain = null;
        if ($operatorMain) {
            /** @var UserDto $userMain */
            $userMain = $userService
                ->users((new RestQuery())->setFilter('id', $operatorMain->user_id))
                ->first();
        }

        $ratings = $merchantService->ratings()->sortByDesc('name');

        $managers = $userService->users((new RestQuery())->setFilter('role', UserDto::ADMIN__MANAGER_MERCHANT));

        return $this->render('Merchant/Detail', [
            'iMerchant' => [
                'id' => $merchant->id,
                'legal_name' => $merchant->legal_name,
                'status' => $merchant->status,
                'status_at' => $merchant->status_at,
                'city' => $merchant->city,
                'rating_id' => $merchant->rating_id,
                'manager_id' => $merchant->manager_id,
                'created_at' => $merchant->created_at,
                'legal_address' => $merchant->legal_address,
                'inn' => $merchant->inn,
                'kpp' => $merchant->kpp,
                'fact_address' => $merchant->fact_address,
                'ceo_last_name' => $merchant->ceo_last_name,
                'ceo_first_name' => $merchant->ceo_first_name,
                'ceo_middle_name' => $merchant->ceo_middle_name,
                'payment_account' => $merchant->payment_account,
                'correspondent_account' => $merchant->correspondent_account,
                'bank' => $merchant->bank,
                'bank_address' => $merchant->bank_address,
                'bank_bik' => $merchant->bank_bik,
                'storage_address' => $merchant->storage_address,
                'sale_info' => $merchant->sale_info,
                'vat_info' => $merchant->vat_info,
                'commercial_info' => $merchant->commercial_info,
                'contract_number' => $merchant->contract_number,
                'contract_at' => $merchant->contract_at ? Carbon::createFromFormat('Y-m-d H:i:s', $merchant->contract_at)->format('Y-m-d') : null,
                'main_operator' => [
                    'first_name' => $userMain ? $userMain->first_name : '',
                    'last_name' => $userMain ? $userMain->last_name : 'N/A',
                    'middle_name' => $userMain ? $userMain->middle_name : '',
                    'phone' => $userMain ? $userMain->phone : 'N/A',
                    'email' => $userMain ? $userMain->email : 'N/A',
                ],
            ],
            'statuses' => MerchantStatus::statusesByMode($isRequest),
            'ratings' => $ratings->map(function (RatingDto $ratingDto) {
                return [
                    'id' => $ratingDto->id,
                    'name' => $ratingDto->name,
                ];
            }),
            'managers' => $managers->sortByDesc('full_name')->map(function (UserDto $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                ];
            }),
            'isRequest' => $isRequest,
        ]);
    }

    public function updateMerchant(int $id, MerchantService $merchantService)
    {
        $data = $this->validate(request(), [
            'merchant.legal_name' => 'nullable',
            'merchant.status' => ['nullable', Rule::in(array_keys(MerchantStatus::allStatuses()))],
            'merchant.city' => 'nullable',
            'merchant.rating_id' => 'nullable|integer',
            'merchant.manager_id' => 'nullable|integer',

            'merchant.inn' => 'nullable|string|size:10',
            'merchant.kpp' => 'nullable|string|size:9',
            'merchant.fact_address' => 'nullable|string',
            'merchant.legal_address' => 'nullable|string',

            'merchant.ceo_last_name' => 'nullable|string',
            'merchant.ceo_first_name' => 'nullable|string',
            'merchant.ceo_middle_name' => 'nullable|string',

            'merchant.payment_account' => 'nullable|string|size:20',
            'merchant.correspondent_account' => 'nullable|string|size:20',
            'merchant.bank' => 'nullable|string',
            'merchant.bank_address' => 'nullable|string',
            'merchant.bank_bik' => 'nullable|string|size:9',

            'merchant.storage_address' => 'nullable|string',
            'merchant.sale_info' => 'nullable|string',
            'merchant.vat_info' => 'nullable|string',
            'merchant.commercial_info' => 'nullable|string',

            'merchant.contract_number' => 'nullable|string',
            'merchant.contract_at' => 'nullable|date_format:Y-m-d',
        ]);

        $editedMerchant = new MerchantDto($data['merchant']);
        $editedMerchant->id = $id;
        $merchantService->update($editedMerchant);

        return response('', 204);
    }
}