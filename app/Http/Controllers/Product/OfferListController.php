<?php
/**
 * Created by PhpStorm.
 * User: madri
 * Date: 21.10.2019
 * Time: 15:32
 */

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Dto\Offer\OfferDto;
use Pim\Dto\Offer\OfferSaleStatus;
use Pim\Services\OfferService\OfferService;
use Pim\Services\ProductService\ProductService;

class OfferListController extends Controller
{
    public function index(
        Request $request,
        OfferService $offerService,
        ProductService $productService,
        MerchantService $merchantService
    )
    {
        $this->title = 'Предложения мерчантов';

        $query = $this->makeQuery($request);

        return $this->render('Product/OfferList', [
            'iOffers' => $this->loadItems($query, $offerService, $productService, $merchantService),
            'iPager' => $offerService->offersCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'saleStatus' => OfferSaleStatus::allStatuses()
            ],
        ]);
    }

    public function page(
        Request $request,
        OfferService $offerService,
        ProductService $productService,
        MerchantService $merchantService
    )
    {
        $query = $this->makeQuery($request);
        $data = [
            'offers' => $this->loadItems($query, $offerService, $productService, $merchantService),
        ];
        if (1 == $request->get('page', 1)) {
            $data['pager'] = $offerService->offersCount($query);
        }
        return response()->json($data);
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
                Rule::in(array_keys(OfferSaleStatus::allStatuses()))
            ],
        ]);

        $offerService->changeSaleStatus($data['offer_ids'], $data['sale_status']);

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

    protected function makeQuery(Request $request)
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);
        $filter = $request->get('filter', []);

        if (isset($filter['saleStatus'])) {
            $query->setFilter('sale_status', $filter['saleStatus']);
        }

        return $query;
    }

    protected function loadItems(
        RestQuery $query,
        OfferService $offerService,
        ProductService $productService,
        MerchantService $merchantService
    )
    {
        $offers = $offerService->offers($query);
        $merchantIds = $offers->pluck('merchant_id')->all();
        $merchants = $merchantService
            ->newQuery()
            ->setFilter('id', $merchantIds)
            ->merchants()
            ->keyBy('id');

        $productIds = $offers->pluck('product_id')->all();
        $products = $productService
            ->newQuery()
            ->setFilter('id', $productIds)
            ->products()
            ->keyBy('id');

        $items = $offers->map(function (OfferDto $offer) use ($merchants, $products) {
            return [
                'id' => $offer->id,
                'merchantName' => isset($merchants[$offer->merchant_id]) ? $merchants[$offer->merchant_id]->legal_name : 'N/A',
                'productName' => isset($products[$offer->product_id]) ? $products[$offer->product_id]->name : 'N/A',
                'sale_status' => $offer->sale_status
            ];
        });

        return $items;
    }
}