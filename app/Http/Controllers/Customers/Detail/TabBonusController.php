<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Customer\Dto\CustomerBonusDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TabBonusController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function load(int $id)
    {
        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);
        $searchBonuses = $customerService->getBonuses($id);
        $bonusInfo = $customerService->getBonusInfo($id);

        $userService = resolve(UserService::class);
        $query = $userService->newQuery();
        $query->setFilter('front', Front::FRONT_ADMIN);
        $users = $userService->users($query);

        $statuses = [
            CustomerBonusDto::STATUS_ON_HOLD => 'На удержании',
            CustomerBonusDto::STATUS_ACTIVE => 'Активный',
            CustomerBonusDto::STATUS_DEBITED => 'Списание',
        ];

        return response()->json([
            'bonuses' => [
                'available' => $bonusInfo->available,
                'on_hold' => $bonusInfo->on_hold,
                'items' => $searchBonuses->bonuses,
            ],
            'statusNames' => $statuses,
            'userNames' => collect($users)->pluck('full_name', 'id'),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(int $id, Request $request, RequestInitiator $user)
    {
        $data = $request->validate([
            'name' => 'string|required',
            'status' => [
                'required',
                Rule::in([
                    CustomerBonusDto::STATUS_ON_HOLD,
                    CustomerBonusDto::STATUS_ACTIVE,
                    CustomerBonusDto::STATUS_DEBITED,
                ]),
            ],
            'value' => 'integer|required',
            'message' => 'string|min:1|required',
            'expiration_date' => 'date|after_or_equal:tomorrow|nullable',
        ]);

        $data['user_id'] = $user->userId();
        $data['customer_id'] = $id;
        $statuses = array_keys(CustomerBonusDto::statusesNames());
        if (!in_array($data['status'], $statuses)) {
            throw new BadRequestHttpException("Некорректный статус");
        }

        $customerBonusDto = new CustomerBonusDto($data);

        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);
        $result = $customerService->createBonus($customerBonusDto);

        return response()->json(['status' => $result ? 'ok' : 'fail']);
    }
}
