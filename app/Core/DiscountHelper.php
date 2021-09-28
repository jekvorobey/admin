<?php

namespace App\Core;

use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Marketing\Dto\Discount\BundleItemDto;
use Greensight\Marketing\Dto\Discount\DiscountBrandDto;
use Greensight\Marketing\Dto\Discount\DiscountCategoryDto;
use Greensight\Marketing\Dto\Discount\DiscountConditionDto;
use Greensight\Marketing\Dto\Discount\DiscountDto;
use Greensight\Marketing\Dto\Discount\DiscountInDto;
use Greensight\Marketing\Dto\Discount\DiscountOfferDto;
use Greensight\Marketing\Dto\Discount\DiscountPublicEventDto;
use Greensight\Marketing\Dto\Discount\DiscountSegmentDto;
use Greensight\Marketing\Dto\Discount\DiscountStatusDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Marketing\Dto\Discount\DiscountUserRoleDto;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Greensight\Oms\Dto\Payment\PaymentMethod;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DiscountHelper
{
    /**
     * @param array $pager
     * @param array $type
     * @return array
     */
    public static function getParams(
        Request $request,
        int $userId,
        array $pager = [],
        ?int $merchantId = null,
        array $type = []
    ) {
        $discountInDto = new DiscountInDto();

        if (!empty($pager)) {
            $discountInDto->pagination($pager['page'], $pager['perPage']);
        }

        $filter = $request->get('filter', []);
        isset($filter['id']) ? $discountInDto->id($filter['id']) : null;
        isset($filter['name']) ? $discountInDto->name($filter['name']) : null;
        isset($filter['status']) ? $discountInDto->status($filter['status']) : null;
        isset($filter['user_id']) ? $discountInDto->user($filter['user_id']) : null;
        isset($filter['merchant_id']) ? $discountInDto->merchant($filter['merchant_id']) : null;
        isset($filter['type']) ? $discountInDto->type($filter['type']) : null;
        isset($filter['role_id']) ? $discountInDto->role($filter['role_id']) : null;
        isset($filter['created_at_from']) || isset($filter['created_at_to'])
            ? $discountInDto->createdAt($filter['created_at_from'] ?? null, $filter['created_at_to'] ?? null)
            : null;
        isset($filter['start_date'])
            ? $discountInDto->periodFrom($filter['start_date'], $filter['fix_start_date'] ?? false)
            : null;
        isset($filter['end_date'])
            ? $discountInDto->periodTo($filter['end_date'], $filter['fix_end_date'] ?? false)
            : null;
        isset($filter['indefinitely']) ? $discountInDto->indefinitely($filter['indefinitely']) : null;

        $merchantId ? $discountInDto->merchant($merchantId) : null;
        $type ? $discountInDto->type($type) : null;

        return $discountInDto
            ->status(DiscountStatusDto::STATUS_CREATED, true, $userId)
            ->sortDirection('desc')
            ->toQuery();
    }

    /**
     * @param array $params
     */
    public static function load(array $params, DiscountService $discountService): Collection
    {
        $discounts = $discountService->discounts($params);
        $discounts = $discounts->map(function (DiscountDto $discount) {
            $data = $discount->toArray();
            $data['statusName'] = $discount->statusDto() ? $discount->statusDto()->name : 'N/A';
            $data['validityPeriod'] = $discount->validityPeriod();

            return $data;
        });

        return $discounts;
    }

    public static function getDiscountUsersInfo(DiscountService $discountService, int $userId, $merchantId = null)
    {
        $filter = [
            'filter' => [
                '!status' => DiscountStatusDto::STATUS_CREATED,
                '!status_user_id' => $userId,
            ],
        ];
        if ($merchantId) {
            $filter['filter']['merchant_id'] = $merchantId;
        }
        return $discountService->usersInfo($filter);
    }

    /**
     * @param array $params
     *
     * @return int
     */
    public static function count(array $params, DiscountService $discountService)
    {
        $discounts = $discountService->discountsCount($params);

        return $discounts['total'] ?? 0;
    }

    /**
     * @return DiscountDto
     */
    public static function validate(Request $request)
    {
        $data = $request->validate([
            'name' => 'string|required',
            'type' => 'numeric|required',
            'value' => 'numeric|required',
            'value_type' => 'numeric|required',
            'merchant_id' => 'numeric|nullable',
            'start_date' => 'string|nullable',
            'end_date' => 'string|nullable',
            'promo_code_only' => 'boolean|required',
            'status' => 'numeric|required',
            'product_qty_limit' => 'numeric|nullable',
            'offers' => 'array',
            'bundle_items' => 'array',
            'brands' => 'array',
            'categories' => 'array',
            'public_events' => 'array',
            'except' => 'array',
            'conditions' => 'array',
        ]);

        $data['start_date'] = $data['start_date']
            ? Carbon::createFromFormat('Y-m-d', $data['start_date'])->format('Y-m-d')
            : null;

        $data['end_date'] = $data['end_date']
            ? Carbon::createFromFormat('Y-m-d', $data['end_date'])->format('Y-m-d')
            : null;

        $data['merchant_id'] ??= null;

        $discount = new DiscountDto([
            'merchant_id' => $data['merchant_id'],
            'type' => $data['type'],
            'name' => $data['name'],
            'value_type' => $data['value_type'],
            'value' => $data['value'],
            'status' => $data['status'],
            'product_qty_limit' => $data['limit'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'promo_code_only' => $data['promo_code_only'],
            'relations' => [],
        ]);

        $arRelations = DiscountHelper::getDiscountRelations($data);
        foreach ($arRelations as $type => $relations) {
            foreach ($relations as $relation) {
                $discount->addRelation($relation, $type);
            }
        }

        $conditions = DiscountHelper::getDiscountCondition($data);
        foreach ($conditions as $condition) {
            $discount->addCondition($condition);
        }

        return $discount;
    }

    /**
     * Возвращает необходимые связи для скидки
     * @param array $data
     * @return array
     */
    public static function getDiscountRelations(array $data)
    {
        $relations = [
            DiscountDto::DISCOUNT_OFFER_RELATION => [],
            DiscountDto::DISCOUNT_BRAND_RELATION => [],
            DiscountDto::DISCOUNT_CATEGORY_RELATION => [],
            DiscountDto::DISCOUNT_SEGMENT_RELATION => [],
            DiscountDto::DISCOUNT_USER_ROLE_RELATION => [],
            DiscountDto::DISCOUNT_BUNDLE_RELATION => [],
            DiscountDto::DISCOUNT_PUBLIC_EVENT_RELATION => [],
        ];

        switch ($data['type']) {
            case DiscountTypeDto::TYPE_OFFER:
                foreach ($data['offers'] as $offer) {
                    $relations[DiscountDto::DISCOUNT_OFFER_RELATION][] = (new DiscountOfferDto())
                        ->setExcept(false)
                        ->setOffer($offer);
                }
                break;
            case DiscountTypeDto::TYPE_BUNDLE_OFFER:
            case DiscountTypeDto::TYPE_BUNDLE_MASTERCLASS:
                foreach ($data['bundle_items'] as $bundleItem) {
                    $relations[DiscountDto::DISCOUNT_BUNDLE_RELATION][] = (new BundleItemDto())
                        ->setItem($bundleItem);
                }
                break;
            case DiscountTypeDto::TYPE_MASTERCLASS:
                foreach ($data['public_events'] as $tickedTypeId) {
                    $relations[DiscountDto::DISCOUNT_PUBLIC_EVENT_RELATION][] = (new DiscountPublicEventDto())
                        ->setTicketTypeId($tickedTypeId);
                }
                break;
            case DiscountTypeDto::TYPE_BRAND:
                foreach ($data['brands'] as $brand) {
                    $relations[DiscountDto::DISCOUNT_BRAND_RELATION][] = (new DiscountBrandDto())
                        ->setExcept(false)
                        ->setBrand($brand);
                }

                // Скидка на бренд за исключением следующих офферов
                if (isset($data['except']['offers'])) {
                    foreach ($data['except']['offers'] as $offer) {
                        $relations[DiscountDto::DISCOUNT_OFFER_RELATION][] = (new DiscountOfferDto())
                            ->setExcept(true)
                            ->setOffer($offer);
                    }
                }
                break;
            case DiscountTypeDto::TYPE_CATEGORY:
                foreach ($data['categories'] as $category) {
                    $relations[DiscountDto::DISCOUNT_CATEGORY_RELATION][] = (new DiscountCategoryDto())
                        ->setExcept(false)
                        ->setCategory($category);
                }

                // Скидка на категорию за исключением следующих офферов
                if (isset($data['except']['offers'])) {
                    foreach ($data['except']['offers'] as $offer) {
                        $relations[DiscountDto::DISCOUNT_OFFER_RELATION][] = (new DiscountOfferDto())
                            ->setExcept(true)
                            ->setOffer($offer);
                    }
                }

                // Скидка на категорию за исключением следующих брендов
                if (isset($data['except']['brands'])) {
                    foreach ($data['except']['brands'] as $brand) {
                        $relations[DiscountDto::DISCOUNT_BRAND_RELATION][] = (new DiscountBrandDto())
                            ->setExcept(true)
                            ->setBrand($brand);
                    }
                }
                break;
        }

        /**
         * Условия, которые выделены в отдельные DTO для оптимизации расчета скидки в каталоге
         */
        foreach ($data['conditions'] as $condition) {
            if ($condition['type'] !== DiscountConditionDto::USER) {
                continue;
            }

            if (isset($condition['segments']) && count($condition['segments']) > 0) {
                foreach ($condition['segments'] as $segment) {
                    $relations[DiscountDto::DISCOUNT_SEGMENT_RELATION][] = (new DiscountSegmentDto())
                        ->setExcept(false)
                        ->setSegment($segment);
                }
            }

            if (isset($condition['roles']) && count($condition['roles']) > 0) {
                foreach ($condition['roles'] as $role) {
                    if (!$role) {
                        continue;
                    }

                    $relations[DiscountDto::DISCOUNT_USER_ROLE_RELATION][] = (new DiscountUserRoleDto())
                        ->setExcept(false)
                        ->setRole($role);
                }
            }
        }

        return $relations;
    }

    /**
     * Возвращает условия возникновения скидки
     *
     * @param array $data
     *
     * @return array
     */
    public static function getDiscountCondition($data)
    {
        $conditions = [];

        foreach ($data['conditions'] as $condition) {
            $model = new DiscountConditionDto();
            switch ($condition['type']) {
                case DiscountConditionDto::FIRST_ORDER:
                    $conditions[] = $model->setFirstOrder();
                    break;
                case DiscountConditionDto::MIN_PRICE_ORDER:
                    $conditions[] = $model->setMinPriceOrder((float) $condition['sum']);
                    break;
                case DiscountConditionDto::MIN_PRICE_BRAND:
                    if (empty($condition['brands'])) {
                        break;
                    }
                    $conditions[] = $model->setMinPriceBrands($condition['brands'], (float) $condition['sum']);
                    break;
                case DiscountConditionDto::MIN_PRICE_CATEGORY:
                    if (empty($condition['categories'])) {
                        break;
                    }
                    $conditions[] = $model->setMinPriceCategories($condition['categories'], (float) $condition['sum']);
                    break;
                case DiscountConditionDto::EVERY_UNIT_PRODUCT:
                    $conditions[] = $model->setEveryUnitProduct((int) $condition['offer'], (int) $condition['count']);
                    break;
                case DiscountConditionDto::DELIVERY_METHOD:
                    if (empty($condition['deliveryMethods'])) {
                        break;
                    }
                    $conditions[] = $model->setDeliveryMethods($condition['deliveryMethods']);
                    break;
                case DiscountConditionDto::PAY_METHOD:
                    if (empty($condition['paymentMethods'])) {
                        break;
                    }
                    $conditions[] = $model->setPaymentMethods($condition['paymentMethods']);
                    break;
                case DiscountConditionDto::REGION:
                    if (empty($condition['regions'])) {
                        break;
                    }
                    $conditions[] = $model->setRegions($condition['regions']);
                    break;
                case DiscountConditionDto::USER:
                    if (empty($condition['users'])) {
                        break;
                    }
                    $conditions[] = $model->setCustomers($condition['users']);
                    break;
                case DiscountConditionDto::ORDER_SEQUENCE_NUMBER:
                    $conditions[] = $model->setOrderSequenceNumber((int) $condition['sequenceNumber']);
                    break;
                case DiscountConditionDto::DISCOUNT_SYNERGY:
                    if (!empty($condition['synergy'])) {
                        $conditions[] = $model->setSynergy(
                            $condition['synergy'],
                            $condition['maxValueType'] ?? null,
                            $condition['maxValue'] ?? null
                        );
                    }
                    break;
            }
        }

        if (!empty($data['bundles'])) {
            $conditions[] = (new DiscountConditionDto())->setBundles($data['bundles']);
        }

        return $conditions;
    }

    /**
     * @return array|Collection
     */
    public static function getMerchantNames()
    {
        $merchantService = resolve(MerchantService::class);
        $merchants = $merchantService->newQuery()
            ->addFields(MerchantDto::entity(), 'id', 'legal_name')
            ->merchants();

        return collect($merchants)->pluck('legal_name', 'id');
    }

    /**
     * @return array|Collection
     */
    public static function getUserNames()
    {
        $userService = resolve(UserService::class);
        $query = $userService->newQuery();
        $users = $userService->users($query);

        return collect($users)->pluck('login', 'id');
    }

    /**
     * @return array
     */
    public static function getDefaultPager(Request $request, int $perPage = 20)
    {
        return [
            'page' => (int) $request->get('page', 1),
            'perPage' => $perPage,
        ];
    }

    /**
     * @return array
     */
    public static function loadData()
    {
        $discountService = resolve(DiscountService::class);
        $listsService = resolve(ListsService::class);
        $merchantService = resolve(MerchantService::class);

        $data = [];
        $data['optionDiscountTypes'] = Helpers::getSelectOptions(DiscountTypeDto::allTypes());
        $data['conditionTypes'] = Helpers::getSelectOptions(DiscountConditionDto::allTypes());
        $data['deliveryMethods'] = Helpers::getSelectOptions(DeliveryMethod::allMethods())->values();
        $data['paymentMethods'] = Helpers::getSelectOptions(PaymentMethod::allMethods())->values();
        $data['roles'] = Helpers::getOptionRoles(false);
        $data['discountStatuses'] = Helpers::getSelectOptions(DiscountStatusDto::allStatuses());
        $data['merchants'] = $merchantService->merchants()->map(function (MerchantDto $merchant) {
            return ['id' => $merchant->id, 'name' => $merchant->legal_name];
        });

        $query = $listsService->newQuery()->include('regions');
        $data['districts'] = $listsService->federalDistricts($query)->toArray();

        $params = (new DiscountInDto())->toQuery();
        $data['discounts'] = $discountService->discounts($params)
            ->sortByDesc('created_at')
            ->map(function (DiscountDto $item) {
                return [
                    'value' => $item['id'],
                    'text' => "{$item['name']} ({$item->validityPeriod()})",
                    'type' => $item->type,
                ];
            })
            ->values();

        return $data;
    }

    /**
     * @return array
     * @throws \Pim\Core\PimException
     */
    public static function detail(int $id)
    {
        $discountService = resolve(DiscountService::class);
        $categoryService = resolve(CategoryService::class);
        $brandService = resolve(BrandService::class);
        $userService = resolve(UserService::class);
        $user = resolve(RequestInitiator::class);

        $userId = $user->userId();
        $params = (new DiscountInDto())
            ->id($id)
            ->status(DiscountStatusDto::STATUS_CREATED, true, $userId)
            ->withAll()
            ->toQuery();

        /** @var DiscountDto $discount */
        $discount = $discountService->discounts($params)->first();
        if (!$discount) {
            throw new NotFoundHttpException();
        }

        $title = '#' . $discount->id . ' ' . $discount->name;
        $data = DiscountHelper::loadData();
        $query = $userService->newQuery()->setFilter('id', $discount->user_id);
        $author = $userService->users($query)->first();

        return [
            'title' => $title,
            'iDiscount' => $discount,
            'discounts' => $data['discounts'],
            'optionDiscountTypes' => $data['optionDiscountTypes'],
            'iConditionTypes' => $data['conditionTypes'],
            'iDeliveryMethods' => $data['deliveryMethods'],
            'discountStatuses' => $data['discountStatuses'],
            'iPaymentMethods' => $data['paymentMethods'],
            'roles' => $data['roles'],
            'iDistricts' => $data['districts'],
            'merchants' => $data['merchants'],
            'author' => $author,
            'categories' => $categoryService->categories($categoryService->newQuery()),
            'brands' => $brandService->brands($brandService->newQuery()),
        ];
    }

    /**
     * @param $id
     *
     * @return array
     */
    public static function getOrdersByDiscount($id, Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);
        $filter = $request->get('filter', []);

        $query = new RestQuery();
        $query->include('discounts');
        $query->setFilter('discount_id', $id);
        $query->pageNumber($page, $perPage);
        $query->addSort('created_at', 'desc');

        foreach ($filter as $field => $value) {
            if (!isset($value)) {
                continue;
            }

            switch ($field) {
                case 'orderStatus':
                    $query->setFilter('status', $value);
                    break;
                case 'orderDateFrom':
                    $query->setFilter('created_at', '>=', $value . ' 00:00:00');
                    break;
                case 'orderDateTo':
                    $query->setFilter('created_at', '<=', $value . ' 23:59:59');
                    break;
                case 'orderNum':
                    $query->setFilter('number', 'like', "%{$value}%");
                    break;
                case 'orderCostFrom':
                    $query->setFilter('cost', '>=', $value);
                    break;
                case 'orderCostTo':
                    $query->setFilter('cost', '<=', $value);
                    break;
                case 'buyer':
                    $customerIds = DiscountHelper::getCustomerIdsByFullName($value);
                    $query->setFilter('customer_id', '=', $customerIds);
                    break;
                case 'orderPriceFrom':
                    $query->setFilter('min_price_for_current_discount', $value);
                    break;
                case 'orderPriceTo':
                    $query->setFilter('max_price_for_current_discount', $value);
                    break;
            }
        }

        $data = [];
        $data['orders'] = resolve(OrderService::class)->orders($query)->map(function ($order) use ($id) {
            return [
                'id' => $order['id'],
                'number' => $order['number'],
                'customer_id' => $order['customer_id'],
                'cost' => $order['cost'],
                'price' => $order['price'],
                'status' => $order['status'],
                'created_at' => $order['created_at'],
                'discount' => collect($order['discounts'])->filter(function ($discount) use ($id) {
                    return $discount['discount_id'] === $id;
                })->sum('change'),
            ];
        });
        $data['count'] = resolve(OrderService::class)->ordersCount($query);

        return $data;
    }

    /**
     * @return array
     */
    public static function getCustomerIdsByFullName(string $fullName)
    {
        // Users
        $users = resolve(UserService::class)->users(
            (new RestQuery())
                ->addFields(UserDto::class, 'id')
                ->setFilter('full_name', 'like', "%{$fullName}%")
        )->keyBy('id');

        // Customers
        $userIds = $users->keys()->toArray();
        $customers = resolve(CustomerService::class)->customers(
            (new RestQuery())
                ->addFields(CustomerDto::class, 'id')
                ->setFilter('user_id', '=', $userIds)
        )->keyBy('id');

        return $customers->keys()->toArray();
    }
}
