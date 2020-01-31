<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Cms\Dto\ProductGroupDto;
use Cms\Dto\ProductGroupTypeDto;
use Cms\Services\ProductGroupService\ProductGroupService;
use Cms\Services\ProductGroupTypeService\ProductGroupTypeService;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductGroupDetailController extends Controller
{
    /**
     * @param int $id
     * @param ProductGroupService $productGroupService
     * @param ProductGroupTypeService $productGroupTypeService
     * @param CategoryService $categoryService
     * @param FileService $fileService
     * @return mixed
     */
    public function index(
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

    public function getProducts(
        Request $request,
        ProductService $productService
    ) {
        $validatedData = $request->validate([
            'id' => 'array',
            'vendor_code' => 'string',
        ]);

        $query = $productService->newQuery();

        $validatedData = array_filter($validatedData);
        if (!$validatedData) {
            throw new HttpException('500');
        }

        foreach ($validatedData as $keyParam => $valueParam) {
            $query->setFilter($keyParam, $valueParam);
        }

        $products = $productService->products($query);

        $images = collect();
        if ($products) {
            $productIds = $products->pluck('id')->all();
            $images = $productService->allImages($productIds, 1)->pluck('url', 'productId');
        }
        $products = $products->map(function (ProductDto $product) use ($images) {
            $data = $product->toArray();
            $data['photo'] = $images[$product->id] ?? '';

            return $data;
        });

        return response()->json($products);
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
            'preview_photo_id' => 'integer|required',
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

        return $productGroupTypes;
    }

    protected function getCategories(CategoryService $categoryService)
    {
        /** @var Collection|CategoryDto[] $productGroupTypes */
        $categories = $categoryService->categories($categoryService->newQuery());

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
