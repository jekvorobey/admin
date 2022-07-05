<?php

namespace App\Http\Controllers\Marketing;

use App\Core\DiscountHelper;
use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
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
     * @return mixed
     */
    public function index(Request $request, DiscountService $discountService, RequestInitiator $user)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

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
            'optionDiscountTypes' => collect(DiscountTypeDto::allTypes())->only(DiscountTypeDto::TYPE_BUNDLE_OFFER, DiscountTypeDto::TYPE_BUNDLE_MASTERCLASS),
            'merchantNames' => DiscountHelper::getMerchantNames(),
            'userNames' => DiscountHelper::getUserNames(),
            'authors' => $discountUserInfo['authors'],
            'initiators' => $discountUserInfo['initiators'],
            'iPager' => $pager,
        ]);
    }

    /**
     * AJAX пагинация страниц с бандлами
     */
    public function page(Request $request, DiscountService $discountService, RequestInitiator $user): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

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
     *
     * @return mixed
     * @throws PimException
     */
    public function createPage(CategoryService $categoryService, BrandService $brandService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        $data = DiscountHelper::loadData();
        $this->loadDiscountTypes = true;

        $this->title = 'Создание бандла';

        return $this->render('Marketing/Discount/Create', [
            'discounts' => $data['discounts'],
            'optionDiscountTypes' => $data['optionDiscountTypes']->only(DiscountTypeDto::TYPE_BUNDLE_OFFER, DiscountTypeDto::TYPE_BUNDLE_MASTERCLASS),
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
