<?php

namespace App\Http\Controllers\Content\Category;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Cms\Dto\FrequentCategoryDto;
use Cms\Services\FrequentCategoryService\FrequentCategoryService;
use Pim\Core\PimException;
use Pim\Dto\CategoryDto;
use Pim\Services\CategoryService\CategoryService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FrequentCategoryController extends Controller
{
    /**
     * @return mixed
     * @throws CmsException
     * @throws PimException
     */
    public function index(
        CategoryService $categoryService,
        FrequentCategoryService $frequentCategoryService,
        FileService $fileService
    ) {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $this->title = 'Управление категориями';

        return $this->render('Content/Category', [
            'categories' => $this->loadCategories($categoryService, $frequentCategoryService, $fileService),
            'frequentMaxCount' => FrequentCategoryDto::FREQUENT_CATEGORY_MAX_COUNT,
        ]);
    }

    public function editCategories(
        Request $request,
        FrequentCategoryService $frequentCategoryService
    ): Response|ResponseFactory {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

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
            $editedCategories->push(new FrequentCategoryDto($item));
        }
        $frequentCategoryService->upsertCategories($editedCategories);

        return response('', 204);
    }

    /**
     * @return Collection|CategoryDto[]
     * @throws CmsException
     * @throws PimException
     */
    protected function loadCategories(
        CategoryService $categoryService,
        FrequentCategoryService $frequentCategoryService,
        FileService $fileService
    ): Collection {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $categories = $categoryService->categories((new RestQuery())
            ->addFields(CategoryDto::entity(), 'id', 'name', 'code', 'parent_id', 'active')
            ->include(CategoryService::PRODUCTS_COUNT));

        $frequentCategories = $frequentCategoryService->list(
            (new RestQuery())
                ->addFields(FrequentCategoryDto::entity(), 'category_id', 'frequent', 'position', 'file_id')
        )->keyBy('category_id');

        $frequentCategoriesWithImages = $this->injectFiles($fileService, $frequentCategories);

        return $categories->map(function (CategoryDto $category) use ($frequentCategoriesWithImages) {
            $id = $category->id;
            return [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'parent_id' => $category->parent_id,
                'active' => $category->active,
                'productsCount' => $category->productsCount,
                'frequent' => $frequentCategoriesWithImages->has($id) ? $frequentCategoriesWithImages->get($id)->frequent : false,
                'position' => $frequentCategoriesWithImages->has($id) ? $frequentCategoriesWithImages->get($id)->position : 0,
                'image' => $frequentCategoriesWithImages->has($id) ? $frequentCategoriesWithImages->get($id)['file'] : null,
            ];
        });
    }

    protected function injectFiles(FileService $fileService, Collection $frequentCategories): Collection
    {
        $fileIds = $frequentCategories->pluck('file_id')->all();
        /** @var Collection|FileDto[] $files */
        $files = $fileService->getFiles($fileIds)->keyBy('id');

        return $frequentCategories->map(function (FrequentCategoryDto $frequentCategory) use ($files) {
            $frequentCategory['file'] = $files->has($frequentCategory->file_id) ? [
                'id' => $files->get($frequentCategory->file_id)->id,
                'url' => $files->get($frequentCategory->file_id)->absoluteUrl(),
            ] : null;
            return $frequentCategory;
        });
    }

    /**
     * Валидация кол-ва категорий, выбранных для отображения на главной (должно быть не более 8)
     */
    protected function validateSelectedCount($items): bool
    {
        return count($items) <= FrequentCategoryDto::FREQUENT_CATEGORY_MAX_COUNT;
    }
}
