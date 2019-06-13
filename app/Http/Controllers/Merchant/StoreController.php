<?php

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;

/**
 * Class StoreController
 * @package App\Http\Controllers\Merchant
 */
class StoreController extends Controller
{
    /**
     * @return mixed
     */
    public function list()
    {
        $this->breadcrumbs = 'merchant.store_list';
        return $this->render('Index', [
            'message' => 'Магазины и склады'
        ]);
    }
}