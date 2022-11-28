<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Cms\Dto\ProductBadgeDto;
use Cms\Services\ContentBadgesService\ContentBadgesService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\RoleDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pim\Core\PimException;
use Pim\Dto\Product\ProductApprovalStatus;
use Pim\Dto\Product\ProductProductionStatus;
use Pim\Dto\Search\ProductQuery;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\OfferService\OfferService;
use Pim\Services\ProductService\ProductService;
use Pim\Services\SearchService\SearchService;
use Pim\Services\ShoppilotService\ShoppilotService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProductListController extends Controller
{
    /**
     * @throws PimException
     */
    public function index(
        Request              $request,
        SearchService        $searchService,
        CategoryService      $categoryService,
        BrandService         $brandService,
        ShoppilotService     $shoppilotService,
        ContentBadgesService $badgesService,
        OfferService         $offerService
    )
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $this->title = 'Товары';
        $query = $this->makeQuery($request);
        $productSearchResult = $searchService->products($query);

        $productIds = collect($productSearchResult->products)->pluck('id')->all();
        $shoppilotProductsExist = $productIds ? $shoppilotService->productsExist($productIds) : [];
        if ($shoppilotProductsExist) {
            $productSearchResult->products = array_map(function ($product) use ($shoppilotProductsExist) {
                $product['shoppilotExist'] = $shoppilotProductsExist[$product['id']];
                return $product;
            }, $productSearchResult->products);
        }
        $offers = $offerService->offers(
            (new RestQuery())
                ->setFilter('product_id', $productIds)
        );
        $offers = $offers->mapToGroups(function ($item) {
            return [$item->product_id => $item->id];
        });

        $productSearchResult->products = array_map(function ($product) use ($offers) {
            $product['offerId'] = $offers[$product['id']] ?? null;
            return $product;
        }, $productSearchResult->products);


        return $this->render('Product/ProductList', [
            'iProducts' => $productSearchResult->products,
            'iTotal' => $productSearchResult->total,
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'brands' => $brandService->brands($brandService->newQuery())->values()->toArray(),
                'categories' => $categoryService->categories($categoryService->newQuery())->values()->toArray(),
                'productionStatuses' => ProductProductionStatus::allStatuses(),
                'productionDone' => ProductProductionStatus::DONE,
                'productionCancel' => ProductProductionStatus::REJECTED,
                'approvalStatuses' => ProductApprovalStatus::allStatuses(),
                'availableBadges' => $badgesService->productBadges('code', '!=', ProductBadgeDto::BADGE_FOR_PROFI),
                'merchants' => $this->getMerchants()->values(),
                'approvalDone' => ProductApprovalStatus::STATUS_APPROVED,
                'approvalCancel' => ProductApprovalStatus::STATUS_REJECT,
            ],
        ]);
    }

    /**
     * @throws PimException
     */
    public function page(
        Request          $request,
        SearchService    $searchService,
        ShoppilotService $shoppilotService
    ): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $query = $this->makeQuery($request);
        $productSearchResult = $searchService->products($query);

        $productIds = collect($productSearchResult->products)->pluck('id')->all();
        $shoppilotProductsExist = $productIds ? $shoppilotService->productsExist($productIds) : [];
        if ($shoppilotProductsExist) {
            $productSearchResult->products = array_map(function ($product) use ($shoppilotProductsExist) {
                $product['shoppilotExist'] = $shoppilotProductsExist[$product['id']];

                return $product;
            }, $productSearchResult->products);
        }

        $data = [
            'products' => $productSearchResult->products,
            'total' => $productSearchResult->total,
        ];
        return response()->json($data);
    }

    public function updateApprovalStatus(Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $ids = $request->get('productIds');
        $status = $request->get('status');
        $comment = $request->get('comment');

        $status = match ($status) {
            'accept' => ProductApprovalStatus::STATUS_APPROVED,
            'reject' => ProductApprovalStatus::STATUS_REJECT,
            default => $request->get('status'),
        };
        if (!$ids || $status === null) {
            throw new BadRequestHttpException('productIds and status required');
        }

        $productService->changeApprovalStatus($ids, $status, $comment);

        return response()->json();
    }

    public function updateProductionStatus(Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $ids = $request->get('productIds');
        $status = $request->get('status');
        $comment = $request->get('comment');
        if (!$ids || $status === null) {
            throw new BadRequestHttpException('productIds and status required');
        }

        $productService->changeProductionStatus($ids, $status, $comment);

        return response()->json();
    }

    public function updateArchiveStatus(Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $ids = $request->get('productIds');
        $status = $request->get('status');
        $comment = $request->get('comment');
        if (!$ids || $status === null) {
            throw new BadRequestHttpException('productIds and status required');
        }

        $productService->changeArchiveStatus($ids, $status, $comment);

        return response()->json();
    }

    /**
     * Обновить шильдики у товаров
     */
    public function updateBadges(ProductService $productService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate(request(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer',
            'badges' => 'nullable|json',
        ]);

        $productIds = $data['product_ids'];
        $badges = json_decode($data['badges']) ?? null;

        $productService->updateBadges($productIds, $badges);

        return response('', 204);
    }

    /**
     * Открепить шильдики от товаров
     */
    public function detachBadges(ProductService $productService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate(request(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer',
            'badges' => 'nullable|json',
        ]);

        $productIds = $data['product_ids'];
        $badges = $data['badges'] ?? null;
        $productService->detachBadges($productIds, $badges);

        return response('', 204);
    }

    protected function makeQuery(Request $request): ProductQuery
    {
        $query = new ProductQuery();
        $filter = $request->get('filter', []);
        $page = $request->get('page', 1);
        if (data_get($filter, 'pageSize') && data_get($filter, 'pageSize') <= 500) {
            $pageSize = data_get($filter, 'pageSize');
        } else {
            $pageSize = 10;
        }
        $query->page($page, $pageSize);
        $query->segment = 1;// todo
        $query->role = RoleDto::ROLE_SHOWCASE_GUEST;
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
            ProductQuery::STRIKES,
            ProductQuery::OFFER_ID,
            ProductQuery::MERCHANT_ID,
        ]);

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
        $query->isPriceHidden = data_get($filter, 'isPriceHidden');
        $query->badges = data_get($filter, 'badges');
        $query->offer_id = data_get($filter, 'offerId');
        $query->merchantId = data_get($filter, 'merchant');
        $query->vendorCode = data_get($filter, 'vendorCode');
        $query->orderBy(ProductQuery::DATE_ADD, 'desc');

        return $query;
    }
}
