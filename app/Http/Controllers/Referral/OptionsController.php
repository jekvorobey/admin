<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\OptionDto;
use Cms\Services\OptionService\OptionService;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;

class OptionsController extends Controller
{
    /**
     * @throws CmsException
     */
    public function index(OptionService $optionService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_REFERRALS);

        $this->title = 'Параметры отображения';

        return $this->render('Referral/Options', [
            'iRP' => $optionService->get(OptionDto::KEY_SHOWCASE_LK_RP_LEVEL_SHOW),
            'iOrder' => $optionService->get(OptionDto::KEY_SHOWCASE_LK_ORDER_LEVEL_SHOW),
        ]);
    }

    /**
     * @throws CmsException
     */
    public function save(OptionService $optionService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_REFERRALS);

        $optionService->put(OptionDto::KEY_SHOWCASE_LK_RP_LEVEL_SHOW, (bool) request('rp', 0));
        $optionService->put(OptionDto::KEY_SHOWCASE_LK_ORDER_LEVEL_SHOW, (bool) request('order', 0));

        return response()->json();
    }
}
