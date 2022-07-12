<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Services\AuthService\AuthService;
use Greensight\Marketing\Dto\Price\PricesInDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Services\OfferService\OfferService;

class TabDigestController extends Controller
{
    /**
     * Загрузить основную информацию
     */
    public function load(
        int $merchantId,
        PriceService $priceService,
        MerchantService $merchantService,
        OfferService $offerService,
        ShipmentService $shipmentService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $period = $this->getPeriod();

        // Товаров на витрине //
        $offerIds = $offerService->activeOffers($merchantId);
        if (empty($offerIds)) {
            $offers_total_count = 0;
            $offers_total_price = 0;
        } else {
            $offers_prices = $priceService->prices(
                (new PricesInDto())->setOffers($offerIds)
            )->keyBy('price')->keys();
            $offers_total_count = $offers_prices->count();
            $offers_total_price = $offers_prices->sum();
        }

        // Принято заказов //
        $accepted_orders = $shipmentService->acceptedOrdersIds($merchantId, $period);

        // Доставлено заказов //
        $delivered_shipments = $shipmentService->deliveredShipmentIds($merchantId, $period);

        // Продано товаров //
        $sold_total = $merchantService->getTotalSold($merchantId, $period);

        // Начислено комиссии //
        $commission = $merchantService->accruedCommission($merchantId, $period);

        // Комментарий к мерчанту //
        $comment = $merchantService->merchant($merchantId)->comment;

        return response()->json([
            'products' => [
                'count' => $offers_total_count,
                'price' => $offers_total_price,
            ],
            'orders' => $accepted_orders,
            'shipments' => $delivered_shipments,
            'sold' => $sold_total,
            'commission' => $commission,
            'comment' => $comment,
        ]);
    }

    /**
     * Сохранить комментарий к мерчанту
     */
    public function comment(int $merchantId, MerchantService $merchantService): Response|Application|ResponseFactory
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $this->validate(request(), [
            'comment' => 'nullable|string|max:1500',
        ]);
        $merchantService->commentMerchant($merchantId, $data['comment']);
        return response('', 204);
    }

    public function auth(int $id, AuthService $authService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $tokenData = $authService->tokenByUserId(Front::FRONT_MAS, $id);

        if (!isset($tokenData['token']) || !isset($tokenData['refresh'])) {
            return response()->json([
                'status' => false,
            ]);
        }
        $url = sprintf(config('app.mas_host') . '/login-by-token/%s/%s', $tokenData['token'], $tokenData['refresh']);

        return response()->json([
            'status' => true,
            'url' => $url,
        ]);
    }

    /**
     * Войти от имени мерчанта
     */
    public function loginAsMerchant()
    {
        /**
         *
         */
    }

    /**
     * Получить начало текущего месяца в формате Unix
     */
    protected function getPeriod(): string
    {
        $today = date_create();
        $month_start = date_date_set($today, date('Y'), date('m'), '01');

        return date_format($month_start, 'Y-m-d');
    }
}
