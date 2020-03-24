<?php

namespace App\Http\Controllers\Customers\Detail;


use App\Http\Controllers\Controller;
use Greensight\Customer\Dto\ReferralBillOperationDto;
use Greensight\Customer\Services\ReferralService\Dto\GetReferralBillOperationDto;
use Greensight\Customer\Services\ReferralService\ReferralService;

class TabBillingController extends Controller
{
    public function load($id, ReferralService $referralService)
    {
        return response()->json([
            'operations' => $referralService->getReferralBillOperations(
                (new GetReferralBillOperationDto())->setReferralId($id)
            )->map(function(ReferralBillOperationDto $dto) {
                return [
                    'action_id' => $dto->action_id,
                    'created_at' => $dto->created_at,
                    'value' => $dto->value,
                    'type' => $dto->type,
                    'type_name' => $dto->getTypeName(),
                ];
            }),
            'types' => ReferralBillOperationDto::getTypes(),
        ]);
    }
}