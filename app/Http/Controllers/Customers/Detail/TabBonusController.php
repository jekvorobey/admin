<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Customer\Dto\CustomerBonusDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TabBonusController extends Controller
{
    /**
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function load(int $id)
    {
        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);
        $bonuses = $customerService->getBonuses($id);
        $bonusInfo = $customerService->getBonusInfo($id);

        $userService = resolve(UserService::class);
        $query = $userService->newQuery();
        $query->setFilter('front', Front::FRONT_ADMIN);
        $users = $userService->users($query);

        return response()->json([
            'bonuses' => [
                'available' => $bonusInfo->available,
                'on_hold' => $bonusInfo->on_hold,
                'items' => $bonuses,
            ],
            'statusNames' => CustomerBonusDto::statusesNames(),
            'userNames' => collect($users)->pluck('full_name', 'id'),
        ]);
    }

    /**
     * @param int              $id
     * @param Request          $request
     *
     * @param RequestInitiator $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(int $id, Request $request, RequestInitiator $user)
    {
        $data = $request->validate([
            'name' => 'string|required',
            'status' => 'integer|required',
            'value' => 'integer|required',
            'expiration_date' => 'date|nullable',
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
