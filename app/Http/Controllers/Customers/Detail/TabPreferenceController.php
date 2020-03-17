<?php

namespace App\Http\Controllers\Customers\Detail;


use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Pim\Services\SearchService\SearchService;

class TabPreferenceController extends Controller
{
    public function load(
        $id,
        BrandService $brandService,
        CategoryService $categoryService,
        CustomerService $customerService,
        ProductService $productService,
        Request $request
    )
    {
        $brands = $brandService->brands((new RestQuery())->addFields(BrandDto::entity(), 'id', 'name'));
        $categories = $categoryService->categories((new RestQuery())->addFields(CategoryDto::entity(), 'id', 'name', '_lft', '_rgt', 'parent_id'));
        /** @var CustomerDto $customer */
        $customer = $customerService->customers((new RestQuery())->setFilter('id', $id))->first();
        $favoriteItems = $customerService->favorites($id)->pluck('product_id')->toArray();
        $query = $this->makeFavoriteProductQuery($request, $favoriteItems);

        return response()->json([
            'brands' => $brands->keyBy('id'),
            'categories' => $categories->keyBy('id'),
            'customer' => [
                'brands' => $customer->brands,
                'categories' => $customer->categories,
            ],
            'favorites' => $favoriteItems ? $this->loadFavoriteItems($query, $productService) : null,
        ]);
    }

    protected function loadFavoriteItems(RestQuery $query, ProductService $productService)
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

    protected function makeFavoriteProductQuery(Request $request, $favoriteItems)
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);

        $query->include(BrandDto::entity(), CategoryDto::entity());
        $query->addFields(BrandDto::entity(), 'id', 'name');
        $query->addFields(CategoryDto::entity(), 'id', 'name');
        $query->addFields(ProductDto::entity(), 'id', 'name', 'vendor_code', 'approval_status', 'updated_at');

        $query->setFilter('id', $favoriteItems);
        return $query;
    }

    public function putBrands(int $id, CustomerService $customerService)
    {
        $this->validate(request(), [
            'brands' => 'array',
            'brands.*' => 'numeric',
        ]);

        $customerService->updateBrands($id, request('brands'));

        return response('', 204);
    }

    public function putCategories(int $id, CustomerService $customerService)
    {
        $this->validate(request(), [
            'categories' => 'array',
            'categories.*' => 'numeric',
        ]);

        $customerService->updateCategories($id, request('categories'));

        return response('', 204);
    }

    public function deleteFavoriteItem(CustomerService $customerService, $id, $product_id)
    {
        $customerService->deleteFromFavorites($id, $product_id);
        return response('', 204);
    }

    public function searchItem(
        Request $request,
        SearchService $searchService
    )
    {
        $query = $this->makeFavoriteProductQuery($request);
        $productSearchResult = $searchService->products($query);
        $data = [
            'products' => $productSearchResult->products,
            'total' => $productSearchResult->total
        ];
        return response()->json($data);
    }
}