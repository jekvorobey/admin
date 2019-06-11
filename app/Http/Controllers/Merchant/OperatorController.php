<?php

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;

/**
 * Class OperatorController
 * @package App\Http\Controllers\Merchant
 */
class OperatorController extends Controller
{
    /**
     * @return mixed
     */
    public function list()
    {
        return $this->render('Index', [
            'message' => 'Менеджеры'
        ]);
    }
}