<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Dto\Delivery\ShipmentStatus;
use Greensight\Oms\Dto\DeliveryType;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StoreService\StoreService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Pim\Dto\Offer\OfferDto;
use Pim\Services\OfferService\OfferService;
use Pim\Services\ProductService\ProductService;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Marketing\Dto\Price\PricesInDto;
use Greensight\Store\Services\StockService\StockService;
use Greensight\Store\Dto\StockDto;


class TabProductController extends Controller
{
    /**
     * AJAX пагинация списка офферов
     *
     * @param int $merchantId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function page(int $merchantId): JsonResponse
    {
        $query = $this->makeQuery($merchantId);
        $offers = $this->loadItems($query);

        return response()->json([
            'offers' => $offers
        ]);
    }

    /**
     * AJAX пагинация списка офферов
     *
     * @param int $merchantId
     * @return RestQuery
     */
    protected function makeQuery(int $merchantId)
    {
        $query = (new RestQuery())
            ->include('product')
            ->setFilter('merchant_id', $merchantId);
//        $page = request('page', 1);
//        $query->pageNumber($page, 10);
//        $filter = request('filter', []);

//        if (isset($filter['saleStatus'])) {
//            $query->setFilter('sale_status', $filter['saleStatus']);
//        }

        return $query;
    }

    protected function loadItems(RestQuery $query)
    {
        /** @var OfferService $offerService */
        $offerService = resolve(OfferService::class);

        /** @var PriceService $priceService */
        $priceService = resolve(PriceService::class);

        /** @var StockService $stockService */
        $stockService = resolve(StockService::class);

        $offers = $offerService->offers($query);

        $offerIds = $offers->pluck('id')
            ->all();
        $prices = $priceService->prices(
            (new PricesInDto())->setOffers($offerIds)
        );

        $offerIdsFromPrices = $prices->pluck('offer_id')
            ->all();

        $prices = $prices->keyBy('offer_id')
            ->all();

        $qtys = $stockService->stocks(
            (new RestQuery())->addFields(StockDto::class, 'offer_id', 'qty')
                ->setFilter('offer_id', $offerIdsFromPrices)
        );

        $offerIdsFromQtys = $qtys->pluck('offer_id')
            ->all();

        $qtys = $qtys->keyBy('offer_id')
            ->all();

        $offers = $offers->filter(function (OfferDto $offer) use ($offerIdsFromQtys) {
            return in_array($offer->id, $offerIdsFromQtys);
        })->map(function (OfferDto $offer) use ($prices, $qtys) {
            return [
                'id' => $offer->id,
                'name' => $offer->product->name,
                'sale_status' => $offer->sale_status,
                'price' => $prices[$offer->id]->price,
                'qty' => $qtys[$offer->id]->qty,
                'created_at' => $offer->created_at,
            ];
        })->all();

        return $offers;
//        return $prices;
//        return [
//            'prices' => $prices,
//            'offers' => $offers,
//        ];
    }
}