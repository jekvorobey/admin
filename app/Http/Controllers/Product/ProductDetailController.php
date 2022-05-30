<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\AttachPublicEventRequest;
use Cms\Dto\ProductBadgeDto;
use Cms\Services\ContentBadgesService\ContentBadgesService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Marketing\Dto\Bonus\ProductBonusOption\ProductBonusOptionDto;
use Greensight\Marketing\Dto\Price\PriceInDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Marketing\Services\ProductBonusOptionService\ProductBonusOptionService;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Store\Services\StockService\StockService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Pim\Core\PimException;
use Pim\Dto\Offer\OfferDto;
use Pim\Dto\Offer\OfferSaleStatus;
use Pim\Dto\Product\ProductApprovalStatus;
use Pim\Dto\Product\ProductDto;
use Pim\Dto\Product\ProductTipDto;
use Pim\Dto\PropertyDirectoryValueDto;
use Pim\Dto\PropertyDto;
use Pim\Dto\PublicEvent\PublicEventStatus;
use Pim\Dto\Search\ProductQuery;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\OfferService\OfferService;
use Pim\Services\ProductService\ProductService;
use Pim\Services\PropertyDirectoryValueService\PropertyDirectoryValueService;
use Pim\Services\PublicEventService\PublicEventService;
use Pim\Services\SearchService\SearchService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductDetailController extends Controller
{
    /**
     * @return mixed
     */
    public function index(
        int $id,
        ProductService $productService,
        CategoryService $categoryService,
        BrandService $brandService,
        ContentBadgesService $badgesService
    ) {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        [
            $product,
            $images,
            $badges,
            $props,
            $availableProps,
            $directoryValues,
            $publicEvents,
        ] = $this->getProductData($id, $productService);

        $approvalStatuses = collect(ProductApprovalStatus::allStatuses())->pluck('name', 'id')->all();
        $brands = $brandService->newQuery()->prepare($brandService)->brands();
        $categories = $categoryService->newQuery()->prepare($categoryService)->categories();

        $productBonusOptionService = resolve(ProductBonusOptionService::class);
        $availableBadges = $badgesService->productBadges('code', '!=', ProductBadgeDto::BADGE_FOR_PROFI)->keyBy('id');
        $maxPercentagePayment = $productBonusOptionService->get($id, ProductBonusOptionDto::MAX_PERCENTAGE_PAYMENT);

        return $this->render('Product/ProductDetail', [
            'iProduct' => $product,
            'iImages' => $images,
            'iBadges' => $badges,
            'iProperties' => $props,
            'iPublicEvents' => $publicEvents,
            'options' => [
                'availableProperties' => $availableProps,
                'availableBadges' => $availableBadges,
                'directoryValues' => $directoryValues,
                'orderStatuses' => OrderStatus::allStatuses(),
                'offerSaleStatuses' => OfferSaleStatus::allStatuses(),
                'approval' => $approvalStatuses,
                'brands' => $brands,
                'categories' => $categories,
                'marketing' => [
                    'bonus' => [
                        'maxPercentagePayment' => $maxPercentagePayment,
                    ],
                ],
            ],
        ]);
    }

    public function detailData(int $id, ProductService $productService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        [$product, $images, $props, $availableProps, $directoryValues] = $this->getProductData($id, $productService);
        return response()->json([
            'product' => $product,
            'images' => $images,
            'properties' => $props,
            'availableProperties' => $availableProps,
            'directoryValues' => $directoryValues,
        ]);
    }

    public function saveProduct(int $id, Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'name' => 'string',
            'brand_id' => 'integer',
            'category_id' => 'integer',
            'approval_status' => 'integer',
            'vendor_code' => 'string',
            'width' => 'integer',
            'height' => 'integer',
            'length' => 'integer',
            'weight' => 'integer',
        ]);
        $product = new ProductDto($data);

        $productService->updateProduct($id, $product);

        return response()->json();
    }

    /**
     * @throws PimException
     */
    public function saveProps(int $id, Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'props' => 'required|array',
        ]);
        $productService->saveProperties($id, $data['props']);

        return response()->json();
    }

    public function saveOfferProps(int $id, Request $request, OfferService $offerService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'props' => 'required',
        ]);
        $offerService->saveProperties($id, $data['props']);

        return response()->json();
    }

    /**
     * @throws PimException
     */
    public function savePublicEvents(
        AttachPublicEventRequest $request,
        int $id,
        ProductService $productService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $productService->savePublicEvents($id, $request->all()['public_events']);

        return response()->json();
    }

    /**
     * Обновить состав продукта
     * @return Application|ResponseFactory|Response
     */
    public function saveIngredients(int $productId, ProductService $productService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate(request(), [
            'items' => 'present|nullable|json',
        ]);

        $productService->updateIngredients($productId, $data['items']);

        return response('', 204);
    }

    public function saveImage(int $id, Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'id' => 'required|integer',
            'type' => 'required|integer',
        ]);
        $productService->addImage($id, $data['id'], $data['type']);

        return response()->json();
    }

    public function sortImages(int $id, Request $request, ProductService $productService)
    {
        $data = $this->validate($request, [
            'images_ids' => 'required|array',
            'images_ids.*' => 'integer',
            'type' => 'required|integer',
        ]);
        $productService->sortImages($id, $data['images_ids'], $data['type']);
        return response()->json();
    }

    public function deleteImage(int $id, Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'id' => 'required|integer',
            'type' => 'required|integer',
        ]);
        $productService->deleteImage($id, $data['id'], $data['type']);

        return response()->json();
    }

    /**
     * Изменить статус согласования товара
     * @throws PimException
     */
    public function changeApproveStatus(int $id, Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'approval_status' => Rule::in(array_keys(ProductApprovalStatus::allStatuses())),
        ]);
        $product = new ProductDto($data);
        $productService->updateProduct($id, $product);

        return response()->json();
    }

    /**
     * Изменить статус согласования товара на "Отклонен" с комментарием
     * @throws PimException
     */
    public function reject(int $id, Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'approval_status_comment' => 'required|string',
        ]);
        $product = new ProductDto($data);
        $product->approval_status = ProductApprovalStatus::STATUS_REJECT;
        $productService->updateProduct($id, $product);

        return response()->json();
    }

    public function addTip(int $id, Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'description' => 'required',
            'fileId' => 'required',
        ]);
        $tip = new ProductTipDto();
        $tip->description = $data['description'];
        $tip->file_id = $data['fileId'];

        $productService->addTip($id, $tip);
        return response()->json();
    }

    public function editTip(int $id, int $tipId, Request $request, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $description = $request->get('description');
        $fileId = $request->get('fileId');

        $tip = new ProductTipDto();
        $tip->description = $description;
        $tip->file_id = $fileId;

        $productService->updateTip($id, $tipId, $tip);

        return response()->json();
    }

    public function deleteTip(int $id, int $tipId, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $productService->deleteTip($id, $tipId);

        return response()->json();
    }

    protected function getProductData(int $id, ProductService $productService): array
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        /** @var Collection|ProductDto[] $products */
        $products = $productService
            ->newQuery()
            ->include('tips', 'constraints')
            ->setFilter('id', $id)
            ->include('properties')
            ->include('offers')
            ->include('ingredients')
            ->include('publicEvents')
            ->products();
        if (!$products->count()) {
            throw new NotFoundHttpException();
        }
        /** @var ProductDto $product */
        $product = $products->first();
        $stockService = resolve(StockService::class);
        $priceService = resolve(PriceService::class);
        $orderService = resolve(OrderService::class);
        $publicEventService = resolve(PublicEventService::class);
        $merchantIds = $product->offers->pluck('merchant_id');
        $merchants = $this->getMerchants($merchantIds->unique()->all());
        $product->offers = $product->offers->map(function (OfferDto $item) use ($priceService, $stockService, $merchants) {
                $item['qty'] = $stockService->qtyByOffer($item->id);
                $priceIn = new PriceInDto($item->id);
                $priceDto = $priceService->price($priceIn);
                $item['price'] = $priceDto ? $priceDto->price : 0;
                $item['saleStatus'] = OfferSaleStatus::statusById($item['sale_status']);
                $item['merchant'] = $merchants->has($item->merchant_id) ? $merchants[$item->merchant_id] : null;
                $item->created_at = date_time2str(new Carbon($item->created_at));

                return $item;
        });

        $query = new ProductQuery();
        $query->fields([
            ProductQuery::OFFER_ID,
            ProductQuery::QTY,
            ProductQuery::INDEXED_AT,
            ProductQuery::ACTIVE,
        ]);
        $query->id = $id;
        /** @var SearchService $searchService */
        $searchService = resolve(SearchService::class);
        $elasticProduct = current($searchService->products($query)->products);
        $currentOffer = [];
        $elasticProductActive = $elasticProduct['active'] ?? null;
        if ($elasticProduct && $elasticProductActive) {
            $currentOffer = clone $product->offers->where('id', $elasticProduct['offerId'])->first();
            $currentOffer['qty'] = $elasticProduct['qty'];
        }
        if (!$currentOffer) {
            $currentOffer['qty'] = 0;
            $currentOffer['price'] = 0;
        }
        $product['currentOffer'] = $currentOffer;
        $images = $productService->images($product->id);
        $badges = $productService->badges($product->id);

        $offersIds = (collect($product->offers->pluck('id'))->toArray());
        $orders = $offersIds ? $orderService->ordersByOffers(['offersIds' => $offersIds]) : [];
        $product['orders'] = $orders;
        $product['offersIds'] = $offersIds;
        [$props, $availableProps, $directoryValues] = $this->properties($product);

        $publicEvents = $publicEventService->query()->setFilter('status_id', '=', PublicEventStatus::ACTIVE)->get();

        $product['indexed_at'] = $elasticProduct['indexed_at'] ?? null;

        return [
            $product,
            $images,
            $badges,
            $props,
            $availableProps,
            $directoryValues,
            $publicEvents,
        ];
    }

    protected function properties(ProductDto $product): array
    {
        $categoryService = resolve(CategoryService::class);
        $directoryValueService = resolve(PropertyDirectoryValueService::class);

        $propertiesQuery = $categoryService->newQuery()->addFields('id', 'name', 'display_name', 'type', 'is_multiple');
        $availableProperties = $categoryService
            ->categoryProperties($product->category_id, $propertiesQuery);
        $directoryPropertyIds = $availableProperties
            ->filter(function (PropertyDto $property) {
                return $property->type == PropertyDto::TYPE_DIRECTORY;
            })
            ->pluck('id')->toArray();

        $directoryValuesQuery = $directoryValueService
            ->newQuery()
            ->setFilter('property_id', $directoryPropertyIds)
            ->addFields(PropertyDirectoryValueDto::entity(), 'id', 'name', 'property_id');

        $directoryValues = $directoryValueService->values($directoryValuesQuery)->groupBy('property_id');
        $properties = [];
        foreach ($product->properties as $property) {
            if (!isset($properties[$property->property_id])) {
                $properties[$property->property_id] = [];
            }
            $properties[$property->property_id][] = $property->value;
        }
        return [
            $properties,
            $availableProperties,
            $directoryValues,
        ];
    }
}
