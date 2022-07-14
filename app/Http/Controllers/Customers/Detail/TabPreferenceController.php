<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Services\FavoriteService\FavoriteService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Pim\Core\PimException;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Pim\Services\SearchService\SearchService;

class TabPreferenceController extends Controller
{
    /**
     * @throws PimException
     */
    public function load(
        $id,
        BrandService $brandService,
        CategoryService $categoryService,
        CustomerService $customerService,
        FavoriteService $favoriteService,
        ProductService $productService,
        Request $request
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $brands = $brandService->brands((new RestQuery())->addFields(BrandDto::entity(), 'id', 'name'));
        $categories = $categoryService->categories((new RestQuery())->addFields(CategoryDto::entity(), 'id', 'name', '_lft', '_rgt', 'parent_id'));
        /** @var CustomerDto $customer */
        $customer = $customerService->customers((new RestQuery())->setFilter('id', $id))->first();
        $favoriteItems = $favoriteService->favorites($id)->pluck('product_id')->toArray();
        $query = $this->makeFavoriteProductQuery($request, $favoriteItems);

        return response()->json([
            'brands' => $brands->keyBy('id'),
            'categories' => $categories->keyBy('id'),
            'pref_personal' => [
                'brands' => $customer->own_brands,
                'categories' => $customer->own_categories,
            ],
            'pref_referral' => [
                'brands' => $customer->ref_brands,
                'categories' => $customer->ref_categories,
            ],
            'favorites' => $favoriteItems ? $this->loadFavoriteItems($query, $productService) : null,
        ]);
    }

    /**
     * @throws PimException
     */
    protected function loadFavoriteItems(RestQuery $query, ProductService $productService): Collection
    {
        /** @var Collection $products */
        $products = $productService->products($query);
        $productIds = $products->pluck('id')->all();
        $images = collect();
        if ($productIds) {
            $images = $productService->allImages($productIds, 1)->pluck('url', 'productId');
        }

        return $products->map(function (ProductDto $product) use ($images) {
            $data = $product->toArray();
            $data['approvalStatusName'] = $product->approvalStatus()->name;
            $data['updated_at'] = (new Carbon($product->updated_at))->toISOString();
            $data['photo'] = $images[$product->id] ?? '';

            return $data;
        });
    }

    protected function makeFavoriteProductQuery(Request $request, $favoriteItems): RestQuery
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 12);

        $query->include(BrandDto::entity(), CategoryDto::entity());
        $query->addFields(BrandDto::entity(), 'id', 'name');
        $query->addFields(CategoryDto::entity(), 'id', 'name');
        $query->addFields(ProductDto::entity(), 'id', 'name', 'vendor_code', 'approval_status', 'updated_at');

        $query->setFilter('id', $favoriteItems);
        return $query;
    }

    /**
     * @return ResponseFactory|Response
     */
    public function putBrands(int $id, int $prefType, CustomerService $customerService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $data = $this->validate(request(), [
            'brands' => 'array',
            'brands.*' => 'integer',
        ]);

        $customerService->updateBrands($id, $prefType, $data['brands']);

        return response('', 204);
    }

    /**
     * @return ResponseFactory|Response
     */
    public function putCategories(int $id, int $prefType, CustomerService $customerService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $data = $this->validate(request(), [
            'categories' => 'array',
            'categories.*' => 'integer',
        ]);

        $customerService->updateCategories($id, $prefType, $data['categories']);

        return response('', 204);
    }

    public function addFavoriteItem(FavoriteService $favoriteService, $id, $product_id)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $favoriteService->addToFavorites($id, $product_id);

        return response('', 204);
    }

    public function deleteFavoriteItem(FavoriteService $favoriteService, $id, $product_id)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $favoriteService->deleteFromFavorites($id, $product_id);

        return response('', 204);
    }

    public function searchItem(Request $request, SearchService $searchService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $query = $this->makeFavoriteProductQuery($request);
        $productSearchResult = $searchService->products($query);
        $data = [
            'products' => $productSearchResult->products,
            'total' => $productSearchResult->total,
        ];

        return response()->json($data);
    }
}
