<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Services\ReferralService\Dto\PutCommissionDto;
use Greensight\Customer\Services\ReferralService\Dto\PutLevelDto;
use Greensight\Customer\Services\ReferralService\Dto\PutSpecialCommissionDto;
use Greensight\Customer\Services\ReferralService\Dto\RemoveCommissionDto;
use Greensight\Customer\Services\ReferralService\Dto\RemoveSpecialCommissionDto;
use Greensight\Customer\Services\ReferralService\ReferralService;

class LevelsController extends Controller
{
    public function list(ReferralService $referralService, CustomerService $customerService, UserService $userService)
    {
        $this->title = 'Вознаграждения';

        $levels = $referralService->getLevels()->sortBy('sort')->values();

        $customers = $customerService->customers((new RestQuery())->setFilter('referral_code', 'notNull'));
        $users = collect();
        if ($customers->isNotEmpty()) {
            $users = $userService->users((new RestQuery())->setFilter('id', $customers->pluck('user_id')))->keyBy('id');
        }

        return $this->render('Referral/Levels', [
            'levels' => $levels,
            'customers' => $customers->map(function (CustomerDto $customerDto) use ($users) {
                /** @var UserDto $user */
                $user = $users->get($customerDto->user_id);
                return [
                    'id' => $customerDto->id,
                    'title' => $user ? $user->getTitle() : 'Не определено',
                ];
            })->sortBy('label')->values(),
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

        $referralService->putLevel(
            (new PutLevelDto())
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
        $isGlobal = !(bool) request('customer_id');
        $percent_rule = ($isGlobal ? 'required' : 'nullable') . '|numeric|min:0|max:100';
        $data = $this->validate(request(), [
            'customer_id' => 'nullable|integer',
            'percent_x' => $percent_rule,
            'percent_y' => $percent_rule,
            'percent_t' => $percent_rule,
            'percent_p' => $percent_rule,
            'percent_z' => $percent_rule,
        ], [
            'customer_id' => 'Реферальный партнёр',
            'percent_x' => 'Acquisition business',
            'percent_y' => 'Ongoing business',
            'percent_t' => 'Promo-business - 1',
            'percent_p' => 'Promo-business - 2',
            'percent_z' => 'Promo-business - 3',
        ]);

        $referralService->putCommission(
            (new PutCommissionDto())
            ->setLevelId($level_id)
            ->setCustomerId($data['customer_id'])
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

    public function removeCommission($level_id, ReferralService $referralService)
    {
        $data = $this->validate(request(), [
            'customer_id' => 'required|integer',
        ], [
            'customer_id' => 'Реферальный партнёр',
        ]);

        $referralService->removeCommission(
            (new RemoveCommissionDto())
            ->setLevelId($level_id)
            ->setCustomerId($data['customer_id'])
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

        $referralService->putSpecialCommission(
            (new PutSpecialCommissionDto())
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

        $referralService->removeSpecialCommission(
            (new RemoveSpecialCommissionDto())
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
        /** @var ReferralService $referralService */
        $referralService = resolve(ReferralService::class);

        return $referralService->getLevel($level_id);
    }
}
