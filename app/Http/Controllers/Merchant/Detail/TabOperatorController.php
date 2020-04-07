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
        $operators = $operatorService->operators((new RestQuery())->setFilter('merchant_id', $id));
        $users = $userService
            ->users((new RestQuery())->setFilter('id', $operators->pluck('user_id')->all()))
            ->keyBy('id');

        return response()->json([
            'operators' => $operators,
            'users' => $users,
        ]);
    }
}