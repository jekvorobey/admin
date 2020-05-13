<?php


namespace App\Http\Controllers\Merchant\Detail;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Oms\Dto\Delivery\ShipmentStatus;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Services\OfferService\OfferService;

class TabDigestController extends Controller
{
    /**
     * Загрузить основную информацию
     * @param int $merchantId
     * @param MerchantService $merchantService
     * @param OfferService $offerService
     * @param ShipmentService $shipmentService
     * @return JsonResponse
     */
    public function load(
        int $merchantId,
        MerchantService $merchantService,
        OfferService $offerService,
        ShipmentService $shipmentService)
    {
        $period = $this->getPeriod();
        //Товаров на витрине
        $offersQuery = (new RestQuery())
            ->include('product')
            ->setFilter('merchant_id', $merchantId)
            ->setFilter('updated_at', '>', $period);
        $offersCount = $offerService->offersCount($offersQuery);
        //Принято заказов
        $shipmentQuery = (new RestQuery())->setFilter('merchant_id', $merchantId)
            ->setFilter('is_canceled', false)
            ->setFilter('updated_at', '>', $period);
        $shipmentsCount = $shipmentService->shipmentsCount($shipmentQuery);
        //Доставлено заказов
        $arrivedQuery = (new RestQuery())->setFilter('merchant_id', $merchantId)
            ->setFilter('status', ShipmentStatus::DONE)
            ->setFilter('updated_at', '>', $period);
        $arrivedCount = $shipmentService->shipmentsCount($arrivedQuery);
        //Продано товаров
        $sold_total = $merchantService->getTotalSold($merchantId, $period);
        //Начислено комиссии
        $commission = $merchantService->accruedCommission($merchantId, $period);
        //Комментарий к мерчанту
        $comment = $merchantService->merchant($merchantId)->comment;
        return response()->json([
            'products_count' => $offersCount['total'],
            'shipments_count' => $shipmentsCount['total'],
            'arrived_count' => $arrivedCount['total'],
            'sold_count' => $sold_total['count'],
            'sold_price' => $sold_total['price'],
            'commission' => $commission,
            'comment' => $comment,
        ]);
    }

    /**
     * Сохранить комментарий к мерчанту
     * @param int $merchantId
     * @param MerchantService $merchantService
     * @return Application|ResponseFactory|Response
     */
    public function comment(int $merchantId, MerchantService $merchantService)
    {
        $data = $this->validate(request(),[
            'comment' => 'nullable|string|max:1500'
        ]);
        $merchantService->commentMerchant($merchantId, $data['comment']);
        return response('', 204);
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
     * @return string
     */
    protected function getPeriod(): string
    {
        $today = date_create();
        $month_start = date_date_set($today, date('Y'), date('m'), '01');

        return date_format($month_start, 'Y-m-d');
    }
}