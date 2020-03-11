<?php

namespace App\Http\Controllers\Referral;


use App\Http\Controllers\Controller;
use Greensight\Customer\Services\ReferralService\Dto\PutCommissionDto;
use Greensight\Customer\Services\ReferralService\Dto\PutLevelDto;
use Greensight\Customer\Services\ReferralService\Dto\PutSpecialCommissionDto;
use Greensight\Customer\Services\ReferralService\Dto\RemoveSpecialCommissionDto;
use Greensight\Customer\Services\ReferralService\ReferralService;

class LevelsController extends Controller
{
    public function list(ReferralService $referralService)
    {
        $this->title = 'Вознаграждения';

        $levels = $referralService->getLevels()->sortBy('sort')->values();
        return $this->render('Referral/Levels/List', [
            'levels' => $levels
        ]);
    }

    public function detail($level_id)
    {
        return response()->json([
            'level' => $this->loadLevel($level_id),
        ]);
    }

    public function putLevel($level_id, ReferralService $referralService)
    {
        $data = $this->validate(request(), [
            'sort' => 'required|integer',
            'name' => 'required',
            'referral_count' => 'required|integer',
            'order_personal_sum' => 'required|integer',
            'order_personal_count' => 'required|integer',
            'order_referral_sum' => 'required|integer',
        ]);

        $referralService->putLevel((new PutLevelDto())
            ->setLevelId($level_id)
            ->setName($data['name'])
            ->setSort($data['sort'])
            ->setReferralCount($data['referral_count'])
            ->setOrderPersonalSum($data['order_personal_sum'])
            ->setOrderPersonalCount($data['order_personal_count'])
            ->setOrderReferralSum($data['order_referral_sum'])
        );

        return response()->json([
            'level' => $this->loadLevel($level_id),
        ]);
    }

    public function putCommission($level_id, ReferralService $referralService)
    {
        $data = $this->validate(request(), [
            'percent_x' => 'required|numeric|min:0|max:100',
            'percent_y' => 'required|numeric|min:0|max:100',
            'percent_t' => 'required|numeric|min:0|max:100',
            'percent_p' => 'required|numeric|min:0|max:100',
            'percent_z' => 'required|numeric|min:0|max:100',
        ], [
            'percent_x' => 'Acquisition business',
            'percent_y' => 'Ongoing business',
            'percent_t' => 'Promo-business - 1',
            'percent_p' => 'Promo-business - 2',
            'percent_z' => 'Promo-business - 3',
        ]);

        $referralService->putCommission((new PutCommissionDto())
            ->setLevelId($level_id)
            ->setPercentX($data['percent_x'])
            ->setPercentY($data['percent_y'])
            ->setPercentT($data['percent_t'])
            ->setPercentP($data['percent_p'])
            ->setPercentZ($data['percent_z'])
        );

        return response()->json([
            'level' => $this->loadLevel($level_id),
        ]);
    }

    public function putSpecialCommission($level_id, ReferralService $referralService)
    {
        $data = $this->validate(request(), [
            'is_sum' => 'required|boolean',
            'coefficient' => 'required|numeric|min:0|max:100',
            'product_id' => 'nullable|numeric|required_without:brand_id',
            'brand_id' => 'nullable|numeric|required_without:product_id',
        ], [
            'is_sum' => 'Суммировать с другими типами',
            'coefficient' => 'Коэффициент',
            'product_id' => 'Товар',
            'brand_id' => 'Бренд',
        ]);

        $referralService->putSpecialCommission((new PutSpecialCommissionDto())
            ->setLevelId($level_id)
            ->setIsSum($data['is_sum'])
            ->setCoefficient($data['coefficient'])
            ->setProductId($data['product_id'] ?: null)
            ->setBrandId($data['brand_id'] ?: null)
        );

        return response()->json([
            'level' => $this->loadLevel($level_id),
        ]);
    }

    public function removeSpecialCommission($level_id, ReferralService $referralService)
    {
        $data = $this->validate(request(), [
            'product_id' => 'nullable|numeric|required_without:brand_id',
            'brand_id' => 'nullable|numeric|required_without:product_id',
        ], [
            'product_id' => 'Товар',
            'brand_id' => 'Бренд',
        ]);

        $referralService->removeSpecialCommission((new RemoveSpecialCommissionDto())
            ->setLevelId($level_id)
            ->setProductId($data['product_id'] ?: null)
            ->setBrandId($data['brand_id'] ?: null)
        );

        return response()->json([
            'level' => $this->loadLevel($level_id),
        ]);
    }

    protected function loadLevel($level_id)
    {
        $referralService = resolve(ReferralService::class);
        $level = $referralService->getLevel($level_id);

        $ar = $level->toArray();

        unset($ar['commissions']);
        $ar['commission'] = $level->commissions->first();

        return $ar;
    }
}