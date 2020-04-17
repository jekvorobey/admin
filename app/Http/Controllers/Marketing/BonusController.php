<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;

/**
 * Class BonusController
 * @package App\Http\Controllers\Marketing
 */
class BonusController extends Controller
{
    public function index()
    {
        return $this->render('Marketing/Bonus/List', [
            'iBonuses' => []
        ]);
    }
}
