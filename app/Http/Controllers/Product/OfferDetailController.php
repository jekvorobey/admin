<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Marketing\Dto\Price\PriceInDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Store\Dto\StockDto;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StockService\StockService;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\Request;
use Pim\Dto\Offer\OfferDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\OfferService\OfferService;

class OfferDetailController extends Controller
{
    public function index(
        int $id,
        OfferService $offerService,
        PriceService $priceService,
        StockService $stockService
    )
    {
        $this->loadOfferSaleStatuses = true;
        $offerInfo = $this->loadOfferInfo($id, $offerService, $priceService, $stockService);

        return $this->render('Product/OfferDetail', [
            'offer' => $offerInfo,
        ]);
    }

    public function loadStocks(int $id, Request $request, StockService $stockService)
    {
        $query = (new RestQuery())
            ->setFilter('offer_id', $id)
            ->include(StoreDto::entity())
            ->include(StoreDto::entity() . '.storeContact')
            ->addFields(StockDto::entity(), 'qty', 'store_id')
            ->addFields(StoreDto::entity(), 'id', 'name', 'address')
            ->addFields(StoreDto::entity() . '.storeContact', 'store_id', 'name', 'phone', 'email');
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);

        $filters = $request->get('filter', []);
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'name':
                    $query->setFilter('store_name', $value);
                    break;
                case 'address':
                    $query->setFilter('store_address', $value);
                    break;
                case 'contacts':
                    $query->setFilter('store_contacts', $value);
                    break;
                case 'qty_from':
                    $query->setFilter('qty', '>=', $value);
                    break;
                case 'qty_to':
                    $query->setFilter('qty', '<=', $value);
                    break;
                default:
                    $query->setFilter($key, $value);
            }
        }

        $stocks = $stockService->stocks($query)->map(function (StockDto $stock) {
            return [
                'store_id' => $stock->store_id,
                'qty' => $stock->qty,
                'name' => $stock->store ? $stock->store->name : 'N/A',
                'address' => $stock->store ? $stock->store->address['address_string'] : 'N/A',
                'contacts' => $stock->store->storeContact->first() ?? null,
            ];
        });
        return response()->json([
            'stocks' => $stocks,
        ]);
    }

    private function loadOfferInfo(
        int $id,
        OfferService $offerService,
        PriceService $priceService,
        StockService $stockService
    )
    {
        $offer = $offerService->newQuery()
            ->setFilter('id', $id)
            ->include(ProductDto::entity())
            ->addFields(OfferDto::entity(), 'id', 'merchant_id', 'sale_status', 'sale_at', 'created_at')
            ->addFields(ProductDto::entity(), 'id', 'name')
            ->offers()
            ->first();

        $price = $priceService->price(new PriceInDto($id));

        $stocks = $stockService->newQuery()
            ->setFilter('offer_id', $id)
            ->include(StoreDto::entity())
            ->include(StoreDto::entity() . '.storeContact')
            ->addFields(StockDto::entity(), 'qty', 'store_id')
            ->addFields(StoreDto::entity(), 'id', 'name', 'address')
            ->addFields(StoreDto::entity() . '.storeContact', 'store_id', 'name', 'phone', 'email')
            ->stocks();

        return [
            'id' => $offer->id,
            'product_id' => $offer->product->id,
            'merchant_id' => $offer->merchant_id,
            'name' => $offer->product ? $offer->product->name : 'N/A',
            'status' => $offer->sale_status,
            'sale_at' => $offer->sale_at,
            'price' => $price ? $price->price : 0,
            'created_at' => $offer->created_at,
            'stocks' => $stocks->map(function ($stock) {
                return [
                    'store_id' => $stock->store_id,
                    'qty' => $stock->qty,
                    'name' => $stock->store ? $stock->store->name : 'N/A',
                    'address' => $stock->store ? $stock->store->address['address_string'] : 'N/A',
                    'contacts' => $q = $stock->store->storeContact->first() ?? null,
                ];
            })->toArray()
        ];
    }

}