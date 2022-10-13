<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\RoleDto;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\Search\ProductQuery;
use Pim\Services\SearchService\SearchService;
use Illuminate\Http\JsonResponse;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Dto\MerchantDto;

class ProductSelectionController extends Controller
{
    /**
     * @throws PimException
     */
    public function selection(Request $request, SearchService $searchService, MerchantService $merchantService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);
        $this->title = 'Подбор товаров';

        $query = $this->makeQuery($request);
        $productSearchResult = $this->loadItems($query, $searchService, $merchantService);

        return $this->render('Product/ProductSelection', [
            'iProducts' => $productSearchResult->products,
            'iTotal' => $productSearchResult->total,
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'merchants' => $this->getMerchants()->pluck('legal_name', 'id'),
            ],
        ]);
    }

    protected function makeQuery(Request $request): ProductQuery
    {
        $query = new ProductQuery();
        $page = $request->get('page', 1);
        $query->page($page, 10);
        $query->segment = 1;// todo
        $query->role = RoleDto::ROLE_SHOWCASE_GUEST;
        $query->fields([
            ProductQuery::OFFER_ID,
            ProductQuery::MERCHANT_ID,
            ProductQuery::VENDOR_CODE,
            ProductQuery::PRODUCT_ID,
            ProductQuery::NAME,
            ProductQuery::CATALOG_IMAGE_ID,
            ProductQuery::BRAND_NAME,
            ProductQuery::ACTIVE,
            ProductQuery::STRIKES,
            ProductQuery::DATE_ADD,
        ]);

        $filter = $request->get('filter', []);
        $query->offer_id = data_get($filter, 'offerId');
        $query->merchantId = data_get($filter, 'merchants');
        $query->vendorCode = data_get($filter, 'vendorCode');
        $query->orderBy(ProductQuery::DATE_ADD, 'desc');

        return $query;
    }

    /**
     * @throws PimException
     */
    protected function loadItems(ProductQuery $query, SearchService $searchService, MerchantService $merchantService)
    {
        $productSearchResult = $searchService->products($query);
        $merchantIds = collect($productSearchResult->products)->pluck('merchantId')->all();
        $productIds = collect($productSearchResult->products)->pluck('id')->all();
        if (!$productIds) {
            return collect();
        }

        $merchants = $merchantService
            ->newQuery()
            ->addFields(MerchantDto::entity(), 'id', 'legal_name')
            ->setFilter('id', $merchantIds)
            ->merchants()
            ->keyBy('id');

        $productSearchResult->products = array_map(function ($product) use ($merchants) {
            $product['merchantName'] = $merchants->has($product['merchantId']) ? $merchants->get($product['merchantId'])->legal_name : 'N/A';
            return $product;
        }, $productSearchResult->products);

        return $productSearchResult;
    }

    /**
     * @throws PimException
     */
    public function page(Request $request, SearchService $searchService, MerchantService $merchantService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $query = $this->makeQuery($request);
        $productSearchResult = $this->loadItems($query, $searchService, $merchantService);

        $data = [
            'products' => $productSearchResult->products,
            'total' => $productSearchResult->total,
        ];
        return response()->json($data);
    }
}