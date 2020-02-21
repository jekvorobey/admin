<?php

namespace App\Http\Controllers\Content\ProductGroup;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\ProductGroupDto;
use Cms\Dto\ProductGroupTypeDto;
use Cms\Services\ProductGroupService\ProductGroupService;
use Cms\Services\ProductGroupTypeService\ProductGroupTypeService;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Core\PimException;
use Pim\Dto\CategoryDto;
use Pim\Services\CategoryService\CategoryService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductGroupDetailController extends Controller
{
    public function updatePage(
        $id,
        ProductGroupService $productGroupService,
        ProductGroupTypeService $productGroupTypeService,
        CategoryService $categoryService,
        FileService $fileService
    ) {
        $productGroup = $this->getProductGroup($id, $productGroupService);
        $productGroupImages = $this->getProductGroupImages([$productGroup['preview_photo_id']], $fileService);
        $productGroupTypes = $this->getProductGroupTypes($productGroupTypeService);
        $categories = $this->getCategories($categoryService);

        return $this->render('Content/ProductGroupDetail', [
            'iProductGroup' => $productGroup,
            'iProductGroupTypes' => $productGroupTypes,
            'iProductGroupImages' => $productGroupImages,
            'iCategories' => $categories,
            'options' => [],
        ]);
    }

    public function createPage(
        ProductGroupService $productGroupService,
        ProductGroupTypeService $productGroupTypeService,
        CategoryService $categoryService,
        FileService $fileService
    ) {
        $productGroupTypes = $this->getProductGroupTypes($productGroupTypeService);
        $categories = $this->getCategories($categoryService);

        return $this->render('Content/ProductGroupDetail', [
            'iProductGroup' => [],
            'iProductGroupTypes' => $productGroupTypes,
            'iProductGroupImages' => [],
            'iCategories' => $categories,
            'options' => [],
        ]);
    }

    public function create(Request $request, ProductGroupService $productGroupService)
    {
        $validatedData = $request->validate([
            'name' => 'string|required',
            'code' => 'string|required',
            'active' => 'boolean|required',
            'is_shown' => 'boolean|required',
            'type_id' => 'integer|required',
            'preview_photo_id' => 'integer|required',
            'category_code' => 'string|nullable',
            'filters' => 'array',
            'products' => 'array',
        ]);

        $productGroupService->createProductGroup(new ProductGroupDto($validatedData));

        return response()->json([], 204);
    }

    public function update(int $id, Request $request, ProductGroupService $productGroupService)
    {
        $validatedData = $request->validate([
            'id' => 'integer|required',
            'name' => 'string|required',
            'code' => 'string|required',
            'active' => 'boolean|required',
            'is_shown' => 'boolean|required',
            'type_id' => 'integer|required',
            'preview_photo_id' => 'integer|required',
            'category_code' => 'string|nullable',
            'filters' => 'array',
            'products' => 'array',
        ]);

        $validatedData['id'] = $id;

        $productGroupService->updateProductGroup($validatedData['id'], new ProductGroupDto($validatedData));

        return response()->json([], 204);
    }

    public function delete(int $id, ProductGroupService $productGroupService)
    {
        $productGroupService->deleteProductGroup($id);

        return response()->json([], 204);
    }

    /**
     * @param ProductGroupTypeService $productGroupTypeService
     * @return ProductGroupTypeDto[]|Collection
     * @throws CmsException
     */
    protected function getProductGroupTypes(ProductGroupTypeService $productGroupTypeService)
    {
        return $productGroupTypeService->productGroupTypes($productGroupTypeService->newQuery());
    }

    /**
     * @param CategoryService $categoryService
     * @return Collection|CategoryDto[]
     * @throws PimException
     */
    protected function getCategories(CategoryService $categoryService)
    {
        return $categoryService->categories($categoryService->newQuery());
    }

    /**
     * @param int $id
     * @param ProductGroupService $productGroupService
     * @return ProductGroupDto
     */
    protected function getProductGroup(int $id, ProductGroupService $productGroupService)
    {
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

    /**
     * @param array $ids
     * @param FileService $fileService
     * @return Collection|FileDto[]
     */
    protected function getProductGroupImages(array $ids, FileService $fileService)
    {
        return $fileService
            ->getFiles($ids)
            ->transform(function ($file) {
                /** @var FileDto $file */
                $file['url'] = $file->absoluteUrl();

                return $file;
            })
            ->keyBy('id');
    }
}
