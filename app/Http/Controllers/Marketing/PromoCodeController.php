<?php

namespace App\Http\Controllers\Marketing;

use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\Marketing\Builder\PromoCode\PromoCodeBuilder;
use Greensight\Marketing\Dto\Discount\DiscountDto;
use Greensight\Marketing\Dto\Discount\DiscountInDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeInDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeOutDto;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Greensight\Marketing\Services\PromoCodeService\PromoCodeService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Carbon;

/**
 * Class PromoCodeController
 * @package App\Http\Controllers\Merchant
 */
class PromoCodeController extends Controller
{
    /**
     * Список промокодов
     *
     * @param Request          $request
     * @param PromoCodeService $promoCodeService
     *
     * @return mixed
     */
    public function index(Request $request, PromoCodeService $promoCodeService)
    {
        $this->title = 'Промокоды';
        $promoCodeInDto = new PromoCodeInDto();
        $promoCodes = $promoCodeService->promoCodes($promoCodeInDto)
            ->sortByDesc('created_at')
            ->map(function (PromoCodeOutDto $promoCode) {
                $promoCode['validityPeriod'] = $promoCode->validityPeriod();
                return $promoCode;
            })->values();

        return $this->render('Marketing/PromoCode/List', [
            'iPromoCodes' => $promoCodes,
            'iStatuses' => PromoCodeOutDto::allStatuses(),
            'iTypes' => PromoCodeOutDto::allTypes(),
        ]);
    }

    /**
     * Страница для создания промокода
     *
     * @param Request          $request
     * @param DiscountService  $discountService
     *
     * @param PromoCodeService $promoCodeService
     *
     * @return mixed
     */
    public function createPage(Request $request, DiscountService $discountService, PromoCodeService $promoCodeService)
    {
        $this->title = 'Создание промокода';
        $promoCodeTypes = PromoCodeOutDto::allTypes();
        $promoCodeStatuses = PromoCodeOutDto::allStatuses();
        $promoCodeInDto = new PromoCodeInDto();
        $promoCodes = $promoCodeService->promoCodes($promoCodeInDto)
            ->sortByDesc('created_at')
            ->map(function (PromoCodeOutDto $item) {
                return ['value' => $item['id'], 'text' => "#{$item['id']} – {$item['name']}"];
            })
            ->values();

        $params = (new DiscountInDto())->toQuery();
        $discounts = $discountService->discounts($params)
            ->sortByDesc('created_at')
            ->map(function (DiscountDto $item) {
                return ['value' => $item['id'], 'text' => "{$item['name']} ({$item->validityPeriod()})"];
            })
            ->values();

        return $this->render('Marketing/PromoCode/Create', [
            'iPromoCodes' => $promoCodes,
            'promoCodeTypes' => Helpers::getSelectOptions($promoCodeTypes),
            'promoCodeStatuses' => Helpers::getSelectOptions($promoCodeStatuses),
            'discounts' => $discounts,
            'gifts' => [],
            'bonuses' => [],
            'iRoles' => Helpers::getOptionRoles(false),
            'iSegments' => [['text' => 'A', 'value' => 1], ['text' => 'B', 'value' => 2]], // todo
        ]);
    }

    /**
     * @param Request          $request
     * @param PromoCodeService $promoCodeService
     */
    public function create(Request $request, PromoCodeService $promoCodeService)
    {
        $data = $request->validate([
            'owner_id' => 'numeric|nullable',
            'name' => 'string|required',
            'code' => 'string|required',
            'counter' => 'numeric|nullable',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'status' => 'numeric|required',
            'type' => 'numeric|required',
            'discount_id' => 'numeric|nullable',
            'gift_id' => 'numeric|nullable',
            'bonus_id' => 'numeric|nullable',
            'conditions' => 'array|nullable',
            'conditions.segments' => 'array|nullable',
            'conditions.segments.*' => 'numeric|nullable',
            'conditions.roles' => 'array|nullable',
            'conditions.roles.*' => 'numeric|nullable',
            'conditions.customers' => 'array|nullable',
            'conditions.customers.*' => 'numeric|nullable',
            'conditions.synergy' => 'array|nullable',
            'conditions.synergy.*' => 'numeric|nullable'
        ]);

        $data['creator_id'] = 1;
        try {
            $data['start_date'] = $data['start_date']
                ? Carbon::createFromFormat('Y-m-d', $data['start_date'])
                : null;

            $data['end_date'] = $data['end_date']
                ? Carbon::createFromFormat('Y-m-d', $data['end_date'])
                : null;
        } catch (Exception $ex) {

        }

        $builder = new PromoCodeBuilder($data);
        $result = $promoCodeService->create($builder);
        return response()->json(['status' => $result ? 'ok' : 'fail']);
    }

    /**
     * @param Request          $request
     * @param PromoCodeService $promoCodeService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request, PromoCodeService $promoCodeService)
    {
        $r = $promoCodeService->generate();
        return response()->json($r);
    }
}
