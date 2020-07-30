<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\Marketing\Dto\Price\PriceInDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Store\Dto\StockDto;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StockService\StockService;
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
            'name' => $offer->product ? $offer->product->name : 'N/A',
            'status' => $offer->sale_status,
            'sale_at' => $offer->sale_at,
            'price' => $price ? $price->price : 0,
            'created_at' => $offer->created_at,
            'stocks' => $stocks->map(function ($stock) {

                $contacts = $stock->store->storeContact->first() ?? null;

                return [
                    'qty' => $stock->qty,
                    'name' => $stock->store ? $stock->store->name : 'N/A',
                    'address' => $stock->store ? $stock->store->address['address_string'] : 'N/A',
                    'contacts' => [
                        'name' => $contacts ? $contacts->name : 'N/A',
                        'phone' => $contacts ? $contacts->phone : 'N/A',
                        'email' => $contacts ? $contacts->email : 'N/A',
                    ]
                ];
            })->toArray()
        ];
    }

}