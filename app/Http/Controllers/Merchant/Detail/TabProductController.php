<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Marketing\Dto\Price\PricesInDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Store\Services\StockService\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Pim\Core\PimException;
use Pim\Dto\Offer\OfferDto;
use Pim\Dto\Offer\OfferSaleStatus;
use Pim\Services\OfferService\OfferService;

class TabProductController extends Controller
{
    /**
     * AJAX подгрузка информации для фильтрации оферов
     *
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function loadProductsData(int $merchantId): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        return response()->json([
            'offerSaleStatuses' => OfferSaleStatus::allStatuses(),
        ]);
    }

    /**
     * AJAX пагинация списка офферов
     *
     * @throws Exception
     */
    public function page(int $merchantId): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

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
     * @throws Exception
     */
    protected function makeQuery(int $merchantId): RestQuery
    {
        $page = request('page', 1);

        $restQuery = (new RestQuery())
            ->include('product')
            ->setFilter('merchant_id', $merchantId)
            ->pageNumber($page, 20);

        $filter = $this->getFilter();

        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'created_at':
                        $value = array_filter($value);
                        if ($value) {
                            $restQuery->setFilter($key, '>=', $value[0]);
                            $restQuery->setFilter($key, '<=', (new Carbon($value[1]))->modify('+1 day')->format('Y-m-d'));
                        }
                        break;
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
        return Validator::make(
            request('filter') ?? [],
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
    }

    /**
     * @throws PimException
     */
    protected function loadItems(RestQuery $query): array
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

        $prices = $offerIds ? $priceService->prices(
            (new PricesInDto())->setOffers($offerIds)
        )->keyBy('offer_id')
            ->all() :
            [];

        $qtys = $offerIds ? $stockService->qtyByOffers($offerIds) : [];

        return $offers->map(function (OfferDto $offer) use ($prices, $qtys) {
            return [
                'id' => $offer->id,
                'product' => [
                    'id' => $offer->product->id,
                    'name' => $offer->product->name,
                    'code' => $offer->product->code
                ],
                'sale_status' => OfferSaleStatus::statusById($offer->sale_status),
                'price' => array_key_exists($offer->id, $prices) ? $prices[$offer->id]->price : 'Нет данных',
                'qty' => array_key_exists($offer->id, $qtys) ? $qtys[$offer->id] : 'Нет данных',
                'created_at' => $offer->created_at,
            ];
        })->all();
    }
}
