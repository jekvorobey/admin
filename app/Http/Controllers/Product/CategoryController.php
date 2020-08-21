<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Pim\Dto\CategoryDto;
use Pim\Dto\PropertyDto;
use Pim\Services\CategoryService\CategoryService;

class CategoryController extends Controller
{
    public function index(
        CategoryService $categoryService
    ) {
        $this->title = 'Категории';

        return $this->render('Product/CategoryList', [
            'categories' =>  $this->loadCategories($categoryService),
        ]);
    }

    public function create(Request $request, CategoryService $categoryService)
    {
        $data = $request->validate([
            'name' => 'string',
            'parent_id' => 'integer|nullable',
            'active' => 'boolean',
        ]);

        $id = $categoryService->createCategory(new CategoryDto($data));
        return response()->json($id);
    }

    public function update(Request $request, CategoryService $categoryService)
    {
        $data = $request->validate([
            'id' => 'integer|required',
            'name' => 'string',
            'parent_id' => 'integer|nullable',
            'active' => 'boolean',
        ]);

        $categoryService->updateCategory($data['id'], new CategoryDto($data));
    }

    /**
     * @param CategoryService $categoryService
     * @return Collection|CategoryDto[]
     * @throws \Pim\Core\PimException
     */
    protected function loadCategories(CategoryService $categoryService)
    {
        $categories = $categoryService->categories((new RestQuery())
            ->include(CategoryService::PRODUCTS_COUNT)
            ->addFields(CategoryDto::entity(), 'id', 'name', 'code', 'parent_id', 'active'));

        return $categories->map(function (CategoryDto $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'parent_id' => $category->parent_id,
                'active' => $category->active,
                'productsCount' => $category->productsCount,
            ];
        });
    }

}