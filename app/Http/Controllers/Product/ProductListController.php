<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductApprovalStatus;
use Pim\Dto\Product\ProductDto;
use Pim\Dto\Product\ProductProductionStatus;
use Pim\Dto\Search\ProductQuery;
use Pim\Dto\Search\ProductSearchResult;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Pim\Services\SearchService\SearchService;

class ProductListController extends Controller
{
    public function index(
        Request $request,
        SearchService $searchService,
        CategoryService $categoryService,
        BrandService $brandService
    )
    {
        $this->title = 'Товары';
        $query = $this->makeQuery($request);
        $productSearchResult = $searchService->products($query);
        return $this->render('Product/ProductList', [
            'iProducts' => $productSearchResult->products,
            'iTotal' => $productSearchResult->total,
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'brands' => $brandService->brands($brandService->newQuery()),
                'categories' => $categoryService->categories($categoryService->newQuery()),
                'productionStatuses' => ProductProductionStatus::allStatuses(),
                'productionDone' => ProductProductionStatus::DONE,
                'approvalStatuses' => ProductApprovalStatus::allStatuses(),
                'approvalDone' => ProductApprovalStatus::STATUS_APPROVED,
            ]
        ]);
    }
    
    public function page(
        Request $request,
        SearchService $searchService
    )
    {
        $query = $this->makeQuery($request);
        $productSearchResult = $searchService->products($query);
        $data = [
            'products' => $productSearchResult->products,
            'total' => $productSearchResult->total
        ];
        return response()->json($data);
    }
    
    protected function makeQuery(Request $request): ProductQuery
    {
        $query = new ProductQuery();
        $page = $request->get('page', 1);
        $query->page($page, 10);
        $query->fields([
            ProductQuery::ID,
            ProductQuery::DATE_ADD,
            ProductQuery::CATALOG_IMAGE_ID,
            ProductQuery::NAME,
            ProductQuery::VENDOR_CODE,
            ProductQuery::CATEGORY_NAME,
            ProductQuery::BRAND_NAME,
            ProductQuery::PRICE,
            ProductQuery::QTY,
            ProductQuery::ACTIVE,
            ProductQuery::ARCHIVE,
            ProductQuery::PRODUCTION_STATUS,
            ProductQuery::APPROVAL_STATUS,
        ]);
        return $query;
    }
}