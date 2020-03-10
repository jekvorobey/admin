<?php

namespace App\Http\Controllers\Referral;


use App\Http\Controllers\Controller;

class LevelsController extends Controller
{
    public function list()
    {
        $this->title = 'Реферальные уровни';
        return $this->render('Referral/Levels/List');
    }
}