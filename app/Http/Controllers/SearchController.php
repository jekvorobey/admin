<?php

namespace App\Http\Controllers;

use Greensight\CommonMsa\Dto\UserDto;
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
     * @param  Request  $request
     * @return JsonResponse
     */
    public function products(Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            'query' => ['required', 'string'],
            'limit' => ['nullable', 'integer'],
        ]);

        /** @var SearchService $searchService */
        $searchService = resolve(SearchService::class);
        $productSuggestQuery = new ProductSuggestQuery();
        $productSuggestQuery->query = $data['query'];
        $productSuggestQuery->limit = 1;
        $productSuggestQuery->withProducts = true;
        $productSuggestQuery->showInactive = true;
        $productSuggestQuery->productsLimit = $data['limit'] ?? 10;
        $productSuggestQuery->role = UserDto::SHOWCASE__GUEST;
        $productSuggestQuery->segment = 1;
        $productSuggestQuery->fields = [
            ProductQuery::PRODUCT_ID,
            ProductQuery::NAME,
            ProductQuery::VENDOR_CODE,
        ];
        $productSuggestResult = $searchService->suggest($productSuggestQuery);

        return response()->json([
            'products' => $productSuggestResult->products
        ]);
    }
}
