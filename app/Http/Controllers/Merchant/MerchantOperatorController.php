<?php


namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Dto\Front;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use MerchantManagement\Dto\CommissionDto;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\OperatorCommunicationMethod;
use MerchantManagement\Services\MerchantService\Dto\GetCommissionDto;
use MerchantManagement\Services\MerchantService\Dto\SaveCommissionDto;
use MerchantManagement\Services\MerchantService\MerchantService;


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
            $props['merchantId'] = $data['merchant_id'];
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

    public function save(MerchantService $merchantService)
    {


        return response()->json([
        ]);
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