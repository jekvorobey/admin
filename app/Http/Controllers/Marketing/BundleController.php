<?php

namespace App\Http\Controllers\Marketing;

use App\Core\DiscountHelper;
use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Marketing\Dto\Discount\DiscountStatusDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;

/**
 * Class BundleController
 * @package App\Http\Controllers\Merchant
 */
class BundleController extends Controller
{
    /**
     * Список бандлов
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @param RequestInitiator $user
     * @return mixed
     */
    public function index(Request $request, DiscountService $discountService, RequestInitiator $user)
    {
        $userId = $user->userId();
        $pager = DiscountHelper::getDefaultPager($request);
        $params = DiscountHelper::getParams($request, $userId, $pager, null, [
            DiscountTypeDto::TYPE_BUNDLE_OFFER,
            DiscountTypeDto::TYPE_BUNDLE_MASTERCLASS,
        ]);
        $countParams = DiscountHelper::getParams($request, $userId, [], null, [
            DiscountTypeDto::TYPE_BUNDLE_OFFER,
            DiscountTypeDto::TYPE_BUNDLE_MASTERCLASS,
        ]);
        $discounts = DiscountHelper::load($params, $discountService);
        $discountUserInfo = DiscountHelper::getDiscountUsersInfo($discountService, $userId);
        $pager['total'] = DiscountHelper::count($countParams, $discountService);

        $this->title = 'Бандлы';
        $this->loadDiscountTypes = true;
        return $this->render('Marketing/Discount/List', [
            'iDiscounts' => $discounts,
            'iCurrentPage' => $pager['page'],
            'roles' => Helpers::getOptionRoles(),
            'iFilter' => $params['filter'],
            'discountStatuses' => DiscountStatusDto::allStatuses(),
            'optionDiscountTypes' => collect(DiscountTypeDto::allTypes())->filter(function ($value, $key) {
                return $key === DiscountTypeDto::TYPE_BUNDLE_OFFER || $key === DiscountTypeDto::TYPE_BUNDLE_MASTERCLASS;
            }),
            'merchantNames' => DiscountHelper::getMerchantNames(),
            'userNames' => DiscountHelper::getUserNames(),
            'authors' => $discountUserInfo['authors'],
            'initiators' => $discountUserInfo['initiators'],
            'iPager' => $pager,
        ]);
    }

    /**
     * AJAX пагинация страниц с бандлами
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @param RequestInitiator $user
     * @return JsonResponse
     */
    public function page(Request $request, DiscountService $discountService, RequestInitiator $user)
    {
        $userId = $user->userId();
        $pager = DiscountHelper::getDefaultPager($request);
        $params = DiscountHelper::getParams($request, $userId, $pager, null, [
            DiscountTypeDto::TYPE_BUNDLE_OFFER,
            DiscountTypeDto::TYPE_BUNDLE_MASTERCLASS,
        ]);
        $countParams = DiscountHelper::getParams($request, $userId, [], null, [
            DiscountTypeDto::TYPE_BUNDLE_OFFER,
            DiscountTypeDto::TYPE_BUNDLE_MASTERCLASS,
        ]);
        $discounts = DiscountHelper::load($params, $discountService);
        return response()->json([
            'iDiscounts' => $discounts,
            'total' => DiscountHelper::count($countParams, $discountService),
        ]);
    }

    /**
     * Страница для создания бандла
     *
     * @param CategoryService $categoryService
     * @param BrandService    $brandService
     *
     * @return mixed
     * @throws PimException
     */
    public function createPage(CategoryService $categoryService, BrandService $brandService)
    {
        $data = DiscountHelper::loadData();
        $this->loadDiscountTypes = true;

        $this->title = 'Создание бандла';
        return $this->render('Marketing/Discount/Create', [
            'discounts' => $data['discounts'],
            'optionDiscountTypes' => $data['optionDiscountTypes']->filter(function ($value, $key) {
                return $key === DiscountTypeDto::TYPE_BUNDLE_OFFER || $key === DiscountTypeDto::TYPE_BUNDLE_MASTERCLASS;
            }),
            'iConditionTypes' => $data['conditionTypes'],
            'iDeliveryMethods' => $data['deliveryMethods'],
            'discountStatuses' => $data['discountStatuses'],
            'iPaymentMethods' => $data['paymentMethods'],
            'roles' => $data['roles'],
            'iDistricts' => $data['districts'],
            'categories' => $categoryService->categories($categoryService->newQuery()),
            'brands' => $brandService->brands($brandService->newQuery()),
        ]);
    }
}