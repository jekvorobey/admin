<?php

namespace App\Core;

use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Marketing\Dto\Discount\DiscountApprovalStatus;
use Greensight\Marketing\Dto\Discount\DiscountBrand;
use Greensight\Marketing\Dto\Discount\DiscountCategory;
use Greensight\Marketing\Dto\Discount\DiscountCondition;
use Greensight\Marketing\Dto\Discount\DiscountDto;
use Greensight\Marketing\Dto\Discount\DiscountOffer;
use Greensight\Marketing\Dto\Discount\DiscountSegment;
use Greensight\Marketing\Dto\Discount\DiscountStatus;
use Greensight\Marketing\Dto\Discount\DiscountType;
use Greensight\Marketing\Dto\Discount\DiscountUserRole;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MerchantManagement\Services\MerchantService\MerchantService;

class DiscountHelper
{
    /**
     * @param Request $request
     * @param RestQuery $restQuery
     * @param DiscountService $discountService
     * @return Collection
     */
    public static function load(
        Request $request,
        RestQuery $restQuery,
        DiscountService $discountService
    ): Collection
    {
        $filters = $request->get('filter', []);

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'name':
                    $restQuery->setFilter('name', 'like', "%{$value}%");
                    break;

                case 'start_date':
                    $restQuery->setFilter('start_date', '>=', Carbon::parse($value));
                    break;

                case 'end_date':
                    $restQuery->setFilter('end_date', '<=', Carbon::parse($value));
                    break;

                default:
                    $restQuery->setFilter($key, $value);
            }
        }

        $discounts = $discountService->discounts($restQuery);

        $discounts = $discounts->map(function (DiscountDto $discount) use ($discountService) {
            $data = $discount->toArray();
            $data['approvalStatusName'] = $discount->approvalStatusDto()->name;
            $data['statusName'] = $discount->statusDto()->name;
            $data['validityPeriod'] = $discount->validityPeriod();

            return $data;
        });

        return $discounts;
    }

    /**
     * @param Request $request
     * @param MerchantService $merchantService
     * @return DiscountDto
     */
    public static function validate(Request $request, MerchantService $merchantService)
    {
        $data = $request->validate([
            'name' => 'string|required',
            'type' => 'numeric|required',
            'value' => 'numeric|required',
            'value_type' => 'numeric|required',
            'start_date' => 'string|nullable',
            'end_date' => 'string|nullable',
            'promo_code_only' => 'boolean|required',
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
            'sponsor' => DiscountDto::DISCOUNT_MERCHANT_SPONSOR,
            'merchant_id' => $merchantService->current()->id,
            'type' => $data['type'],
            'name' => $data['name'],
            'value_type' => $data['value_type'],
            'value' => $data['value'],
            'approval_status' => DiscountApprovalStatus::STATUS_SENT,
            'status' => DiscountStatus::STATUS_PAUSED,
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
        foreach ($conditions as $type => $condition) {
            $discount->addCondition($type, $condition);
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
            case DiscountType::TYPE_OFFER:
                foreach ($data['offers'] as $offer) {
                    $relations[DiscountDto::DISCOUNT_OFFER_RELATION][] = (new DiscountOffer())
                        ->setExcept(false)
                        ->setOffer($offer);
                }
                break;
            case DiscountType::TYPE_BRAND:
                foreach ($data['brands'] as $brand) {
                    $relations[DiscountDto::DISCOUNT_BRAND_RELATION][] = (new DiscountBrand())
                        ->setExcept(false)
                        ->setBrand($brand);
                }

                // Скидка на бренд за исключением следующих офферов
                if (isset($data['except']['offers'])) {
                    foreach ($data['except']['offers'] as $offer) {
                        $relations[DiscountDto::DISCOUNT_OFFER_RELATION][] = (new DiscountOffer())
                            ->setExcept(true)
                            ->setOffer($offer);
                    }
                }
                break;
            case DiscountType::TYPE_CATEGORY:
                foreach ($data['categories'] as $category) {
                    $relations[DiscountDto::DISCOUNT_CATEGORY_RELATION][] = (new DiscountCategory())
                        ->setExcept(false)
                        ->setCategory($category);
                }

                // Скидка на категорию за исключением следующих офферов
                if (isset($data['except']['offers'])) {
                    foreach ($data['except']['offers'] as $offer) {
                        $relations[DiscountDto::DISCOUNT_OFFER_RELATION][] = (new DiscountOffer())
                            ->setExcept(true)
                            ->setOffer($offer);
                    }
                }

                // Скидка на категорию за исключением следующих брендов
                if (isset($data['except']['brands'])) {
                    foreach ($data['except']['brands'] as $brand) {
                        $relations[DiscountDto::DISCOUNT_BRAND_RELATION][] = (new DiscountBrand())
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
            if ($condition['type'] !== DiscountCondition::CONDITION_TYPE_USER) {
                continue;
            }

            if (isset($condition['segments']) && count($condition['segments']) > 0) {
                foreach ($condition['segments'] as $segment) {
                    $relations[DiscountDto::DISCOUNT_SEGMENT_RELATION][] = (new DiscountSegment())
                        ->setExcept(false)
                        ->setSegment($segment);
                }
            }

            if (isset($condition['roles']) && count($condition['roles']) > 0) {
                foreach ($condition['roles'] as $role) {
                    $relations[DiscountDto::DISCOUNT_USER_ROLE_RELATION][] = (new DiscountUserRole())
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
            switch ($condition['type']) {
                case DiscountCondition::CONDITION_TYPE_FIRST_ORDER:
                    $conditions[$condition['type']] = null;
                    break;
                case DiscountCondition::CONDITION_TYPE_MIN_PRICE_ORDER:
                    $conditions[$condition['type']] = ['min_price' => (float)$condition['sum']];
                    break;
                case DiscountCondition::CONDITION_TYPE_MIN_PRICE_BRAND:
                    $conditions[$condition['type']] = [
                        'brands' => (int)$condition['brands'],
                        'min_price' => (float)$condition['sum'],
                    ];
                    break;
                case DiscountCondition::CONDITION_TYPE_MIN_PRICE_CATEGORY:
                    $conditions[$condition['type']] = [
                        'categories' => (int)$condition['categories'],
                        'min_price' => (float)$condition['sum'],
                    ];
                    break;
                case DiscountCondition::CONDITION_TYPE_EVERY_UNIT_PRODUCT:
                    $conditions[$condition['type']] = [
                        'offer' => (int)$condition['offer'],
                        'count' => (int)$condition['count'],
                    ];
                    break;
                case DiscountCondition::CONDITION_TYPE_DELIVERY_METHOD:
                    $conditions[$condition['type']] = [
                        'deliveryMethods' => $condition['deliveryMethods'],
                    ];
                    break;
                case DiscountCondition::CONDITION_TYPE_PAY_METHOD:
                    $conditions[$condition['type']] = [
                        'paymentMethods' => $condition['paymentMethods'],
                    ];
                    break;
                case DiscountCondition::CONDITION_TYPE_REGION:
                    $conditions[$condition['type']] = [
                        'regions' => $condition['regions'],
                    ];
                    break;
                case DiscountCondition::CONDITION_TYPE_USER:
                    $conditions[$condition['type']] = [
                        'users' => $condition['users'],
                    ];
                    break;
                case DiscountCondition::CONDITION_TYPE_ORDER_SEQUENCE_NUMBER:
                    $conditions[$condition['type']] = [
                        'sequence_number' => (int)$condition['sequenceNumber'],
                    ];
                    break;
                case DiscountCondition::CONDITION_TYPE_DISCOUNT_SYNERGY:
                    $conditions[$condition['type']] = [
                        'discounts' => $condition['synergy'],
                    ];
                    break;
            }
        }

        if (!empty($data['bundles'])) {
            $conditions[DiscountCondition::CONDITION_TYPE_BUNDLE] = ['bundles' => $data['bundles'],];
        }

        return $conditions;
    }
}
