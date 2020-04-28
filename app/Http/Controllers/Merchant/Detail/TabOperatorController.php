<?php


namespace App\Http\Controllers\Merchant\Detail;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Illuminate\Support\Collection;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\OperatorService\OperatorService;

class TabOperatorController extends Controller
{
    public function load($id, OperatorService $operatorService, UserService $userService)
    {
        /** @var Collection|OperatorDto $operators */
        $operators = $operatorService->operators(
            (new RestQuery())->addFields(OperatorDto::class, 'user_id')
                ->setFilter('merchant_id', $id)
        );

        $users = $userService->users((new RestQuery())->setFilter('id', $operators->pluck('user_id')->all()))
            ->keyBy('id');

        return response()->json([
            'operators' => $operators->map(function ($operator, $key) use ($users) {
                return [
                    'user_id' => $operator->user_id,
                    'full_name' => $users[$operator->user_id]->full_name,
                    'email' => $users[$operator->user_id]->email,
                    'phone' => $users[$operator->user_id]->phone,
                    'is_receive_sms' => $operator->is_receive_sms,
                    'roles' => $users[$operator->user_id]->roles,
                    'is_main' => $operator->is_main,
                    'login' => $users[$operator->user_id]->login,
                ];
            }),
//            'users' => $users,
        ]);
    }
}