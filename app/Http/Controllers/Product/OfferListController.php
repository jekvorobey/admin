<?php
/**
 * Created by PhpStorm.
 * User: madri
 * Date: 21.10.2019
 * Time: 15:32
 */

namespace App\Http\Controllers\Product;

use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Marketing\Dto\Price\OfferPriceDto;
use Greensight\Marketing\Dto\Price\PriceOutDto;
use Greensight\Marketing\Dto\Price\PricesInDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Store\Dto\StockDto;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StockService\StockService;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Dto\Offer\OfferDto;
use Pim\Dto\Offer\OfferSaleStatus;
use Pim\Dto\Product\ProductDto;
use Pim\Services\OfferService\OfferService;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OfferListController extends Controller
{
    public function index(
        Request $request,
        OfferService $offerService,
        MerchantService $merchantService,
        PriceService $priceService,
        StockService $stockService
    )
    {
        $this->title = 'Предложения мерчантов';
        $this->loadOfferSaleStatuses = true;

        $query = $this->makeQuery($request);

        return $this->render('Product/OfferList', [
            'iOffers' => $this->loadItems($query, $offerService, $merchantService, $priceService, $stockService),
            'iPager' => $offerService->offersCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'merchants' => $this->getMerchants()->pluck('legal_name', 'id')
            ],
        ]);
    }

    public function page(
        Request $request,
        OfferService $offerService,
        MerchantService $merchantService,
        PriceService $priceService,
        StockService $stockService
    )
    {
        $query = $this->makeQuery($request);
        $data = [
            'offers' => $this->loadItems($query, $offerService, $merchantService, $priceService, $stockService),
        ];
        if (1 == $request->get('page', 1)) {
            $data['pager'] = $offerService->offersCount($query);
        }
        return response()->json($data);
    }

    public function createOffer(
        Request $request,
        OfferService $offerService,
        PriceService $priceService,
        StockService $stockService
    ) {
        $data = $request->validate([
            'product_id' => 'integer|required',
            'merchant_id' => 'integer|required',
            'price' => 'numeric|required',
            'sale_status' => [
                'nullable',
                Rule::in(array_keys(OfferSaleStatus::offerCreateStatuses()))
            ],
            'sale_at' => 'nullable|date',
            'stocks' => 'array',
            'stocks.*.store_id' => 'integer|required',
            'stocks.*.qty' => 'integer|required'
        ]);

        $offerDto = new OfferDto([
            'product_id' => $data['product_id'],
            'merchant_id' => $data['merchant_id'],
            'sale_status' => $data['sale_status'],
        ]);
        if ($data['sale_at']) $offerDto['sale_at'] = $data['sale_at'];
        $offerId = $offerService->createOffer($offerDto);

        if (!$offerId) {
            throw new BadRequestHttpException('Ошибка сохранения оффера');
        }

        $priceService->setPrice($offerId, $data['price']);

        if (!empty($data['stocks'])) {
            $stocks = collect();
            foreach ($data['stocks'] as $stock) {
                $stockDto = new StockDto([
                    'store_id' => $stock['store_id'],
                    'product_id' => $data['product_id'],
                    'offer_id' => $offerId,
                    'qty' => (double) $stock['qty']
                ]);
                $stocks->push($stockDto);
            }
            $stockService->setStocks($stocks);
        }

        return response('', 204);
    }

    public function editOffer(
        Request $request,
        OfferService $offerService,
        PriceService $priceService,
        StockService $stockService
    ) {
        $data = $request->validate([
            'offer_id' => 'integer|required',
            'product_id' => 'integer|required',
            'price' => 'sometimes|numeric|required',
            'sale_status' => [
                'sometimes',
                Rule::in(array_keys(OfferSaleStatus::offerEditStatuses()))
            ],
            'sale_at' => 'sometimes|date',
            'stocks' => 'array',
            'stocks.*.store_id' => 'integer|required',
            'stocks.*.qty' => 'integer|required'
        ]);

        $statusChanged = array_key_exists('sale_status', $data);
        $dateChanged = array_key_exists('sale_at', $data);

        if ($statusChanged || $dateChanged) {
            $offerDto = new OfferDto();
            if ($statusChanged) {
                $offerDto['sale_status'] = $data['sale_status'];
            }
            if ($dateChanged) {
                $offerDto['sale_at'] = $data['sale_at'];
            }
            $offerService->updateOffer($data['offer_id'], $offerDto);
        }

        if (array_key_exists('price', $data)) {
            $priceService->setPrice($data['offer_id'], $data['price']);
        }

        if (!empty($data['stocks'])) {
            $stocks = collect();
            foreach ($data['stocks'] as $stock) {
                $stockDto = new StockDto([
                    'store_id' => $stock['store_id'],
                    'product_id' => $data['product_id'],
                    'offer_id' => $data['offer_id'],
                    'qty' => (double) $stock['qty']
                ]);
                $stocks->push($stockDto);
            }
            $stockService->setStocks($stocks);
        }

        return response('', 204);
    }

    public function changeSaleStatus(
        Request $request,
        OfferService $offerService
    )
    {
        $data = $request->validate([
            'offer_ids' => 'array|required',
            'offer_ids.*' => 'integer',
            'sale_status' => [
                'required',
                Rule::in(array_keys(OfferSaleStatus::offerEditStatuses()))
            ],
            'sale_at' => 'nullable|date'
        ]);

        $offerService->changeSaleStatus($data['offer_ids'], $data['sale_status']);

        if ($data['sale_at']) {
            $offerDto = new OfferDto([
                'sale_at' => $data['sale_at']
            ]);
            foreach ($data['offer_ids'] as $offer_id) {
                $offerService->updateOffer($offer_id, $offerDto);
            }
        }
        return response('', 204);
    }

    public function deleteOffers(
        Request $request,
        OfferService $offerService
    )
    {
        $data = $request->validate([
            'offer_ids' => 'array|required',
            'offer_ids.*' => 'integer',
        ]);

        $offerService->deleteOffers($data['offer_ids']);

        return response('', 204);
    }

//    public function setOfferStocks(
//        int $id,
//        Request $request,
//        StockService $stockService
//    ) {
//        $data = $request->validate([
//            'product_id' => 'integer|required',
//            'stocks' => 'array|required',
//            'stocks.*' => 'array|required',
//            'stocks.*.store_id' => 'integer|required',
//            'stocks.*.qty' => 'integer|required'
//        ]);
//
//        foreach ($data['stocks'] as $stock) {
//            $dto = new StockDto([
//                'store_id' => $stock['store_id'],
//                'product_id' => $data['product_id'],
//                'offer_id' => $id,
//                'qty' => $stock['qty']
//            ]);
//            $stockService->setStock($dto);
//        }
//
//        return response('', 204);
//    }

    public function loadStoreAndQty(
        Request $request,
        StoreService $storeService,
        StockService $stockService
    ) {
        $data = $request->validate([
            'merchant_id' => 'integer|required',
            'offer_id' => 'sometimes|integer|required'
        ]);

        $storeQuery = $storeService->newQuery()->addFields(StoreDto::entity(),'id', 'name');
        $stores = $storeService->stores($storeQuery, $data['merchant_id'])->keyBy('id');
        $qtys = collect([]);

        if (isset($data['offer_id'])) {
            $stockQuery = $stockService->newQuery()
                ->addFields(StockDto::entity(), 'id', 'store_id', 'qty')
                ->setFilter('offer_id', $data['offer_id']);
            $qtys = $stockService->stocks($stockQuery)->keyBy('store_id');
        }

        $data = $stores->map(function (StoreDto $store) use ($qtys) {
            $store['qty'] = $qtys->has($store->id) ? $qtys->get($store->id)->qty : 0;
            return $store;
        });
        return response()->json($data);
    }

    public function validateOffer(
        Request $request,
        ProductService $productService,
        OfferService $offerService
    ) {
        $data = $request->validate([
            'product_id' => 'integer|required',
            'merchant_id' => 'integer|required'
        ]);

        $isOk = true;
        $message = '';

        $product = $productService->newQuery()
            ->setFilter('id', $data['product_id'])
            ->products()
            ->first();
        if (!$product) {
            $isOk = false;
            $message = 'Не найден товар с таким ID';
        } else {
            $offer = $offerService->newQuery()
                ->setFilter('product_id', $data['product_id'])
                ->setFilter('merchant_id', $data['merchant_id'])
                ->offers()
                ->first();
            if ($offer) {
                $isOk = false;
                $message = 'Такой оффер уже существует';
            }
        }

        return response()->json([
            'isOk' => $isOk,
            'message' => $message
        ]);
    }

    protected function makeQuery(Request $request)
    {
        $query = (new RestQuery())
            ->setFilter('entity_type', OfferDto::OFFER_ENTITY_PRODUCT)
            ->include(ProductDto::entity())
            ->addFields(OfferDto::entity(), 'id', 'sale_status', 'merchant_id', 'sale_at', 'created_at')
            ->addFields(ProductDto::entity(), 'id', 'name', 'brand_id');
        $page = $request->get('page', 1);
        $query->pageNumber($page, 20);
        $filters = $request->get('filter', []);

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'statuses':
                    $query->setFilter('sale_status', $value);
                    break;
                case 'merchants':
                    $query->setFilter('merchant_id', $value);
                    break;
                case 'productName':
                    $query->setFilter('product_name', 'like', $value);
                    break;
                default:
                    $query->setFilter($key, $value);
            }
        }
        return $query;
    }

    protected function loadItems(
        RestQuery $query,
        OfferService $offerService,
        MerchantService $merchantService,
        PriceService $priceService,
        StockService $stockService
    )
    {
        $offers = $offerService->offers($query);
        $merchantIds = $offers->pluck('merchant_id')->all();
        $offerIds = $offers->pluck('id')->all();
        if (!$offerIds) {
            return [];
        }

        $merchants = $merchantService
            ->newQuery()
            ->addFields(MerchantDto::entity(), 'id', 'legal_name')
            ->setFilter('id', $merchantIds)
            ->merchants()
            ->keyBy('id');

        $stocks = collect($stockService->qtyByOffers($offerIds));

        $pricesIn = (new PricesInDto())->setOffers($offerIds);
        $prices = $priceService->prices($pricesIn)->keyBy('offer_id');;

        $items = $offers->map(function (OfferDto $offer) use ($merchants, $prices, $stocks) {
            return [
                'id' => $offer->id,
                'productId' => $offer->product ? $offer->product->id : null,
                'productName' => $offer->product ? $offer->product->name : 'N/A',
                'saleStatus' => $offer->sale_status,
                'saleAt' => $offer->sale_at,
                'createdAt' => $offer->created_at,
                'merchantId' => $merchants->has($offer->merchant_id) ? $merchants->get($offer->merchant_id)->id : 'N/A',
                'merchantName' => $merchants->has($offer->merchant_id) ? $merchants->get($offer->merchant_id)->legal_name : 'N/A',
                'price' => $prices->has($offer->id) ? $prices->get($offer->id)->price : 0,
                'qty' => $stocks->has($offer->id) ? $stocks->get($offer->id) : 0
            ];
        });

        return $items;
    }
}