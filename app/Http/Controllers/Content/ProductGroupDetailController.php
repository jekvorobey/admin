<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Cms\Dto\ProductGroupDto;
use Cms\Dto\ProductGroupTypeDto;
use Cms\Services\ProductGroupService\ProductGroupService;
use Cms\Services\ProductGroupTypeService\ProductGroupTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Dto\CategoryDto;
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
        CategoryService $categoryService
    ) {
        $productGroup = $this->getProductGroup($id, $productGroupService);
        $productGroupTypes = $this->getProductGroupTypes($productGroupTypeService);
        $categories = $this->getCategories($categoryService);

        return $this->render('Content/ProductGroupDetail', [
            'iProductGroup' => $productGroup,
            'iProductGroupTypes' => $productGroupTypes,
            'iCategories' => $categories,
            'options' => [],
        ]);
    }

    public function getFilters(
        Request $request,
        ProductService $productService
    ) {
        $categoryCode = $request->get('category', '');
        $filters = $productService->filters($categoryCode, true);

        if (!$filters->count()) {
            throw new NotFoundHttpException('FilterItem not found');
        }

        return response()->json($filters);
    }

    public function update($id, Request $request, ProductGroupService $productGroupService)
    {
        $validatedData = $request->validate([
            'id' => 'integer|required',
            'name' => 'string|required',
            'code' => 'string|required',
            'active' => 'boolean|required',
            'added_in_menu' => 'boolean|required',
            'type_id' => 'integer|required',
            'category_code' => 'string|nullable',
            'filters' => 'array',
            'products' => 'array',
        ]);

        $validatedData['id'] = $id;

        $productGroupService->updateProductGroup($validatedData['id'], new ProductGroupDto($validatedData));

        return response()->json([], 204);
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
            ->addFields('products', '*')
            ->addFields('filters', '*')
            ->setFilter('id', $id)
            ->productGroups($productGroupService->newQuery());

        if (!$productGroups->count()) {
            throw new NotFoundHttpException('ProductGroup not found');
        }

        return $productGroups->first();
    }
}
