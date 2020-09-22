<?php

namespace App\Http\Controllers\Content\ProductGroup;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\ProductGroupDto;
use Cms\Dto\ProductGroupFilterDto;
use Cms\Dto\ProductGroupTypeDto;
use Cms\Services\ProductGroupService\ProductGroupService;
use Cms\Services\ProductGroupTypeService\ProductGroupTypeService;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Core\PimException;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Pim\Services\PropertyDirectoryValueService\PropertyDirectoryValueService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductGroupDetailController extends Controller
{
    public function updatePage(
        $id,
        ProductGroupService $productGroupService,
        ProductGroupTypeService $productGroupTypeService,
        CategoryService $categoryService,
        FileService $fileService,
        PropertyDirectoryValueService $propertyDirectoryValueService,
        BrandService $brandService
    ) {
        $productGroup = $this->getProductGroup($id, $productGroupService, $propertyDirectoryValueService, $brandService);
        $productGroupImages = $this->getProductGroupImages([$productGroup['preview_photo_id']], $fileService);
        $productGroupTypes = $this->getProductGroupTypes($productGroupTypeService);
        $categories = $this->getCategories($categoryService);

        return $this->render('Content/ProductGroupDetail', [
            'iProductGroup' => $productGroup,
            'iProductGroupTypes' => $productGroupTypes,
            'iProductGroupImages' => $productGroupImages,
            'iCategories' => $categories,
            'options' => [
                'typesBasedOnCategory' => ProductGroupTypeDto::TYPES_BASED_ON_CATEGORY,
            ],
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
            'options' => [
                'typesBasedOnCategory' => ProductGroupTypeDto::TYPES_BASED_ON_CATEGORY,
            ],
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
            'category_code' => 'array|nullable',
            'category_code.*' => 'string|required',
            'banner_id' => 'integer|nullable',
            'filters' => 'array',
            'products' => 'array',
        ]);

        if (isset($validatedData['category_code'])) {
            foreach ($validatedData['category_code'] as $categoryCode) {
                $validatedData['filters'][] = [
                    'code' => ProductGroupFilterDto::CATEGORY_FILTER,
                    'value' => $categoryCode
                ];
            }
        }

        $productGroupService->createProductGroup(new ProductGroupDto($validatedData));

        return response()->json([], 204);
    }

    public function update(int $id, Request $request, ProductGroupService $productGroupService)
    {
        $validatedData = $request->validate([
            'id' => 'integer|required',
            'name' => 'string|required',
            'code' => 'string|required',
            'active' => 'boolean',
            'is_shown' => 'boolean',
            'type_id' => 'integer|required',
            'preview_photo_id' => 'integer|required',
            'category_code' => 'array|nullable',
            'category_code.*' => 'string|required',
            'banner_id' => 'integer|nullable',
            'filters' => 'array',
            'products' => 'array',
        ]);

        $validatedData['id'] = $id;
        if (isset($validatedData['category_code'])) {
            foreach ($validatedData['category_code'] as $categoryCode) {
                $validatedData['filters'][] = [
                    'code' => ProductGroupFilterDto::CATEGORY_FILTER,
                    'value' => $categoryCode
                ];
            }
        }

        $productGroupService->updateProductGroup($validatedData['id'], new ProductGroupDto($validatedData));

        return response()->json([], 204);
    }

    public function delete(int $id, ProductGroupService $productGroupService)
    {
        $productGroupService->deleteProductGroup($id);

        return response()->json([], 204);
    }

    public function getFilters(
        BrandService $brandService,
        ProductService $productService
    ) {
        $brandsFilter = $brandService->filters();
        $appliedFilters = [
            'brand' => $brandsFilter->pluck('code')
        ];
        $excludedFilters = ['brand'];
        $filters = $productService->filters($appliedFilters, $excludedFilters)->all();

        $result = array_merge($brandsFilter->all(), $filters);
        return response()->json($result);
    }

    public function getFiltersByCategory(
        Request $request,
        ProductService $productService
    ) {
        $appliedFilters = [
            'category' => $request->get('category', ''),
        ];
        $filters = $productService->filters($appliedFilters, []);

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
        return $categoryService->categories($categoryService->newQuery()
            ->include('ancestors')
            ->addSort('_lft', 'asc')
        );
    }

    /**
     * @param int $id
     * @param ProductGroupService $productGroupService
     * @param PropertyDirectoryValueService $propertyDirectoryValueService
     * @param BrandService $brandService
     * @return mixed
     * @throws PimException
     */
    protected function getProductGroup(
        int $id,
        ProductGroupService $productGroupService,
        PropertyDirectoryValueService $propertyDirectoryValueService,
        BrandService $brandService
    )
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

        $productGroup = $productGroups->first();
        $categoryFilters = [];
        $brandFilters = [];
        $otherFilters = [];
        foreach ($productGroup->filters as $index => $filter) {
            switch ($filter['code']) {
                case ProductGroupFilterDto::CATEGORY_FILTER:
                    $categoryFilters[] = $filter['value'];
                    break;
                case ProductGroupFilterDto::BRAND_FILTER:
                    $brandFilters[] = $filter;
                    break;
                default:
                    $otherFilters[] = $filter;
            }
        }
        $productGroup->category_code = $categoryFilters;

        $filterValues = collect($otherFilters)->pluck('value')->all();
        $directoryValues = $propertyDirectoryValueService->values((new RestQuery())
            ->setFilter('code', 'in', $filterValues)
        )->keyBy('code');
        $this->injectNames($otherFilters, $directoryValues);

        $brandCodes = collect($brandFilters)->pluck('value')->all();
        $brands = $brandService->brands((new RestQuery())
            ->setFilter('code', 'in', $brandCodes)
        )->keyBy('code');
        $this->injectNames($brandFilters, $brands);

        $productGroup->filters = array_merge($brandFilters, $otherFilters);
        return $productGroup;
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

    /**
     * Дополнить объекты фильтров их названиями
     * @param array $target
     * @param Collection $source
     */
    protected function injectNames(array &$target, Collection $source)
    {
        $target = array_map(function ($item) use ($source) {
            $item['name'] = $source->has($item['value'])
                ? $source->get($item['value'])->name
                : null;
            return $item;
        }, $target);
    }
}
