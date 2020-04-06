<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\MerchantStatus;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Dto\RatingDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MerchantDetailController extends Controller
{
    const ROLE_MANAGER = 7;

    public function index(
        int $id,
        MerchantService $merchantService,
        OperatorService $operatorService,
        UserService $userService
    ) {
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
        /** @var UserDto $userMain */
        $userMain = $userService
            ->users((new RestQuery())->setFilter('id', $operatorMain->user_id))
            ->first();

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
                'main_operator' => [
                    'first_name' => $userMain->first_name,
                    'last_name' => $userMain->last_name,
                    'middle_name' => $userMain->middle_name,
                    'phone' => $userMain->phone,
                    'email' => $userMain->email,
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
                    'name' => $user->full_name
                ];
            }),
            'isRequest' => $isRequest,
        ]);
    }

    public function updateMerchant(
        int $id,
        Request $request,
        MerchantService $merchantService
    ) {
        $merchant = $merchantService->newQuery()->setFilter('id', $id)->merchants()->first();
        if (!$merchant) {
            throw new NotFoundHttpException('merchant not found');
        }
        $availableStatuses = array_keys(MerchantStatus::allStatuses());
        $data = $request->all();
        $fields = [
            'legal_name' => 'nullable',
            'legal_address' => 'nullable',
            'inn' => 'nullable|numeric',
            'kpp' => 'nullable',
            'payment_account' => 'nullable|numeric',
            'payment_account_bank' => 'nullable',
            'correspondent_account' => 'nullable|numeric',
            'correspondent_account_bank' => 'nullable',
            'status' => ['nullable', Rule::in($availableStatuses)],
            'manager_id' => 'nullable|integer',
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
            'merchant' => $newMerchant,
        ]);
    }

    public function availableManagers(UserService $userService)
    {
        $managerQuery = new RestQuery();
        $managerQuery->setFilter('role', self::ROLE_MANAGER)->include('profile');
        $users = $userService->users($managerQuery)->map(function (UserDto $user) {
            return [
                'id' => $user->id,
                'name' => $user->full_name ?: $user->login,
            ];
        });

        return response()->json([
            'items' => $users,
        ]);
    }
}