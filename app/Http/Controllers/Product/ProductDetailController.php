<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Pim\Dto\Product\ProductApprovalStatus;
use Pim\Dto\Product\ProductDto;
use Pim\Dto\PropertyDirectoryValueDto;
use Pim\Dto\PropertyDto;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Pim\Services\PropertyDirectoryValueService\PropertyDirectoryValueService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductDetailController extends Controller
{
    /**
     * @param int $id
     * @param ProductService $productService
     * @param CategoryService $categoryService
     * @param BrandService $brandService
     *
     * @return mixed
     */
    public function index(
        $id,
        ProductService $productService,
        CategoryService $categoryService,
        BrandService $brandService
    )
    {
        [$product, $images, $props, $availableProps, $directoryValues] = $this->getProductData($id, $productService);
        
        $approvalStatuses = collect(ProductApprovalStatus::allStatuses())->pluck('name', 'id')->all();
        $brands = $brandService->newQuery()->prepare($brandService)->brands();
        $categories = $categoryService->newQuery()->prepare($categoryService)->categories();
        
        return $this->render('Product/ProductDetail', [
            'iProduct' => $product,
            'iImages' => $images,
            'iProperties' => $props,
            'options' => [
                'availableProperties' => $availableProps,
                'directoryValues' => $directoryValues,
                
                'approval' => $approvalStatuses,
                'brands' => $brands,
                'categories' => $categories,
                'segments' => ['A', 'B', 'C']
            ]
        ]);
    }
    
    public function detailData(int $id, ProductService $productService)
    {
        [$product, $images, $props, $availableProps, $directoryValues] = $this->getProductData($id, $productService);
        return response()->json([
            'product' => $product,
            'images' => $images,
            'properties' => $props,
            'availableProperties' => $availableProps,
            'directoryValues' => $directoryValues,
        ]);
    }
    
    public function saveProduct(
        int $id,
        Request $request,
        ProductService $productService
    )
    {
        $data = $this->validate($request, [
            'name' => 'string',
            'brand_id' => 'integer',
            'category_id' => 'integer',
            'segment' => 'string',
            'approval_status' => 'integer',
            'vendor_code' => 'string',
            'width' => 'integer',
            'height' => 'integer',
            'length' => 'integer',
            'weight' => 'integer',
        ]);
        $product = new ProductDto($data);
        
        $productService->updateProduct($id, $product);
        
        return response()->json();
    }
    
    public function saveProps(
        int $id,
        Request $request,
        ProductService $productService
    )
    {
        $data = $this->validate($request, [
           'props' => 'required|array'
        ]);
        $productService->saveProperties($id, $data['props']);
        return response()->json();
    }
    
    public function saveImage(int $id, Request $request, ProductService $productService)
    {
        $data = $this->validate($request, [
            'id' => 'required|integer',
            'type' => 'required|integer'
        ]);
        $productService->addImage($id, $data['id'], $data['type']);
        return response()->json();
    }
    
    public function deleteImage(int $id, Request $request, ProductService $productService)
    {
        $data = $this->validate($request, [
            'id' => 'required|integer',
            'type' => 'required|integer'
        ]);
        $productService->deleteImage($id, $data['id'], $data['type']);
        return response()->json();
    }

    /**
     * Изменить статус согласования товара
     * @param  int  $id
     * @param  Request  $request
     * @param  ProductService  $productService
     * @return JsonResponse
     * @throws \Pim\Core\PimException
     */
    public function changeApproveStatus(int $id, Request $request, ProductService $productService): JsonResponse
    {
        $data = $this->validate($request, [
            'approval_status' => Rule::in(array_keys(ProductApprovalStatus::allStatuses())),
        ]);
        $product = new ProductDto($data);
        $productService->updateProduct($id, $product);
        return response()->json();
    }

    /**
     * Изменить статус согласования товара на "Отклонен" с комментарием
     * @param  int  $id
     * @param  Request  $request
     * @param  ProductService  $productService
     * @return JsonResponse
     * @throws \Pim\Core\PimException
     */
    public function reject(int $id, Request $request, ProductService $productService): JsonResponse
    {
        $data = $this->validate($request, [
            'approval_status_comment' => 'required|string',
        ]);
        $product = new ProductDto($data);
        $product->approval_status = ProductApprovalStatus::STATUS_REJECT;
        $productService->updateProduct($id, $product);
        return response()->json();
    }
    
    protected function getProductData(int $id, ProductService $productService)
    {
        /** @var Collection|ProductDto[] $products */
        $products = $productService
            ->newQuery()
            ->include('tips')
            ->setFilter('id', $id)
            ->include('properties')
            ->products();
        if (!$products->count()) {
            throw new NotFoundHttpException();
        }
        
        /** @var ProductDto $product */
        $product = $products->first();
        $images = $productService->images($product->id);
        
        [$props, $availableProps, $directoryValues] = $this->properties($product);
        return [
            $product,
            $images,
            $props,
            $availableProps,
            $directoryValues
        ];
    }
    
    protected function properties(ProductDto $product)
    {
        $categoryService = resolve(CategoryService::class);
        $directoryValueService = resolve(PropertyDirectoryValueService::class);
        
        $propertiesQuery = $categoryService->newQuery()->addFields('id', 'name', 'display_name', 'type', 'is_multiple');
        $availableProperties = $categoryService
            ->categoryProperties($product->category_id, $propertiesQuery);
        $directoryPropertyIds = $availableProperties
            ->filter(function (PropertyDto $property) {
                return $property->type == PropertyDto::TYPE_DIRECTORY;
            })
            ->pluck('id')->toArray();
        
        $directoryValuesQuery = $directoryValueService
            ->newQuery()
            ->setFilter('property_id', $directoryPropertyIds)
            ->addFields(PropertyDirectoryValueDto::entity(), 'id', 'name', 'property_id');
        
        $directoryValues = $directoryValueService->values($directoryValuesQuery)->groupBy('property_id');
        $properties = [];
        foreach ($product->properties as $property) {
            if (!isset($properties[$property->property_id])) {
                $properties[$property->property_id] = [];
            }
            $properties[$property->property_id][] = $property->value;
        }
        return [
            $properties,
            $availableProperties,
            $directoryValues,
        ];
    }
}