<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Core\DiscountHelper;
use App\Core\PromoCodeHelper;
use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Marketing\Dto\Discount\DiscountStatusDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeOutDto;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Greensight\Marketing\Services\PromoCodeService\PromoCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TabMarketingController extends Controller
{
    /**
     * AJAX подгрузка информации для фильтрации скидок
     */
    public function loadDiscountsData(
        int $merchantId,
        Request $request,
        DiscountService $discountService,
        RequestInitiator $user
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $userId = $user->userId();
        $discountUserInfo = DiscountHelper::getDiscountUsersInfo($discountService, $userId, $merchantId);

        return response()->json([
            'discountStatuses' => DiscountStatusDto::allStatuses(),
            'discountTypes' => DiscountTypeDto::allTypes(),
            'roles' => Helpers::getOptionRoles(false),
            'authors' => $discountUserInfo['authors'],
            'initiators' => $discountUserInfo['initiators'],
            'userNames' => DiscountHelper::getUserNames(),
            'pager' => DiscountHelper::getDefaultPager($request, 10),
        ]);
    }

    /**
     * AJAX пагинация слайдера со скидками на странице мерчанта
     */
    public function pageDiscounts(
        int $merchantId,
        Request $request,
        DiscountService $discountService,
        RequestInitiator $user
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $userId = $user->userId();
        $pager = DiscountHelper::getDefaultPager($request, 10);
        $params = DiscountHelper::getParams($request, $userId, $pager, $merchantId);
        $countParams = DiscountHelper::getParams($request, $userId, $pager, $merchantId);
        $discounts = DiscountHelper::load($params, $discountService);
        return response()->json([
            'discounts' => $discounts,
            'total' => DiscountHelper::count($countParams, $discountService),
        ]);
    }

    /**
     * AJAX подгрузка информации для фильтрации промокодов
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function loadPromoCodesData(int $merchantId): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        return response()->json([
            'types' => PromoCodeOutDto::allTypes(),
            'statuses' => PromoCodeOutDto::allStatuses(),
        ]);
    }

    /**
     * AJAX получение промокодов для слайдера промокодов
     */
    public function loadPromoCodes(
        int $merchantId,
        PromoCodeService $promoCodeService,
        DiscountService $discountService,
        CustomerService $customerService,
        UserService $userService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $discountPromoCodes = PromoCodeHelper::getDiscountPromoCodes($discountService, $promoCodeService, $merchantId);
        $merchantPromoCodes = PromoCodeHelper::getPromoCodes($promoCodeService, $merchantId);
        $promoCodes = $discountPromoCodes
            ->merge($merchantPromoCodes)
            ->keyBy('id')
            ->values();

        $referralIds = $promoCodes
            ->pluck('owner_id')
            ->filter()
            ->unique();
        $referrals = PromoCodeHelper::getReferrals($referralIds, $customerService);

        $creatorIds = $promoCodes
            ->pluck('creator_id')
            ->unique();
        $users = PromoCodeHelper::getUsers($creatorIds, $userService, $referrals);

        $promoCodes = PromoCodeHelper::formatPromoCodes($promoCodes, $referrals, $users);

        return response()->json([
            'promoCodes' => $promoCodes,
            'creators' => PromoCodeHelper::getCreators($creatorIds, $users),
            'owners' => PromoCodeHelper::getOwners($referralIds, $users, $referrals),
        ]);
    }
}
