<?php

namespace App\Http\Controllers\Settings;

use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\Customer\Dto\OptionDto as CustomerOptionDto;
use Greensight\Marketing\Dto\Option\OptionDto as MarketingOptionDto;
use Greensight\Marketing\Services\OptionService\OptionService as MarketingOptionService;
use Greensight\Customer\Services\OptionService\OptionService as CustomerOptionService;
use Illuminate\Validation\Rule;


class MarketingController extends Controller
{
    public function index()
    {
        $this->title = 'Маректинговые инструменты iBT.Studio';
        $marketingOptionService = resolve(MarketingOptionService::class);
        $customerOptionService = resolve(CustomerOptionService::class);
        $activationBonus = $customerOptionService->get(CustomerOptionDto::KEY_ACTIVATION_BONUS);
        return $this->render('Settings/Marketing', [
            'bonus_per_rubles' => $marketingOptionService->get(MarketingOptionDto::KEY_BONUS_PER_RUBLES),
            'roles_available_for_bonuses' => $marketingOptionService->get(MarketingOptionDto::KEY_ROLES_AVAILABLE_FOR_BONUSES, []),
            'order_activation_bonus_delay' => $marketingOptionService->get(MarketingOptionDto::KEY_ORDER_ACTIVATION_BONUS_DELAY, 0),
            'activation_bonus_name' => $activationBonus['name'] ?? null,
            'activation_bonus_value' => $activationBonus['value'] ?? null,
            'activation_bonus_valid_period' => $activationBonus['valid_period'] ?? null,
            'activation_bonus' => $activationBonus ?? null,

            'roles' => Helpers::getOptionRoles(false),
        ]);
    }

    public function update()
    {
        $data = $this->validate(request(), [
            'bonus_per_rubles' => 'numeric|gte:0',
            'order_activation_bonus_delay' => 'integer|gte:0',
            'roles_available_for_bonuses'  => 'array',
            'roles_available_for_bonuses.*'  => [
                Rule::in([
                    UserDto::SHOWCASE__PROFESSIONAL,
                    UserDto::SHOWCASE__REFERRAL_PARTNER
                ])],
            'activation_bonus' => 'array|nullable',
            'activation_bonus.name'  => 'string|nullable',
            'activation_bonus.value'  => 'integer|gt:0|nullable',
            'activation_bonus.valid_period'  => 'integer|gte:0|nullable',
        ]);

        $marketingOptionService = resolve(MarketingOptionService::class);
        $customerOptionService = resolve(CustomerOptionService::class);
        foreach ($data as $key => $v) {
            switch ($key) {
                case 'bonus_per_rubles':
                    $marketingOptionService->put(MarketingOptionDto::KEY_BONUS_PER_RUBLES, (float) $v);
                    break;
                case 'roles_available_for_bonuses':
                    $marketingOptionService->put(MarketingOptionDto::KEY_ROLES_AVAILABLE_FOR_BONUSES, $v);
                    break;
                case 'order_activation_bonus_delay':
                    $marketingOptionService->put(MarketingOptionDto::KEY_ORDER_ACTIVATION_BONUS_DELAY, (int) $v);
                    break;
                case 'activation_bonus':
                    $customerOptionService->put(CustomerOptionDto::KEY_ACTIVATION_BONUS, $v);
                    break;
            }
        }
    }
}
