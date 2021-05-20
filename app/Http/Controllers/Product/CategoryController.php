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
            'categories' => $this->loadCategories($categoryService),
        ]);
    }

    public function create(Request $request, CategoryService $categoryService)
    {
        $data = $request->validate([
            'name' => 'string',
            'code' => 'string|nullable',
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
            'name' => 'string|required',
            'code' => 'string|required',
            'parent_id' => 'integer|nullable',
            'active' => 'boolean',
        ]);

        $categoryService->updateCategory($data['id'], new CategoryDto($data));
    }

    /**
     * @return Collection|CategoryDto[]
     * @throws \Pim\Core\PimException
     */
    protected function loadCategories(CategoryService $categoryService)
    {
        $categories = $categoryService->categories((new RestQuery())
            ->include('descendants', 'ancestors', 'products', 'properties')
            ->addFields(CategoryDto::entity(), 'id', 'name', 'code', 'parent_id', 'active')
            ->addFields('products', 'id', 'category_id'));

        return $categories->map(function (CategoryDto $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'parent_id' => $category->parent_id,
                'active' => $category->active,
                'productsCount' => $category->products->count(),
                'descendants' => $category->descendants()->pluck('id')->all(),
                'ancestors' => $category->ancestors()->pluck('id')->all(),
                'properties' => $category->properties->map(function (PropertyDto $prop) {
                    return [
                        'id' => $prop->id,
                        'name' => $prop->name,
                    ];
                })->all(),
            ];
        });
    }
}
