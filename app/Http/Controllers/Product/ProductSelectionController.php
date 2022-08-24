<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\RoleDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\Search\ProductQuery;
use Pim\Services\BrandService\BrandService;
use Pim\Services\OfferService\OfferService;
use Pim\Services\SearchService\SearchService;

class ProductSelectionController extends Controller
{
    /**
     * @throws PimException
     */
    public function selection(
        Request $request,
        SearchService $searchService,
        BrandService $brandService,
        OfferService $offerService
    ) {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);
        $this->title = 'Подбор товаров';

        $query = $this->makeQuery($request);
        $productSearchResult = $searchService->products($query);

        $productIds = collect($productSearchResult->products)->pluck('id')->all();

        $offers = $offerService->offers(
            (new RestQuery())
                ->setFilter('product_id', $productIds)
        );

        $offers = $offers->mapToGroups(function ($item) {
            return [$item->product_id => $item->id];
        });

        $productSearchResult->products = array_map(function ($product) use ($offers) {

            $product['offerIds'] = $offers[$product['id']] ?? null;
            return $product;
        }, $productSearchResult->products);

        dump([
            'iProducts' => $productSearchResult->products,
            'iTotal' => $productSearchResult->total,
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'brands' => $brandService->brands($brandService->newQuery())->values()->toArray(),
                'merchants' => $this->getMerchants()->values(),
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
            ProductQuery::BRAND_NAME,
            ProductQuery::STRIKES,
            ProductQuery::DATE_ADD,
        ]);

        $filter = $request->get('filter', []);
        $query->merchantId = data_get($filter, 'merchants');
        $query->vendorCode = data_get($filter, 'vendorCode');
        $query->id = data_get($filter, 'id');
        $query->name = data_get($filter, 'name');
        $query->brandCode = data_get($filter, 'brand');
        $query->badges = data_get($filter, 'badges');
        $query->orderBy(ProductQuery::DATE_ADD, 'desc');

        return $query;
    }
}
