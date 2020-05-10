<?php


namespace App\Http\Controllers\Merchant\Detail;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use MerchantManagement\Dto\MerchantDocumentDto;
use MerchantManagement\Services\MerchantService\MerchantService;

class TabDigestController extends Controller
{
    /**
     * Загрузить основную информацию
     * @param int $id
     * @param MerchantService $merchantService
     * @param FileService $fileService
     */
    public function load(int $id, MerchantService $merchantService, FileService $fileService)
    {
        //Товаров на витрине
        //Принято заказов
        //Доставлено заказов
        //Продано товаров
        //Начислено комиссии
    }

    /**
     * Сохранить комментарий к мерчанту
     * @param int $id
     * @param MerchantService $merchantService
     * @return \Illuminate\Http\JsonResponse
     */
    public function comment(int $id, MerchantService $merchantService)
    {
        /**
         * Inner
         */
        return response()->json([
            'status' => '$status'
        ], 200);
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
}