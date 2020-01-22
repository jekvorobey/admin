<?php
namespace App\Http\Controllers\Orders\Flow;

use App\Http\Controllers\Controller;

/**
 * Class FlowDeliveryController
 * @package App\Http\Controllers\Orders\Flow
 */
class FlowDeliveryController extends Controller
{
    public function detail(int $id)
    {
        return $this->render('Orders/Flow/Delivery', [
//            'iOrder' => $data
        ]);
    }

}
