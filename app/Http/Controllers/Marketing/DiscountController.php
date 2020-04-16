<?php

namespace App\Http\Controllers\Marketing;

use App\Core\DiscountHelper;
use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Marketing\Dto\Discount\DiscountDto;
use Greensight\Marketing\Dto\Discount\DiscountStatusDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Core\PimException;
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
        $pager = DiscountHelper::getDefaultPager($request);
        $params = DiscountHelper::getParams($request, $pager);
        $countParams = DiscountHelper::getParams($request);
        $discounts = DiscountHelper::load($params, $discountService);
        $discountUserInfo = DiscountHelper::getDiscountUsersInfo($discountService);
        $pager['total'] = DiscountHelper::count($countParams, $discountService);

        return $this->render('Marketing/Discount/List', [
            'iDiscounts' => $discounts,
            'iCurrentPage' => $pager['page'],
            'roles' => Helpers::getOptionRoles(),
            'iFilter' => $params['filter'],
            'discountStatuses' => DiscountStatusDto::allStatuses(),
            'optionDiscountTypes' => DiscountTypeDto::allTypes(),
            'merchantNames' => DiscountHelper::getMerchantNames(),
            'userNames' => DiscountHelper::getUserNames(),
            'authors' => $discountUserInfo['authors'],
            'initiators' => $discountUserInfo['initiators'],
            'iPager' => $pager,
        ]);
    }

    /**
     * AJAX пагинация страниц со скидками
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function page(Request $request, DiscountService $discountService)
    {
        $pager = DiscountHelper::getDefaultPager($request);
        $params = DiscountHelper::getParams($request, $pager);
        $countParams = DiscountHelper::getParams($request);
        $discounts = DiscountHelper::load($params, $discountService);
        return response()->json([
            'iDiscounts' => $discounts,
            'total' => DiscountHelper::count($countParams, $discountService),
        ]);
    }

    /**
     * Страница для создания скидки
     *
     * @param CategoryService $categoryService
     * @param BrandService    $brandService
     *
     * @return mixed
     * @throws PimException
     */
    public function createPage(CategoryService $categoryService, BrandService $brandService)
    {
        $this->title = 'Создание скидки';
        $this->loadDiscountTypes = true;

        $data = DiscountHelper::loadData();
        return $this->render('Marketing/Discount/Create', [
            'discounts' => $data['discounts'],
            'optionDiscountTypes' => $data['optionDiscountTypes'],
            'iConditionTypes' => $data['conditionTypes'],
            'deliveryMethods' => $data['deliveryMethods'],
            'discountStatuses' => $data['discountStatuses'],
            'paymentMethods' => $data['paymentMethods'],
            'roles' => $data['roles'],
            'iDistricts' => $data['districts'],
            'categories' => $categoryService->categories($categoryService->newQuery()),
            'brands' => $brandService->brands($brandService->newQuery()),
        ]);
    }

    /**
     * Запрос на создание заявки на скидки
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function create(Request $request, DiscountService $discountService)
    {
        try {
            $discount = DiscountHelper::validate($request);
            $result = $discountService->create($discount);
        } catch (\Exception $ex) {
            return response()->json(['status' => $ex->getMessage()]);
        }

        return response()->json(['status' => $result ? 'ok' : 'fail']);
    }

    /**
     * @param int $id
     * @param Request $request
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function update(int $id, Request $request, DiscountService $discountService)
    {
        try {
            $discount = DiscountHelper::validate($request);
            $discountService->update($id, $discount);
        } catch (\Exception $ex) {
            return response()->json(['status' => $ex->getMessage()]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * @param int             $id
     *
     * @return mixed
     * @throws PimException
     */
    public function edit(int $id)
    {
        $this->loadDiscountTypes = true;
        $data = DiscountHelper::detail($id);
        $this->title = $data['title'];
        return $this->render('Marketing/Discount/Edit', $data);
    }


    /**
     * @param int             $id
     * @param DiscountService $discountService
     * @param CategoryService $categoryService
     * @param ListsService    $listsService
     * @param BrandService    $brandService
     *
     * @return mixed
     * @throws PimException
     */
    public function detail(int $id)
    {
        $data = DiscountHelper::detail($id);
        $data['KPI'] = resolve(OrderService::class)->orderDiscountKPI($id);

        $this->title = $data['title'];
        return $this->render('Marketing/Discount/Detail', $data);
    }

    /**
     * @param Request         $request
     * @param DiscountService $discountService
     *
     * @return JsonResponse
     */
    public function status(Request $request, DiscountService $discountService)
    {
        $data = $request->validate([
            'ids' => 'array|required',
            'ids.*' => 'integer|required',
            'status' => 'integer|required',
        ]);

        /** @var DiscountDto $discount */
        $discountService->updateStatuses($data['ids'], (int) $data['status']);
        return response()->json(['status' => 'ok']);
    }

    /**
     * @param Request         $request
     * @param DiscountService $discountService
     *
     * @return JsonResponse
     */
    public function delete(Request $request, DiscountService $discountService)
    {
        $data = $request->validate([
            'ids' => 'array|required',
            'ids.*' => 'integer|required',
        ]);

        /** @var DiscountDto $discount */
        $discountService->delete($data['ids']);
        return response()->json(['status' => 'ok']);
    }
}
