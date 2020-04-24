<?php

namespace App\Http\Controllers\Settings;

use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\Marketing\Dto\Option\OptionDto;
use Greensight\Marketing\Services\OptionService\OptionService;
use Illuminate\Validation\Rule;


class MarketingController extends Controller
{
    public function index(OptionService $optionService)
    {
        $this->title = 'Маректинговые инструменты iBT.Studio';

        return $this->render('Settings/Marketing', [
            'bonus_per_rubles' => $optionService->get(OptionDto::KEY_BONUS_PER_RUBLES),
            'roles_available_for_bonuses' => $optionService->get(OptionDto::KEY_ROLES_AVAILABLE_FOR_BONUSES),
            'activation_bonus_name' => $optionService->get(OptionDto::KEY_ACTIVATION_BONUS_NAME),
            'activation_bonus_value' => $optionService->get(OptionDto::KEY_ACTIVATION_BONUS_VALUE),
            'activation_bonus_valid_period' => $optionService->get(OptionDto::KEY_ACTIVATION_BONUS_VALID_PERIOD),

            'roles' => Helpers::getOptionRoles(false),
        ]);
    }

    public function update(OptionService $optionService)
    {
        $data = $this->validate(request(), [
            'bonus_per_rubles' => 'numeric|gte:0',
            'roles_available_for_bonuses'  => 'array',
            'roles_available_for_bonuses.*'  => [
                Rule::in([
                    UserDto::SHOWCASE__PROFESSIONAL,
                    UserDto::SHOWCASE__REFERRAL_PARTNER
                ])],
            'activation_bonus_name'  => 'string|nullable',
            'activation_bonus_value'  => 'integer|gt:0|nullable',
            'activation_bonus_valid_period'  => 'integer|gte:0|nullable',
        ]);

        foreach ($data as $key => $v) {
            switch ($key) {
                case 'bonus_per_rubles':
                    $optionService->put(OptionDto::KEY_BONUS_PER_RUBLES, (float) $v);
                    break;
                case 'roles_available_for_bonuses':
                    $optionService->put(OptionDto::KEY_ROLES_AVAILABLE_FOR_BONUSES, $v);
                    break;
                case 'activation_bonus_name':
                    $optionService->put(OptionDto::KEY_ACTIVATION_BONUS_NAME, $v);
                    break;
                case 'activation_bonus_value':
                    $optionService->put(OptionDto::KEY_ACTIVATION_BONUS_VALUE, $v ? (int) $v : null);
                    break;
                case 'activation_bonus_valid_period':
                    $optionService->put(OptionDto::KEY_ACTIVATION_BONUS_VALID_PERIOD, $v ? (int) $v : null);
                    break;
            }
        }
    }
}
