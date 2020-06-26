<?php

namespace App\Http\Controllers\Marketing;

use App\Core\DiscountHelper;
use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Marketing\Dto\Discount\DiscountDto;
use Greensight\Marketing\Dto\Discount\DiscountStatusDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\Offer\OfferDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\OfferService\OfferService;

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
     * @param RequestInitiator $user
     * @return mixed
     */
    public function index(Request $request, DiscountService $discountService, RequestInitiator $user)
    {
        $this->title = 'Скидки';
        $userId = $user->userId();
        $pager = DiscountHelper::getDefaultPager($request);
        $params = DiscountHelper::getParams($request, $userId, $pager);
        $countParams = DiscountHelper::getParams($request, $userId);
        $discounts = DiscountHelper::load($params, $discountService);
        $discountUserInfo = DiscountHelper::getDiscountUsersInfo($discountService, $userId);
        $pager['total'] = DiscountHelper::count($countParams, $discountService);

        $this->loadDiscountTypes = true;
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
     * @param RequestInitiator $user
     * @return JsonResponse
     */
    public function page(Request $request, DiscountService $discountService, RequestInitiator $user)
    {
        $userId = $user->userId();
        $pager = DiscountHelper::getDefaultPager($request);
        $params = DiscountHelper::getParams($request, $userId, $pager);
        $countParams = DiscountHelper::getParams($request, $userId);
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
            'iDeliveryMethods' => $data['deliveryMethods'],
            'discountStatuses' => $data['discountStatuses'],
            'iPaymentMethods' => $data['paymentMethods'],
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
     * @param OfferService $offerService
     *
     * @return mixed
     * @throws PimException
     */
    public function edit(int $id, OfferService $offerService)
    {
        $this->loadDiscountTypes = true;
        $data = DiscountHelper::detail($id);
        if ($data['iDiscount']['type'] === DiscountTypeDto::TYPE_BUNDLE_OFFER) {
            $data['offers'] = $offerService->offers(
                (new RestQuery())->include('product')
                    ->addFields(OfferDto::entity(), 'id')
                    ->addFields(ProductDto::entity(), 'id', 'name')
                    ->setFilter(
                        'id',
                        collect($data['iDiscount']->bundleItems)->pluck('item_id')
                            ->unique()
                            ->all()
                    )
            )->keyBy('id')
                ->map(function (OfferDto $offer) {
                    return [
                        'product_id' => $offer->product_id,
                        'name' => $offer->product->name,
                    ];
                })->all();
        }
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
