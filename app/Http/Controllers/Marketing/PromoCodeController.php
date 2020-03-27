<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Illuminate\Http\Request;

/**
 * Class PromoCodeController
 * @package App\Http\Controllers\Merchant
 */
class PromoCodeController extends Controller
{
    /**
     * Список промокодов
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $this->title = 'Промокоды';
        return $this->render('Marketing/PromoCode/List', []);
    }

    /**
     * Страница для создания промокода
     *
     * @param Request $request
     * @return mixed
     */
    public function createPage(Request $request)
    {
        $this->title = 'Создание промокода';
        return $this->render('Marketing/PromoCode/Create', []);
    }
}
