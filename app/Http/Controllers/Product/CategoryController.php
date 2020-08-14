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
        $this->loadPropertyTypes = true;

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
            'props' => 'array',
        ]);

        $categoryData = [
            'name' => $data['name'],
            'parent_id' => $data['parent_id'],
            'active' => $data['active']
        ];
        $id = $categoryService->createCategory(new CategoryDto($categoryData));

        $this->setProperties($id, $data['props'], $categoryService);

        return response()->json($id);
    }

    public function update(Request $request, CategoryService $categoryService)
    {
        $data = $request->validate([
            'id' => 'integer|required',
            'name' => 'string',
            'parent_id' => 'integer|nullable',
            'active' => 'boolean',
            'prop' => 'array',
        ]);

        $categoryService->updateCategory($data['id'], new CategoryDto($data));
    }

    protected function setProperties(int $id, Array $props, CategoryService $categoryService)
    {

    }

    /**
     * @param CategoryService $categoryService
     * @return Collection|CategoryDto[]
     * @throws \Pim\Core\PimException
     */
    protected function loadCategories(CategoryService $categoryService)
    {
        $categories = $categoryService->categories((new RestQuery())
            ->include('properties')
            ->addFields(CategoryDto::entity(), 'id', 'name', 'code', 'parent_id', 'active'));

//        dd($categories);

        return $categories->map(function (CategoryDto $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'parent_id' => $category->parent_id,
                'active' => $category->active,
                'props' => $category->properties->all(),
            ];
        });
    }

}