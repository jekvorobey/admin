<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Cms\Dto\ProductGroupDto;
use Cms\Dto\ProductGroupTypeDto;
use Cms\Services\ProductGroupService\ProductGroupService;
use Cms\Services\ProductGroupTypeService\ProductGroupTypeService;
use Illuminate\Support\Collection;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductGroupDetailController extends Controller
{
    /**
     * @param int $id
     * @param ProductGroupService $productGroupService
     * @param ProductGroupTypeService $productGroupTypeService
     * @param CategoryService $categoryService
     * @param ProductService $productService
     * @return mixed
     */
    public function index(
        $id,
        ProductGroupService $productGroupService,
        ProductGroupTypeService $productGroupTypeService,
        CategoryService $categoryService,
        ProductService $productService
    ) {
        $productGroup = $this->getProductGroup($id, $productGroupService);
        $productGroupTypes = $this->getProductGroupTypes($productGroupTypeService);
        $categories = $this->getCategories($categoryService);
        $filters = $this->getFilters($productService);

        return $this->render('Content/ProductGroupDetail', [
            'iProductGroup' => $productGroup,
            'iProductGroupTypes' => $productGroupTypes,
            'iCategories' => $categories,
            'iFilters' => $filters,
            'options' => [],
        ]);
    }

    protected function getProductGroupTypes(ProductGroupTypeService $productGroupTypeService)
    {
        /** @var Collection|ProductGroupTypeDto[] $productGroupTypes */
        $productGroupTypes = $productGroupTypeService->productGroupTypes($productGroupTypeService->newQuery());

        if (!$productGroupTypes->count()) {
            throw new NotFoundHttpException('ProductGroupType not found');
        }

        return $productGroupTypes;
    }

    protected function getFilters(ProductService $productService)
    {
        /** @var Collection|ProductDto[] $filters */
        $filters = $productService->filters('', true);

        if (!$filters->count()) {
            throw new NotFoundHttpException('Product not found');
        }

        return $filters;
    }

    protected function getCategories(CategoryService $categoryService)
    {
        /** @var Collection|CategoryDto[] $productGroupTypes */
        $categories = $categoryService->categories($categoryService->newQuery());

        if (!$categories->count()) {
            throw new NotFoundHttpException('Category not found');
        }

        return $categories;
    }

    protected function getProductGroup(int $id, ProductGroupService $productGroupService)
    {
        /** @var Collection|ProductGroupDto[] $productGroups */
        $productGroups = $productGroupService
            ->newQuery()
            ->addFields('type', '*')
            ->addFields('productIds', '*')
            ->addFields('filters', '*')
            ->setFilter('id', $id)
            ->productGroups($productGroupService->newQuery());

        if (!$productGroups->count()) {
            throw new NotFoundHttpException('ProductGroup not found');
        }

        return $productGroups->first();
    }
}
