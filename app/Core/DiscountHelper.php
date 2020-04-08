<?php

namespace App\Core;

use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Marketing\Dto\Discount\DiscountBrandDto;
use Greensight\Marketing\Dto\Discount\DiscountCategoryDto;
use Greensight\Marketing\Dto\Discount\DiscountConditionDto;
use Greensight\Marketing\Dto\Discount\DiscountDto;
use Greensight\Marketing\Dto\Discount\DiscountInDto;
use Greensight\Marketing\Dto\Discount\DiscountOfferDto;
use Greensight\Marketing\Dto\Discount\DiscountSegmentDto;
use Greensight\Marketing\Dto\Discount\DiscountStatusDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Marketing\Dto\Discount\DiscountUserRoleDto;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Greensight\Oms\Dto\PaymentMethod;
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
     * @param Request $request
     * @param array   $pager
     *
     * @return array
     */
    public static function getParams(Request $request, array $pager = [])
    {
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
        (isset($filter['created_at_from']) || isset($filter['created_at_to']))
            ? $discountInDto->createdAt($filter['created_at_from'] ?? null, $filter['created_at_to'] ?? null)
            : null;
        isset($filter['start_date'])
            ? $discountInDto->periodFrom($filter['start_date'], $filter['fix_start_date'] ?? false)
            : null;
        isset($filter['end_date'])
            ? $discountInDto->periodTo($filter['end_date'], $filter['fix_end_date'] ?? false)
            : null;
        isset($filter['indefinitely']) ? $discountInDto->indefinitely($filter['indefinitely']) : null;

        return $discountInDto
            ->status(DiscountStatusDto::STATUS_CREATED, true)
            ->sortDirection('desc')
            ->toQuery();
    }

    /**
     * @param array           $params
     * @param DiscountService $discountService
     *
     * @return Collection
     */
    public static function load(array $params, DiscountService $discountService): Collection
    {
        $discounts = $discountService->discounts($params);
        $discounts = $discounts->map(function (DiscountDto $discount) use ($discountService) {
            $data                   = $discount->toArray();
            $data['statusName']     = $discount->statusDto() ? $discount->statusDto()->name : 'N/A';
            $data['validityPeriod'] = $discount->validityPeriod();

            return $data;
        });

        return $discounts;
    }

    public static function getDiscountUsersInfo(DiscountService $discountService)
    {
        return $discountService->usersInfo([
            'filter' => [
                '!status' => DiscountStatusDto::STATUS_CREATED
            ]
        ]);
    }

    /**
     * @param array           $params
     * @param DiscountService $discountService
     *
     * @return int
     */
    public static function count(array $params, DiscountService $discountService)
    {
        $discounts = $discountService->discountsCount($params);

        return $discounts['total'] ?? 0;
    }

    /**
     * @param Request $request
     *
     * @return DiscountDto
     */
    public static function validate(Request $request)
    {
        $data = $request->validate([
            'name'            => 'string|required',
            'type'            => 'numeric|required',
            'value'           => 'numeric|required',
            'value_type'      => 'numeric|required',
            'start_date'      => 'string|nullable',
            'end_date'        => 'string|nullable',
            'promo_code_only' => 'boolean|required',
            'status'          => 'numeric|required',
            'offers'          => 'array',
            'bundles'         => 'array',
            'brands'          => 'array',
            'categories'      => 'array',
            'except'          => 'array',
            'conditions'      => 'array',
        ]);

        $data['start_date'] = $data['start_date']
            ? Carbon::createFromFormat('Y-m-d', $data['start_date'])->format('Y-m-d')
            : null;

        $data['end_date'] = $data['end_date']
            ? Carbon::createFromFormat('Y-m-d', $data['end_date'])->format('Y-m-d')
            : null;

        $discount = new DiscountDto([
            'merchant_id'     => null,
            'type'            => $data['type'],
            'name'            => $data['name'],
            'value_type'      => $data['value_type'],
            'value'           => $data['value'],
            'status'          => $data['status'],
            'start_date'      => $data['start_date'],
            'end_date'        => $data['end_date'],
            'promo_code_only' => $data['promo_code_only'],
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
     * Возваращает необходимые связи для скидки
     *
     * @param array $data
     *
     * @return array
     */
    public static function getDiscountRelations(array $data)
    {
        $relations = [
            DiscountDto::DISCOUNT_OFFER_RELATION     => [],
            DiscountDto::DISCOUNT_BRAND_RELATION     => [],
            DiscountDto::DISCOUNT_CATEGORY_RELATION  => [],
            DiscountDto::DISCOUNT_SEGMENT_RELATION   => [],
            DiscountDto::DISCOUNT_USER_ROLE_RELATION => [],
        ];

        switch ($data['type']) {
            case DiscountTypeDto::TYPE_OFFER:
                foreach ($data['offers'] as $offer) {
                    $relations[DiscountDto::DISCOUNT_OFFER_RELATION][] = (new DiscountOfferDto())
                        ->setExcept(false)
                        ->setOffer($offer);
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
         * Условия, которые выделени в отдельные DTO для оптимизации расчета скидки в каталоге
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
                    $conditions[] = $model->setMinPriceOrder((float)$condition['sum']);
                    break;
                case DiscountConditionDto::MIN_PRICE_BRAND:
                    if (empty($condition['brands'])) break;
                    $conditions[] = $model->setMinPriceBrands($condition['brands'], (float)$condition['sum']);
                    break;
                case DiscountConditionDto::MIN_PRICE_CATEGORY:
                    if (empty($condition['categories'])) break;
                    $conditions[] = $model->setMinPriceCategories($condition['categories'], (float)$condition['sum']);
                    break;
                case DiscountConditionDto::EVERY_UNIT_PRODUCT:
                    $conditions[] = $model->setEveryUnitProduct((int)$condition['offer'], (int)$condition['count']);
                    break;
                case DiscountConditionDto::DELIVERY_METHOD:
                    if (empty($condition['deliveryMethods'])) break;
                    $conditions[] = $model->setDeliveryMethods($condition['deliveryMethods']);
                    break;
                case DiscountConditionDto::PAY_METHOD:
                    if (empty($condition['paymentMethods'])) break;
                    $conditions[] = $model->setPaymentMethods($condition['paymentMethods']);
                    break;
                case DiscountConditionDto::REGION:
                    if (empty($condition['regions'])) break;
                    $conditions[] = $model->setRegions($condition['regions']);
                    break;
                case DiscountConditionDto::USER:
                    if (empty($condition['users'])) break;
                    $conditions[] = $model->setCustomers($condition['users']);
                    break;
                case DiscountConditionDto::ORDER_SEQUENCE_NUMBER:
                    $conditions[] = $model->setOrderSequenceNumber((int)$condition['sequenceNumber']);
                    break;
                case DiscountConditionDto::DISCOUNT_SYNERGY:
                    if (!empty($condition['synergy'])) {
                        $conditions[] = $model->setSynergy($condition['synergy']);
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
     * @param Request $request
     * @param int     $perPage
     *
     * @return array
     */
    public static function getDefaultPager(Request $request, int $perPage = 20)
    {
        return [
            'page'    => (int)$request->get('page', 1),
            'perPage' => $perPage,
        ];
    }

    /**
     * @param DiscountService $discountService
     * @param ListsService $listsService
     * @return array
     */
    public static function loadData(DiscountService $discountService, ListsService $listsService)
    {
        $data = [];
        $data['discountTypes'] = Helpers::getSelectOptions(DiscountTypeDto::allTypes());
        $data['conditionTypes'] = Helpers::getSelectOptions(DiscountConditionDto::allTypes());
        $data['deliveryMethods'] = Helpers::getSelectOptions(DeliveryMethod::allMethods())->values();
        $data['paymentMethods'] = Helpers::getSelectOptions(PaymentMethod::allMethods())->values();
        $data['roles'] = Helpers::getOptionRoles(false);
        $data['discountStatuses'] = Helpers::getSelectOptions(DiscountStatusDto::allStatuses());

        $query = $listsService->newQuery()->include('regions');
        $data['districts'] = $listsService->federalDistricts($query)->toArray();

        $params = (new DiscountInDto())->toQuery();
        $data['discounts'] = $discountService->discounts($params)
            ->sortByDesc('created_at')
            ->map(function (DiscountDto $item) {
                return ['value' => $item['id'], 'text' => "{$item['name']} ({$item->validityPeriod()})"];
            })
            ->values();

        return $data;
    }

    /**
     * @param int             $id
     * @param DiscountService $discountService
     * @param CategoryService $categoryService
     * @param ListsService    $listsService
     * @param BrandService    $brandService
     *
     * @return array
     * @throws \Pim\Core\PimException
     */
    public static function detail(
        int $id,
        DiscountService $discountService,
        CategoryService $categoryService,
        ListsService $listsService,
        BrandService $brandService
    ) {
        $params = (new DiscountInDto())
            ->id($id)
            ->status(DiscountStatusDto::STATUS_CREATED, true)
            ->withAll()
            ->toQuery();

        /** @var DiscountDto $discount */
        $discount = $discountService->discounts($params)->first();
        if (!$discount) {
            throw new NotFoundHttpException();
        }

        $title = '#' . $discount->id . ' ' . $discount->name;
        $data = DiscountHelper::loadData($discountService, $listsService);

        return [
            'title'            => $title,
            'iDiscount'        => $discount,
            'discounts'        => $data['discounts'],
            'discountTypes'    => $data['discountTypes'],
            'iConditionTypes'  => $data['conditionTypes'],
            'deliveryMethods'  => $data['deliveryMethods'],
            'discountStatuses' => $data['discountStatuses'],
            'paymentMethods'   => $data['paymentMethods'],
            'roles'            => $data['roles'],
            'iDistricts'       => $data['districts'],
            'categories'       => $categoryService->categories($categoryService->newQuery()),
            'brands'           => $brandService->brands($brandService->newQuery()),
        ];
    }
}
