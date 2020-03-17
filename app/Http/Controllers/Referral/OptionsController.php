<?php

namespace App\Http\Controllers\Referral;


use App\Http\Controllers\Controller;
use Cms\Dto\OptionDto;
use Cms\Services\OptionService\OptionService;

class OptionsController extends Controller
{
    public function index(OptionService $optionService)
    {
        $this->title = 'Параметры отображения';

        return $this->render('Referral/Options', [
            'iRP' => $optionService->get(OptionDto::KEY_SHOWCASE_LK_RP_LEVEL_SHOW),
            'iOrder' => $optionService->get(OptionDto::KEY_SHOWCASE_LK_ORDER_LEVEL_SHOW),
        ]);
    }

    public function save(OptionService $optionService)
    {
        $optionService->put(OptionDto::KEY_SHOWCASE_LK_RP_LEVEL_SHOW, request('rp', 0) ? true : false);
        $optionService->put(OptionDto::KEY_SHOWCASE_LK_ORDER_LEVEL_SHOW, request('order', 0) ? true : false);

        return response()->json();
    }
}