<?php

namespace App\Http\Controllers\Customers\Detail;


use App\Http\Controllers\Controller;
use Greensight\Customer\Services\ReferralService\Dto\PutPromotionDto;
use Greensight\Customer\Services\ReferralService\ReferralService;

class TabPromoProductController extends Controller
{
    public function load($id, ReferralService $referralService)
    {
        $promoProducts = $referralService->getPromotions($id);

        return response()->json([
            'promoProducts' => $promoProducts,
        ]);
    }

    public function save($id, ReferralService $referralService)
    {
        $referralService->putPromotions((new PutPromotionDto())
            ->setCustomerId($id)
            ->setProductId(request('product_id'))
            ->setActive((bool)request('active'))
            ->setFiles(request('files', []))
            ->setDescription(request('description'))
        );

        $promoProducts = $referralService->getPromotions($id);

        return response()->json([
            'promoProducts' => $promoProducts,
        ]);
    }
}