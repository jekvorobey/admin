<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use Pim\Dto\CategoryDto;
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

    protected function loadCategories(CategoryService $categoryService)
    {
        $categories = $categoryService->categories((new RestQuery())
            ->addFields(CategoryDto::entity(), 'id', 'name', 'code', 'parent_id'));

//        $frequentCategories = $frequentCategoryService->list((new RestQuery())
//            ->addFields(FrequentCategoryDto::entity(), 'category_id', 'frequent', 'position', 'file_id')
//        )->keyBy('category_id');
//
//        $frequentCategoriesWithImages = $this->injectFiles($fileService, $frequentCategories);

        return $categories->map(function (CategoryDto $category) {
            $id = $category->id;
            return [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'parent_id' => $category->parent_id,
//                'frequent' => $frequentCategoriesWithImages->has($id) ? ($frequentCategoriesWithImages->get($id))->frequent : false,
//                'position' => $frequentCategoriesWithImages->has($id) ? ($frequentCategoriesWithImages->get($id))->position : 0,
//                'image' => $frequentCategoriesWithImages->has($id) ? $frequentCategoriesWithImages->get($id)['file'] : null,
            ];
        });
    }


}