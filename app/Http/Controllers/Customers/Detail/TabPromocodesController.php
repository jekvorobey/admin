<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeInDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeOutDto;
use Greensight\Marketing\Services\PromoCodeService\PromoCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TabPromocodesController extends Controller
{
    public function load($id, Request $request, PromoCodeService $promoCodeService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

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
