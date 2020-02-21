<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;

class ProductListController extends Controller
{
    public function index(
        Request $request,
        ProductService $productService,
        CategoryService $categoryService,
        BrandService $brandService
    )
    {
        $this->title = 'Товары';
        $query = $this->makeQuery($request);
        return $this->render('Product/ProductList', [
            'iProducts' => $this->loadItems($query, $productService),
            'iPager' => $productService->productsCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'brands' => $brandService->brands($brandService->newQuery()),
                'categories' => $categoryService->categories($categoryService->newQuery())
            ]
        ]);
    }
    
    public function page(
        Request $request,
        ProductService $productService
    )
    {
        $query = $this->makeQuery($request);
        $data = [
            'products' => $this->loadItems($query, $productService),
        ];
        if (1 == $request->get('page', 1)) {
            $data['pager'] = $productService->productsCount($query);
        }
        return response()->json($data);
    }
    
    protected function makeQuery(Request $request)
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);
    
        $query->include(BrandDto::entity(), CategoryDto::entity());
        $query->addFields(BrandDto::entity(), 'id', 'name');
        $query->addFields(CategoryDto::entity(), 'id', 'name');
        $query->addFields(ProductDto::entity(), 'id', 'name', 'vendor_code', 'approval_status', 'updated_at');
        
        $filter = $request->get('filter', []);
    
        if (isset($filter['id']) && $filter['id']) {
            $query->setFilter('id', $filter['id']);
        }
        if (isset($filter['vendorCode']) && $filter['vendorCode']) {
            $query->setFilter('vendor_code', $filter['vendorCode']);
        }
    
        if (isset($filter['category'])) {
            $query->setFilter('category_id', $filter['category']);
        }
    
        if (isset($filter['brand'])) {
            $query->setFilter('brand_id', $filter['brand']);
        }
        
        return $query;
    }
    
    protected function loadItems(
        RestQuery $query,
        ProductService $productService
    )
    {
        /** @var Collection $products */
        $products = $productService->products($query);
        $productIds = $products->pluck('id')->all();
        $images = collect();
        if ($productIds) {
            $images = $productService->allImages($productIds, 1)->pluck('url', 'productId');
        }
        $products = $products->map(function (ProductDto $product) use ($images) {
            $data = $product->toArray();
            $data['approvalStatusName'] = $product->approvalStatus()->name;
            $data['updated_at'] = (new Carbon($product->updated_at))->toISOString();
            $data['photo'] = $images[$product->id] ?? '';
        
            return $data;
        });
        return $products;
    }
}