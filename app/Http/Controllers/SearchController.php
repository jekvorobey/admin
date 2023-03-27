<?php

namespace App\Http\Controllers;

use Greensight\CommonMsa\Dto\RoleDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Dto\Search\ProductQuery;
use Pim\Dto\Search\ProductSuggestQuery;
use Pim\Services\SearchService\SearchService;

/**
 * Class SearchController
 * @package App\Http\Controllers
 */
class SearchController extends Controller
{
    /**
     * Поиск товаров по запросу
     */
    public function products(Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            'query' => ['required', 'string'],
            'limit' => ['nullable', 'integer'],
            'merchantId' => ['nullable', 'integer'],
            'exceptedIds' => ['nullable', 'array'],
            'exceptedIds.*' => ['required', 'integer'],
        ]);
        $merchantId = $data['merchantId'] ?? null;
        $exceptedIds = $data['exceptedIds'] ?? [];

        /** @var SearchService $searchService */
        $searchService = resolve(SearchService::class);
        $productSuggestQuery = new ProductSuggestQuery();
        $productSuggestQuery->query = $data['query'];
        $productSuggestQuery->limit = 1;
        $productSuggestQuery->withProducts = true;
        $productSuggestQuery->showInactive = true;
        $productSuggestQuery->productsLimit = $data['limit'] ?? 10;
        $productSuggestQuery->role = RoleDto::ROLE_SHOWCASE_GUEST;
        $productSuggestQuery->segment = 1;
        if ($merchantId) {
            $productSuggestQuery->merchantId = $merchantId;
        }
        $productSuggestQuery->fields = [
            ProductQuery::PRODUCT_ID,
            ProductQuery::NAME,
            ProductQuery::VENDOR_CODE,
        ];
        $productSuggestResult = $searchService->suggest($productSuggestQuery);

        return response()->json([
            'products' => array_values(array_filter($productSuggestResult->products, function ($product) use ($exceptedIds) {
                return !in_array($product['id'], $exceptedIds);
            })),
        ]);
    }

    public function productsByVendorCode(Request $request): JsonResponse
    {
        $data = $this->validate(
            $request,
            [
                'vendorCode' => ['required', 'array'],
                'vendorCodes.*' => ['required', 'string'],
                'merchantId' => ['nullable', 'integer'],
            ],
            [],
            [
                'vendorCode.required' => 'Поле \'артикулы\' обязательно для заполнения',
                'vendorCodes.*.required' => 'Поле \'артикулы\' code обязательно для заполнения',
            ],
        );

        $productQuery = new ProductQuery();
        $productQuery->setFilters($data);
        $productQuery->fields([
            ProductQuery::PRODUCT_ID,
            ProductQuery::VENDOR_CODE,
            ProductQuery::NAME,
        ]);
        $productQuery->pageOffset(0, 1000);
        $productQuery->segment = 1;
        $productQuery->role = RoleDto::ROLE_SHOWCASE_GUEST;

        /** @var SearchService $searchService */
        $searchService = resolve(SearchService::class);
        $productSearchResult = $searchService->products($productQuery);

        return response()->json([
            'products' => $productSearchResult->products,
        ]);
    }
}
