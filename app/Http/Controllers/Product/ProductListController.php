<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Illuminate\Http\Request;
use Pim\Dto\Product\ProductApprovalStatus;
use Pim\Dto\Product\ProductProductionStatus;
use Pim\Dto\Search\ProductQuery;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Pim\Services\SearchService\SearchService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
                'productionCancel' => ProductProductionStatus::REJECTED,
                'approvalStatuses' => ProductApprovalStatus::allStatuses(),
                'approvalDone' => ProductApprovalStatus::STATUS_APPROVED,
                'approvalCancel' => ProductApprovalStatus::STATUS_REJECT,
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
    
    public function updateApprovalStatus(Request $request, ProductService $productService)
    {
        $ids = $request->get('productIds');
        $status = $request->get('status');
        $comment = $request->get('comment');
        if (!$ids || $status === null) {
            throw new BadRequestHttpException('productIds and status required');
        }
        
        $productService->changeApprovalStatus($ids, $status, $comment);
        
        return response()->json();
    }
    
    public function updateProductionStatus(Request $request, ProductService $productService)
    {
        $ids = $request->get('productIds');
        $status = $request->get('status');
        $comment = $request->get('comment');
        if (!$ids || $status === null) {
            throw new BadRequestHttpException('productIds and status required');
        }
        
        $productService->changeProductionStatus($ids, $status, $comment);
        
        return response()->json();
    }
    
    public function updateArchiveStatus(Request $request, ProductService $productService)
    {
        $ids = $request->get('productIds');
        $status = $request->get('status');
        $comment = $request->get('comment');
        if (!$ids || $status === null) {
            throw new BadRequestHttpException('productIds and status required');
        }
        
        $productService->changeArchiveStatus($ids, $status, $comment);
        
        return response()->json();
    }
    protected function makeQuery(Request $request): ProductQuery
    {
        $query = new ProductQuery();
        $page = $request->get('page', 1);
        $query->page($page, 10);
        $query->segment = 1;// todo
        $query->role = UserDto::SHOWCASE__GUEST;
        $query->fields([
            ProductQuery::PRODUCT_ID,
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
            ProductQuery::INDEX_MARK,
        ]);
        
        $filter = $request->get('filter', []);
        $query->name = data_get($filter, 'name');
        $query->id = data_get($filter, 'id');
        $query->vendorCode = data_get($filter, 'vendorCode');
        $active = data_get($filter, 'active');
        if ($active !== null) {
            $query->active = to_boolean($active);
        }
        $archive = data_get($filter, 'archive');
        if ($archive !== null) {
            $query->archive = to_boolean($archive);
        }
        $query->brandCode = data_get($filter, 'brand');
        $query->categoryCode = data_get($filter, 'category');
        $query->approvalStatus = data_get($filter, 'approvalStatus');
        $query->productionStatus = data_get($filter, 'productionStatus');
        $query->priceFrom = data_get($filter, 'priceFrom');
        $query->priceTo = data_get($filter, 'priceTo');
        $query->qtyFrom = data_get($filter, 'qtyFrom');
        $query->qtyTo = data_get($filter, 'qtyTo');
        $query->dateFrom = data_get($filter, 'dateFrom');
        $query->dateTo = data_get($filter, 'dateTo');
        $query->orderBy(ProductQuery::DATE_ADD, 'desc');
        return $query;
    }
}