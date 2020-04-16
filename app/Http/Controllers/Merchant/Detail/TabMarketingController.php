<?php


namespace App\Http\Controllers\Merchant\Detail;


use App\Core\DiscountHelper;
use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\Marketing\Dto\Discount\DiscountStatusDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class TabMarketingController extends Controller
{
    /**
     * AJAX подгрузка информации для фильтрации скидок
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function loadDiscounts(int $id, DiscountService $discountService)
    {
        return response()->json([
            'discountStatuses' => DiscountStatusDto::allStatuses(),
            'discountTypes' => DiscountTypeDto::allTypes(),
            'roles' => Helpers::getOptionRoles(false),
            'authors' => DiscountHelper::getDiscountAuthors($discountService, $id),
            'userNames' => DiscountHelper::getUserNames(),
            'pager' => DiscountHelper::getDefaultPager(null, 5),
        ]);
    }


    /**
     * AJAX пагинация слайдера со скидками на странице мерчанта
     *
     * @param int $merchantId
     * @param Request $request
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function page(int $merchantId, Request $request, DiscountService $discountService)
    {
        $pager = DiscountHelper::getDefaultPager($request, 5);
        $params = DiscountHelper::getParams($request, $pager, $merchantId);
        $countParams = DiscountHelper::getParams($request, [], $merchantId);
        $discounts = DiscountHelper::load($params, $discountService);
        return response()->json([
            'discounts' => $discounts,
            'total' => DiscountHelper::count($countParams, $discountService),
        ]);
    }
}