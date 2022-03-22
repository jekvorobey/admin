<?php

namespace App\Http\Controllers\Marketing;

use App\Core\DiscountHelper;
use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Marketing\Core\MarketingException;
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
     * @return mixed
     */
    public function index(Request $request, DiscountService $discountService, RequestInitiator $user)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

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
     */
    public function page(Request $request, DiscountService $discountService, RequestInitiator $user): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

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
     * @return mixed
     * @throws PimException
     */
    public function createPage(CategoryService $categoryService, BrandService $brandService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

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
     */
    public function create(Request $request, DiscountService $discountService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        try {
            $discount = DiscountHelper::validate($request);
            $result = $discountService->create($discount);
        } catch (\Throwable $ex) {
            return response()->json(['status' => $ex->getMessage()]);
        }

        return response()->json(['status' => $result ? 'ok' : 'fail']);
    }

    public function update(int $id, Request $request, DiscountService $discountService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        try {
            $discount = DiscountHelper::validate($request);
            $discountService->update($id, $discount);
        } catch (\Throwable $ex) {
            return response()->json(['error' => $ex->getMessage()], 400);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
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
     * @return mixed
     * @throws PimException
     */
    public function detail(int $id)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->loadDiscountTypes = true;
        $this->loadDeliveryMethods = true;
        $this->loadOrderStatuses = true;
        $data = DiscountHelper::detail($id);
        $data['KPI'] = resolve(OrderService::class)->orderDiscountKPI($id);

        $this->title = $data['title'];

        return $this->render('Marketing/Discount/Detail', $data);
    }

    public function discountOrdersDetail(int $id, Request $request): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        // Orders
        $data = DiscountHelper::getOrdersByDiscount($id, $request);

        // Customers
        $customerIds = $data['orders']->pluck('customer_id')->all();
        $customers = resolve(CustomerService::class)->customers(
            (new RestQuery())
                ->addFields(CustomerDto::class, 'id', 'user_id')
                ->setFilter('id', '=', $customerIds)
        )->keyBy('id');

        // Users
        $userIds = $customers->pluck('user_id')->all();
        $users = resolve(UserService::class)->users(
            (new RestQuery())
                ->addFields(UserDto::class, 'id', 'full_name')
                ->setFilter('id', '=', $userIds)
        )->keyBy('id');

        $data['customers'] = $customers->map(function ($customer) use ($users) {
            return $users[$customer['user_id']]['full_name'] ?? '–';
        });

        return response()->json($data);
    }

    public function status(Request $request, DiscountService $discountService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $data = $request->validate([
            'ids' => 'array|required',
            'ids.*' => 'integer|required',
            'status' => 'integer|required',
        ]);

        $discountService->updateStatuses($data['ids'], (int) $data['status']);

        return response()->json(['status' => 'ok']);
    }

    /**
     * @throws MarketingException
     */
    public function delete(Request $request, DiscountService $discountService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $data = $request->validate([
            'ids' => 'array|required',
            'ids.*' => 'integer|required',
        ]);

        $discountService->delete($data['ids']);

        return response()->json(['status' => 'ok']);
    }
}
