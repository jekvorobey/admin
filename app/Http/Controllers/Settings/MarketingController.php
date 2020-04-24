<?php

namespace App\Http\Controllers\Settings;

use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\Marketing\Dto\Option\OptionDto;
use Greensight\Marketing\Services\OptionService\OptionService;


class MarketingController extends Controller
{
    public function index(OptionService $optionService)
    {
        $this->title = 'Маректинговые инструменты iBT.Studio';

        return $this->render('Settings/Marketing', [
            'bonus_per_rubles' => $optionService->get(OptionDto::KEY_BONUS_PER_RUBLES),
            'roles_available_for_bonuses' => $optionService->get(OptionDto::KEY_ROLES_AVAILABLE_FOR_BONUSES),

            'roles' => Helpers::getOptionRoles(false),
        ]);
    }

    public function update(OptionService $optionService)
    {
        $data = $this->validate(request(), [
            'bonus_per_rubles' => 'numeric',
            'roles_available_for_bonuses'  => 'array',
            'roles_available_for_bonuses.*'  => 'integer',
        ]);

        if (array_key_exists('bonus_per_rubles', $data)) {
            $bonusPerRub = (float) $data['bonus_per_rubles'];
            if ($bonusPerRub >= 0) {
                $optionService->put(OptionDto::KEY_BONUS_PER_RUBLES, $bonusPerRub);
            }
        }
        if (array_key_exists('roles_available_for_bonuses', $data)) {
            $roles = array_filter($data['roles_available_for_bonuses'], function ($roleId) {
                return in_array($roleId, [
                    UserDto::SHOWCASE__PROFESSIONAL,
                    UserDto::SHOWCASE__REFERRAL_PARTNER,
                ]);
            });

            $optionService->put(OptionDto::KEY_ROLES_AVAILABLE_FOR_BONUSES, $roles);
        }
    }
}
