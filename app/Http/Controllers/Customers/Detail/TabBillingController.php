<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Dto\ReferralBillOperationDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Services\ReferralService\Dto\CorrectReferralBillOperationDto;
use Greensight\Customer\Services\ReferralService\Dto\GetReferralBillOperationDto;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class TabBillingController extends Controller
{
    public function load($id): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        return response()->json([
            'operations' => $this->loadOperations($id),
            'types' => ReferralBillOperationDto::getTypes(),
        ]);
    }

    public function correct($id, ReferralService $referralService, CustomerService $customerService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $data = $this->validate(request(), [
            'comment' => 'required',
            'value' => 'numeric',
        ], [
            'comment' => 'Комментарий',
            'value' => 'Сумма',
        ]);

        $referralService->correctReferralBillOperations(
            (new CorrectReferralBillOperationDto())
                ->setReferralId($id)
                ->setValue($data['value'])
                ->setComment($data['comment'])
        );

        /** @var CustomerDto $customer */
        $customer = $customerService->customers((new RestQuery())->setFilter('id', $id))->first();

        return response()->json([
            'operations' => $this->loadOperations($id),
            'referral_bill' => $customer->referral_bill,
        ]);
    }

    protected function loadOperations($id): Collection
    {
        /** @var ReferralService $referralService */
        $referralService = resolve(ReferralService::class);
        return $referralService->getReferralBillOperations(
            (new GetReferralBillOperationDto())->setReferralId($id)
        )->map(function (ReferralBillOperationDto $dto) {
            return [
                'action_id' => $dto->action_id,
                'created_at' => $dto->created_at,
                'comment' => $dto->comment,
                'value' => $dto->value,
                'type' => $dto->type,
                'type_name' => $dto->getTypeName(),
            ];
        });
    }
}
