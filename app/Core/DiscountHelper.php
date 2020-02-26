<?php

namespace App\Core;

use Greensight\CommonMsa\Dto\UserDto;
use Greensight\Marketing\Dto\Discount\DiscountBrandDto;
use Greensight\Marketing\Dto\Discount\DiscountCategoryDto;
use Greensight\Marketing\Dto\Discount\DiscountConditionDto;
use Greensight\Marketing\Dto\Discount\DiscountDto;
use Greensight\Marketing\Dto\Discount\DiscountOfferDto;
use Greensight\Marketing\Dto\Discount\DiscountSegmentDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Marketing\Dto\Discount\DiscountUserRoleDto;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DiscountHelper
{
    /**
     * @param Request $request
     * @param array $pager
     * @return array
     */
    public static function getParams(Request $request, array $pager = [])
    {
        $whiteListFilters = [
            'id',
            'name',
            'status',
            'start_date',
            'fix_start_date',
            'end_date',
            'fix_end_date',
            'type',
            'role_id',
        ];

        $params = [];
        $params['sort'] = 'desc';
        $params['filter'] = [];
        if (!empty($pager)) {
            $params['page'] = $pager['page'];
            $params['perPage'] = $pager['perPage'];
        }

        $filter = $request->get('filter', []);
        foreach ($filter as $key => $value) {
            if (in_array($key, $whiteListFilters)) {
                $params['filter'][$key] = $value;
            }
        }

        $params['filter']['!status'] = DiscountDto::STATUS_CREATED;

        return $params;
    }

    /**
     * @param array $params
     * @param DiscountService $discountService
     * @return Collection
     */
    public static function load(array $params, DiscountService $discountService): Collection
    {
        $discounts = $discountService->discounts($params);
        $discounts = $discounts->map(function (DiscountDto $discount) use ($discountService) {
            $data = $discount->toArray();
            $data['statusName'] = $discount->statusDto() ? $discount->statusDto()->name : 'N/A';
            $data['validityPeriod'] = $discount->validityPeriod();
            return $data;
        });

        return $discounts;
    }

    /**
     * @param array $params
     * @param DiscountService $discountService
     * @return int
     */
    public static function count(array $params, DiscountService $discountService)
    {
        $discounts = $discountService->discountsCount($params);
        return $discounts['total'] ?? 0;
    }

    /**
     * @param Request $request
     * @return DiscountDto
     */
    public static function validate(Request $request)
    {
        $data = $request->validate([
            'name' => 'string|required',
            'type' => 'numeric|required',
            'value' => 'numeric|required',
            'value_type' => 'numeric|required',
            'start_date' => 'string|nullable',
            'end_date' => 'string|nullable',
            'promo_code_only' => 'boolean|required',
            'status' => 'numeric|required',
            'offers' => 'array',
            'bundles' => 'array',
            'brands' => 'array',
            'categories' => 'array',
            'except' => 'array',
            'conditions' => 'array',
        ]);

        $data['start_date'] = $data['start_date']
            ? Carbon::createFromFormat('Y-m-d', $data['start_date'])->format('Y-m-d')
            : null;

        $data['end_date'] = $data['end_date']
            ? Carbon::createFromFormat('Y-m-d', $data['end_date'])->format('Y-m-d')
            : null;

        $discount = new DiscountDto([
            'merchant_id' => null,
            'type' => $data['type'],
            'name' => $data['name'],
            'value_type' => $data['value_type'],
            'value' => $data['value'],
            'status' => $data['status'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
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
                    $conditions[] = $model->setUsers($condition['users']);
                    break;
                case DiscountConditionDto::ORDER_SEQUENCE_NUMBER:
                    $conditions[] = $model->setOrderSequenceNumber((int) $condition['sequenceNumber']);
                    break;
                case DiscountConditionDto::DISCOUNT_SYNERGY:
                    $conditions[] = $model->setSynergy($condition['synergy']);
                    break;
            }
        }

        if (!empty($data['bundles'])) {
            $conditions[] = (new DiscountConditionDto())->setBundles($data['bundles']);
        }

        return $conditions;
    }

    /**
     * @return array
     */
    static public function getOptionRoles()
    {
        return [
            ['value' => null, 'text' => 'Все'],
            ['value' => UserDto::SHOWCASE__PROFESSIONAL, 'text' => 'Профессионал'],
            ['value' => UserDto::SHOWCASE__REFERRAL_PARTNER, 'text' => 'Реферальный партнер'],
        ];
    }

    /**
     * @param Request $request
     * @param int $perPage
     * @return array
     */
    static public function getDefaultPager(Request $request, int $perPage = 20)
    {
        return [
            'page' =>  (int) $request->get('page', 1),
            'perPage' => $perPage,
        ];
    }
}
