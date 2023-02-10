<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\RoleDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Core\PimException;
use Pim\Dto\Offer\OfferDto;
use Pim\Dto\Search\ProductQuery;
use Pim\Dto\Search\ProductSearchResult;
use Pim\Services\OfferService\OfferService;
use Pim\Services\SearchService\SearchService;

class ProductSelectionController extends Controller
{
    /**
     * @throws PimException
     */
    public function selection(Request $request, SearchService $searchService, MerchantService $merchantService, OfferService $offerService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);
        $this->title = 'Подбор товаров';

        $query = $this->makeQuery($request);
        $productSearchResult = $this->loadItems($query, $searchService, $merchantService, $offerService);

        return $this->render('Product/ProductSelection', [
            'iProducts' => $productSearchResult->products,
            'iTotal' => $productSearchResult->total,
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'merchants' => $this->getMerchants()->pluck('name', 'id'),
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
    protected function loadItems(ProductQuery $query, SearchService $searchService, MerchantService $merchantService, OfferService $offerService)
    {
        $productSearchResult = $searchService->products($query);
        $merchantIds = collect($productSearchResult->products)->pluck('merchantId')->all();
        $productIds = collect($productSearchResult->products)->pluck('id')->all();
        if (!$productIds) {
            return collect();
        }

        $OfferQuery = $offerService->newQuery()
            ->addFields(OfferDto::entity(), 'id', 'xml_id', 'guid')
            ->setFilter('product_id', $productIds);
        $offers = $offerService->offers($OfferQuery)->keyBy('id');

        $merchantQuery = $merchantService->newQuery()
            ->addFields(MerchantDto::entity(), 'id', 'name')
            ->setFilter('id', $merchantIds);
        $merchants = $merchantService->merchants($merchantQuery)->keyBy('id');

        $productSearchResult->products = array_map(static function ($product) use ($merchants, $offers) {
            $product['merchantName'] = $merchants->has($product['merchantId']) ? $merchants->get($product['merchantId'])->name : 'N/A';
            $product['xmlId'] = $offers->has($product['offerId']) ? $offers->get($product['offerId'])->xml_id : null;
            $product['guid'] = $offers->has($product['offerId']) ? $offers->get($product['offerId'])->guid : null;
            return $product;
        }, $productSearchResult->products);

        return $productSearchResult;
    }

    /**
     * @throws PimException
     */
    public function page(Request $request, SearchService $searchService, MerchantService $merchantService, OfferService $offerService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $query = $this->makeQuery($request);
        $productSearchResult = $this->loadItems($query, $searchService, $merchantService, $offerService);

        $data = [
            'products' => $productSearchResult->products,
            'total' => $productSearchResult->total,
        ];
        return response()->json($data);
    }
}
