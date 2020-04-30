<?php


namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use MerchantManagement\Dto\CommissionDto;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\OperatorCommunicationMethod;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\MerchantService\Dto\GetCommissionDto;
use MerchantManagement\Services\MerchantService\Dto\SaveCommissionDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;


class MerchantOperatorController extends Controller
{
    public function indexCreate(Request $request, MerchantService $merchantService)
    {
        $data = $request->validate([
            'merchant_id' => 'integer|sometimes',
        ]);

        $this->title = 'Создание оператора';
        $props = [];
        if (array_key_exists('merchant_id', $data)) {
            $props['merchantId'] = intval($data['merchant_id']);
        } else {
            $merchants = $merchantService->merchants((new RestQuery())->addFields(MerchantDto::entity(), 'id', 'legal_name'))
                ->all();
            $props['merchants'] = $merchants;
        }
        $props['communicationMethods'] = OperatorCommunicationMethod::allMethods();
        $props['roles'] = UserDto::rolesByFrontIds([Front::FRONT_MAS]);
        return $this->render('Merchant/Operator/Create', $props);
    }

    public function indexEdit(int $operatorId)
    {
        $this->title = 'Оператор: ';

        return $this->render('Merchant/Commission', [

        ]);
    }

    public function save(Request $request, UserService $userService, OperatorService $operatorService)
    {
        $userData = $request->validate([
            'last_name' => 'string|required',
            'first_name' => 'string|required',
            'middle_name' => 'string|required',
            'email' => 'email|required',
            'phone' => 'string|required',
            'login' => 'string|required',
            'password' => 'string|required',
            'roles' => 'array|required',
            'roles.' => Rule::in(UserDto::rolesByFrontIds([Front::FRONT_MAS])),
            'active' => 'bool|required',
        ]);

        $operatorData = $request->validate([
            'merchant_id' => 'integer|required',
            'position' => 'string|nullable',
            'communication_method' => [
                Rule::in(array_keys(OperatorCommunicationMethod::allMethods())),
                'required',
            ],
        ]);

        $userData['phone'] = phone_format($userData['phone']);
        $userData['front'] = Front::FRONT_MAS;
        $userId = $userService->create(new UserDto($userData));
        $userService->addRoles($userId, $userData['roles']);

        $operatorData['user_id'] = $userId;
        $operatorService->create(new OperatorDto($operatorData));

        return response('', 201);
    }

    public function update(MerchantService $merchantService)
    {


        return response()->json([
        ]);
    }

    public function changeRole(MerchantService $merchantService)
    {


        return response()->json([
        ]);
    }

    public function delete(MerchantService $merchantService)
    {


        return response()->json([
        ]);
    }
}