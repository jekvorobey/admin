<?php

namespace App\Http\Controllers\Content\Category;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Dto\CategoryDto;
use Pim\Services\CategoryService\CategoryService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CategoryListController extends Controller
{
    /**
     * @param CategoryService $categoryService
     * @return mixed
     * @throws \Pim\Core\PimException
     */
    public function index(
        CategoryService $categoryService
    ) {
        $this->title = 'Категории';

        return $this->render('Content/Category', [
            'categories' =>  $this->loadCategories($categoryService),
            'frequentMaxCount' => CategoryDto::FREQUENT_CATEGORY_MAX_COUNT,
        ]);
    }

    /**
     * @param Request $request
     * @param CategoryService $categoryService
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function editCategories(
        Request $request,
        CategoryService $categoryService
    ) {
        $data = $request->validate([
            'items' => 'array|required',
            'items.*.id' => 'integer|required',
            'items.*.frequent' => 'boolean|required',
            'items.*.position' => 'integer|required',
            'items.*.file_id' => 'integer|nullable',
            'selected' => 'array|required',
        ]);

        if (!$this->validateSelectedCount($data['selected'])) {
            throw new BadRequestHttpException('Неверные данные');
        }

        $editedCategories = collect();
        foreach ($data['items'] as $item) {
            $editedCategories->push(new CategoryDto($item));
        }
        $categoryService->editCategories($editedCategories);
        return response('', 204);
    }

    /**
     * @param CategoryService $categoryService
     * @return Collection|CategoryDto[]
     * @throws \Pim\Core\PimException
     */
    protected function loadCategories(CategoryService $categoryService)
    {
        $query = (new RestQuery())
            ->addFields(CategoryDto::entity(), 'id', 'name', 'code', 'frequent', 'position', 'file_id', 'parent_id');
        return $categoryService->categories($query);
    }

    /**
     * Валидация кол-ва категорий, выбранных для отображения на главной (должно быть не более 8)
     * @param $items
     * @return bool
     */
    protected function validateSelectedCount($items)
    {
        return (count($items) <= CategoryDto::FREQUENT_CATEGORY_MAX_COUNT);
    }
}