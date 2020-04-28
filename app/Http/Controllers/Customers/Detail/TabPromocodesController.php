<?php

namespace App\Http\Controllers\Customers\Detail;

use Greensight\Marketing\Dto\PromoCode\PromoCodeInDto;
use Greensight\Marketing\Services\PromoCodeService\PromoCodeService;

class TabPromocodesController
{
    public function load($id, PromoCodeService $promoCodeService)
    {
        $promoCodeQuery = new PromoCodeInDto();
        $promoCodeQuery->ownerId([$id]);

        $promoCodes = $promoCodeService->promoCodes($promoCodeQuery);

        return response()->json([
            'promocodes' => $promoCodes
        ]);
    }
}