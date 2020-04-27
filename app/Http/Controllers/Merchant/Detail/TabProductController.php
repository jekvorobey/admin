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
use Pim\Dto\Offer\OfferSaleStatus;
use Pim\Services\ProductService\ProductService;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Marketing\Dto\Price\PricesInDto;
use Greensight\Store\Services\StockService\StockService;
use Greensight\Store\Dto\StockDto;


class TabProductController extends Controller
{
    /**
     * AJAX подгрузка информации для фильтрации оферов
     *
     * @param int $merchantId
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function loadProductsData(int $merchantId, Request $request)
    {
        return response()->json([
            'offerSaleStatuses' => OfferSaleStatus::allStatuses(),
        ]);
    }

    /**
     * AJAX пагинация списка офферов
     *
     * @param int $merchantId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function page(int $merchantId): JsonResponse
    {
        /** @var OfferService $offerService */
        $offerService = resolve(OfferService::class);

        $restQuery = $this->makeQuery($merchantId);
        $offers = $this->loadItems($restQuery);
        $result = [
            'offers' => $offers,
        ];
        if (request('page') == 1) {
            $result['pager'] = $offerService->offersCount($restQuery);
        }

        return response()->json($result);
    }

    /**
     * AJAX пагинация списка офферов
     *
     * @param int $merchantId
     * @return RestQuery
     */
    protected function makeQuery(int $merchantId)
    {
        $page = request('page', 1);

        $restQuery = (new RestQuery())
            ->include('product')
            ->setFilter('merchant_id', $merchantId)
            ->pageNumber($page, 10);

        $filter = $this->getFilter();

        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'price_from':
                        $restQuery->setFilter('price', '>=', $value);
                        break;
                    case 'price_to':
                        $restQuery->setFilter('price', '<=', $value);
                        break;
//                    case 'qty_from':
//                        $restQuery->setFilter('qty', '>=', $value);
//                        break;
//                    case 'qty_to':
//                        $restQuery->setFilter('qty', '<=', $value);
//                        break;
                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
        }

        return $restQuery;
    }

    /**
     * @return array
     */
    protected function getFilter(): array
    {
        $filter = Validator::make(request('filter') ?? [],
            [
                'id' => 'string|someone',
                'product_name' => 'string|someone',
                'sale_status' => 'array|someone',
                'sale_status.' => Rule::in(array_keys(OfferSaleStatus::allStatuses())),
                'price_from' => 'numeric|someone',
                'price_to' => 'numeric|someone',
                'qty_from' => 'numeric|someone',
                'qty_to' => 'numeric|someone',
            ]
        )->attributes();

        return $filter;
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
        )->keyBy('offer_id')
        ->all();

        $qtys = $stockService->stocks(
            (new RestQuery())->addFields(StockDto::class, 'offer_id', 'qty')
                ->setFilter('offer_id', $offerIds)
        )->keyBy('offer_id')
            ->all();

        $offers = $offers->map(function (OfferDto $offer) use ($prices, $qtys) {
            return [
                'id' => $offer->id,
                'product' => [
                    'id' => $offer->product->id,
                    'name' => $offer->product->name,
                ],
                'sale_status' => OfferSaleStatus::statusById($offer->sale_status),
                'price' => array_key_exists($offer->id, $prices) ? $prices[$offer->id]->price : "Нет данных",
                'qty' => array_key_exists($offer->id, $qtys) ? $qtys[$offer->id]->qty : "Нет данных",
                'created_at' => $offer->created_at,
            ];
        })->all();

        return $offers;
    }
}