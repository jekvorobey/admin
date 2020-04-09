<?php


namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\CommissionDto;
use MerchantManagement\Services\MerchantService\Dto\GetCommissionDto;
use MerchantManagement\Services\MerchantService\Dto\SaveCommissionDto;
use MerchantManagement\Services\MerchantService\MerchantService;

class MerchantCommissionController extends Controller
{
    public function index(MerchantService $merchantService)
    {
        $this->title = 'Взаиморасчёты';

        $commissions = $merchantService->commissions(
            (new GetCommissionDto())
                ->addType(CommissionDto::TYPE_GLOBAL)
                ->addType(CommissionDto::TYPE_RATING)
        );

        $ratings = $merchantService->ratings()->sortBy('name');

        /** @var CommissionDto|null $globalCommission */
        $globalCommission = $commissions->firstWhere('type', CommissionDto::TYPE_GLOBAL);
        $form = [
            [
                'value' => $globalCommission ? $globalCommission->value : '',
                'name' => 'Глобальный',
                'type' => CommissionDto::TYPE_GLOBAL,
                'rating_id' => null,
            ]
        ];

        foreach ($ratings as $rating) {
            /** @var CommissionDto|null $ratingCommission */
            $ratingCommission = $commissions->first(function (CommissionDto $commission) use ($rating) {
                return $commission->type == CommissionDto::TYPE_RATING && $commission->rating_id == $rating->id;
            });
            $form[] = [
                'value' => $ratingCommission ? $ratingCommission->value : '',
                'name' => "Рейтинг {$rating->name}",
                'type' => CommissionDto::TYPE_RATING,
                'rating_id' => $rating->id,
            ];
        }

        return $this->render('Merchant/Commission', [
            'iForm' => $form,
        ]);
    }

    public function save(MerchantService $merchantService)
    {
        $data = $this->validate(request(), [
            'type' => ['required', Rule::in([CommissionDto::TYPE_GLOBAL, CommissionDto::TYPE_RATING])],
            'value' => 'required|numeric',
            'rating_id' => request('type') == CommissionDto::TYPE_RATING ? 'required' : 'nullable',
        ]);

        $merchantService->saveCommission(
            (new SaveCommissionDto())
                ->setType($data['type'])
                ->setValue($data['value'])
                ->setRatingId($data['rating_id'])
        );

        return response('', 204);
    }
}