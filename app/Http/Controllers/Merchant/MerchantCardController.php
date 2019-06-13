<?php

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;

/**
 * Class MerchantCardController
 * @package App\Http\Controllers\Merchant
 */
class MerchantCardController extends Controller
{
    /**
     * @return mixed
     */
    public function index()
    {
        $this->breadcrumbs = 'merchant.card';
        return $this->render('Index', [
            'message' => 'Карточка мерчанта'
        ]);
    }
}