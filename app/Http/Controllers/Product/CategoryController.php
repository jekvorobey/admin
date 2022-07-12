<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Core\PimException;
use Pim\Dto\CategoryDto;
use Pim\Dto\PropertyDto;
use Pim\Services\CategoryService\CategoryService;

class CategoryController extends Controller
{
    /**
     * @throws PimException
     */
    public function index(CategoryService $categoryService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $this->title = 'Категории';

        return $this->render('Product/CategoryList', [
            'categories' => $this->loadCategories($categoryService),
        ]);
    }

    public function create(Request $request, CategoryService $categoryService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $request->validate([
            'name' => 'string',
            'code' => 'string|nullable',
            'parent_id' => 'integer|nullable',
            'active' => 'boolean',
            'meta_title' => 'string|nullable',
            'meta_description' => 'string|nullable',
        ]);

        $id = $categoryService->createCategory(new CategoryDto($data));
        return response()->json($id);
    }

    public function update(Request $request, CategoryService $categoryService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $request->validate([
            'id' => 'integer|required',
            'name' => 'string|required',
            'code' => 'string|required',
            'parent_id' => 'integer|nullable',
            'active' => 'boolean',
            'meta_title' => 'string|nullable',
            'meta_description' => 'string|nullable',
        ]);

        $categoryService->updateCategory($data['id'], new CategoryDto($data));
    }

    /**
     * @return Collection|CategoryDto[]
     * @throws PimException
     */
    protected function loadCategories(CategoryService $categoryService): array|Collection
    {
        $categories = $categoryService->categories((new RestQuery())
            ->include('descendants', 'ancestors', 'products', 'properties')
            ->addFields(CategoryDto::entity(), 'id', 'name', 'code', 'parent_id', 'active', 'meta_title', 'meta_description')
            ->addFields('products', 'id', 'category_id'));

        return $categories->map(function (CategoryDto $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'parent_id' => $category->parent_id,
                'active' => $category->active,
                'metaTitle' => $category->meta_title,
                'metaDescription' => $category->meta_description,
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
