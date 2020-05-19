<?php

namespace App\Http\Controllers\Marketing;

use App\Core\DiscountHelper;
use App\Http\Controllers\Controller;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
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
            'deliveryMethods' => $data['deliveryMethods'],
            'discountStatuses' => $data['discountStatuses'],
            'paymentMethods' => $data['paymentMethods'],
            'roles' => $data['roles'],
            'iDistricts' => $data['districts'],
            'categories' => $categoryService->categories($categoryService->newQuery()),
            'brands' => $brandService->brands($brandService->newQuery()),
        ]);
    }
}