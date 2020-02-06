<?php

namespace App\Http\Controllers\Marketing;

use App\Core\Helpers;
use App\Core\DiscountHelper;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\RestRoleService;
use Greensight\Marketing\Dto\Discount\DiscountApprovalStatus;
use Greensight\Marketing\Dto\Discount\DiscountCondition;
use Greensight\Marketing\Dto\Discount\DiscountStatus;
use Greensight\Marketing\Dto\Discount\DiscountType;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Oms\Dto\PaymentMethod;
use Illuminate\Http\Request;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;


/**
 * Class DiscountController
 * @package App\Http\Controllers\Merchant
 */
class DiscountController extends Controller
{
    /**
     * Список скидок
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @return mixed
     */
    public function index(Request $request, DiscountService $discountService)
    {
        $this->title = 'Скидки';
        $page = $request->get('page', 1);
        $restQuery = new RestQuery();
        $filter = $request->get('filter', []);
        if (isset($filter['id'])) {
            $restQuery->setFilter('id', $filter['id']);
        }
        $restQuery->pageNumber($page, 20);
        $discounts = DiscountHelper::load($request, $restQuery, $discountService);
        $pager = $discountService->discountsCount($restQuery);

        return $this->render('Marketing/Discount/List', [
            'iDiscounts' => $discounts,
            'iCurrentPage' => (int)$page,
            'discountStatuses' => DiscountStatus::allStatuses(),
            'discountApprovalStatuses' => DiscountApprovalStatus::allStatuses(),
            'discountTypes' => DiscountType::allTypes(),
            'pager' => $pager,
        ]);
    }

    /**
     * Страница для создания администратором скидки
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @param CategoryService $categoryService
     * @param RestRoleService $roleService
     * @param ListsService $listsService
     * @param BrandService $brandService
     * @return mixed
     * @throws \Pim\Core\PimException
     */
    public function createPage(
        Request $request,
        DiscountService $discountService,
        CategoryService $categoryService,
        RestRoleService $roleService,
        ListsService $listsService,
        BrandService $brandService
    )
    {
        $this->title = 'Создание скидки';

        $discountTypes = Helpers::getSelectOptions(DiscountType::allTypes());
        $conditionTypes = Helpers::getSelectOptions(DiscountCondition::allTypes());
        $deliveryMethods = Helpers::getSelectOptions(DeliveryMethod::allMethods())->values();
        $paymentMethods = Helpers::getSelectOptions(PaymentMethod::allMethods())->values();
        $roles = Helpers::getSelectOptions($roleService->rolesByFront(Front::FRONT_SHOWCASE));

        $query = $listsService->newQuery()->include('regions');
        $districts = $listsService->federalDistricts($query)->toArray();

        $restQuery = new RestQuery();
        $discounts = DiscountHelper::load($request, $restQuery, $discountService)
            ->sortByDesc('created_at')
            ->map(function ($item) {
                return ['value' => $item['id'], 'text' => "{$item['name']} ({$item['validityPeriod']})"];
            })
            ->values();

        return $this->render('Marketing/Discount/Create', [
            'discounts' => $discounts,
            'discountTypes' => $discountTypes,
            'iConditionTypes' => $conditionTypes,
            'deliveryMethods' => $deliveryMethods,
            'paymentMethods' => $paymentMethods,
            'roles' => $roles,
            'iDistricts' => $districts,
            'categories' => $categoryService->categories($categoryService->newQuery()),
            'brands' => $brandService->brands($brandService->newQuery()),
        ]);
    }
}
