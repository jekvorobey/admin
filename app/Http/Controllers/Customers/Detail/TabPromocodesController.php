<?php

namespace App\Http\Controllers\Customers\Detail;

use Greensight\Marketing\Dto\PromoCode\PromoCodeInDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeOutDto;
use Greensight\Marketing\Services\PromoCodeService\PromoCodeService;
use Illuminate\Http\Request;

class TabPromocodesController
{
    public function load($id, Request $request, PromoCodeService $promoCodeService)
    {
        $promoCodeQuery = new PromoCodeInDto();
        $promoCodeQuery->ownerId([$id]);
        if ($request->get('mode') == 'archive') {
            $promoCodeQuery->status([
                PromoCodeOutDto::STATUS_REJECTED,
                PromoCodeOutDto::STATUS_PAUSED,
                PromoCodeOutDto::STATUS_EXPIRED,
            ]);
        } else {
            $promoCodeQuery->status([
                PromoCodeOutDto::STATUS_CREATED,
                PromoCodeOutDto::STATUS_SENT,
                PromoCodeOutDto::STATUS_ON_CHECKING,
                PromoCodeOutDto::STATUS_ACTIVE,
                PromoCodeOutDto::STATUS_TEST,
            ]);
        }

        $promoCodes = $promoCodeService->promoCodes($promoCodeQuery);

        return response()->json([
            'promocodes' => $promoCodes->map(function (PromoCodeOutDto $promoCode) {
                $promoCode['validityPeriod'] = $promoCode->validityPeriod();
                return $promoCode;
            }),
        ]);
    }
}
