<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use MerchantManagement\Dto\MerchantSettingDto;
use MerchantManagement\Services\MerchantService\MerchantService;

class TabBillingController extends Controller
{
    /**
     * @param int             $merchantId
     * @param MerchantService $merchantService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function load(int $merchantId, MerchantService $merchantService)
    {
        $settings = $merchantService->getSetting($merchantId, MerchantSettingDto::BILLING_CYCLE)->first();
        $billingCycle = $settings ? $settings->value : MerchantSettingDto::DEFAULT_BILLING_CYCLE;
        return response()->json([
            'billing_cycle' => (int)$billingCycle,
        ]);
    }

    /**
     * Сохранить биллинговый период
     * @param int $merchantId
     * @param MerchantService $merchantService
     * @return Application|ResponseFactory|Response
     */
    public function billingCycle(int $merchantId, MerchantService $merchantService)
    {
        $data = $this->validate(request(),[
            'billing_cycle' => 'integer|gt:0'
        ]);
        $merchantService->setSetting($merchantId, MerchantSettingDto::BILLING_CYCLE, $data['billing_cycle']);
        return response('', 204);
    }

}
