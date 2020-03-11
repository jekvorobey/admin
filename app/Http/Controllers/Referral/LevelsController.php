<?php

namespace App\Http\Controllers\Referral;


use App\Http\Controllers\Controller;
use Greensight\Customer\Services\ReferralService\ReferralService;

class LevelsController extends Controller
{
    public function list(ReferralService $referralService)
    {
        $this->title = 'Реферальные уровни';

        $levels = $referralService->getLevels()->sortBy('sort')->values();
        return $this->render('Referral/Levels/List', [
            'levels' => $levels
        ]);
    }
}