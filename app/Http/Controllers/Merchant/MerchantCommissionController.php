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
    public function index()
    {
        $this->title = 'Комиссия';

        return $this->render('Merchant/Commission', [
            'iForm' => $this->getForm(),
        ]);
    }

    public function save(MerchantService $merchantService)
    {
        $data = $this->validate(request(), [
            'id' => 'nullable|integer',
            'type' => ['required', Rule::in([CommissionDto::TYPE_GLOBAL, CommissionDto::TYPE_RATING])],
            'value' => 'required|numeric|min:0|max:100',
            'rating_id' => request('type') == CommissionDto::TYPE_RATING ? 'required' : 'nullable',
        ]);

        $merchantService->saveCommission(
            (new SaveCommissionDto())
                ->setId($data['id'])
                ->setType($data['type'])
                ->setValue($data['value'])
                ->setRatingId($data['rating_id'])
        );

        return response()->json([
            'form' => $this->getForm(),
        ]);
    }

    protected function getForm()
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

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
                'id' => $globalCommission ? $globalCommission->id : '',
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
                'id' => $ratingCommission ? $ratingCommission->id : '',
                'value' => $ratingCommission ? $ratingCommission->value : '',
                'name' => "Рейтинг {$rating->name}",
                'type' => CommissionDto::TYPE_RATING,
                'rating_id' => $rating->id,
            ];
        }

        return $form;
    }
}