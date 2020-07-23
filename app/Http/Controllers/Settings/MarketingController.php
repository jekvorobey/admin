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
        $this->title = 'Маркетинговые инструменты iBT.Studio';
        $marketingOptionService = resolve(MarketingOptionService::class);
        $customerOptionService = resolve(CustomerOptionService::class);
        $activationBonus = $customerOptionService->get(CustomerOptionDto::KEY_ACTIVATION_BONUS);
        $bonusExpireDaysNotify = $customerOptionService->get(CustomerOptionDto::BONUS_EXPIRE_DAYS_NOTIFY);
        return $this->render('Settings/Marketing', [
            'min_withdrawal_amount' => $customerOptionService->get(CustomerOptionDto::KEY_REFERRAL_MIN_WITHDRAWAL_LIMIT),
            'max_withdrawal_amount' => $customerOptionService->get(CustomerOptionDto::KEY_REFERRAL_MAX_WITHDRAWAL_LIMIT),
            'bonus_per_rubles' => $marketingOptionService->get(MarketingOptionDto::KEY_BONUS_PER_RUBLES),
            'roles_available_for_bonuses' => $marketingOptionService->get(MarketingOptionDto::KEY_ROLES_AVAILABLE_FOR_BONUSES, []),
            'order_activation_bonus_delay' => $marketingOptionService->get(MarketingOptionDto::KEY_ORDER_ACTIVATION_BONUS_DELAY, 0),
            'max_debit_percentage_for_product' => $marketingOptionService->get(MarketingOptionDto::KEY_MAX_DEBIT_PERCENTAGE_FOR_PRODUCT, 0),
            'max_debit_percentage_for_order' => $marketingOptionService->get(MarketingOptionDto::KEY_MAX_DEBIT_PERCENTAGE_FOR_ORDER, 0),
            'activation_bonus_name' => $activationBonus['name'] ?? null,
            'activation_bonus_value' => $activationBonus['value'] ?? null,
            'activation_bonus_valid_period' => $activationBonus['valid_period'] ?? null,
            'activation_bonus' => $activationBonus ?? null,
            'bonus_expire_days_notify' => $bonusExpireDaysNotify ?? null,
            'roles' => Helpers::getOptionRoles(false),
        ]);
    }

    public function update()
    {
        $data = $this->validate(request(), [
            'min_withdrawal_amount' => 'integer',
            'max_withdrawal_amount' => 'integer',
            'bonus_per_rubles' => 'numeric|gte:0',
            'order_activation_bonus_delay' => 'integer|gte:0',
            'max_debit_percentage_for_product' => 'integer|between:0,100',
            'max_debit_percentage_for_order' => 'integer|between:0,100',
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
                case 'min_withdrawal_amount':
                    $customerOptionService->put(CustomerOptionDto::KEY_REFERRAL_MIN_WITHDRAWAL_LIMIT, (int) $v);
                    break;
                case 'max_withdrawal_amount':
                    $customerOptionService->put(CustomerOptionDto::KEY_REFERRAL_MAX_WITHDRAWAL_LIMIT, (int) $v);
                    break;
                case 'bonus_per_rubles':
                    $marketingOptionService->put(MarketingOptionDto::KEY_BONUS_PER_RUBLES, (float) $v);
                    break;
                case 'roles_available_for_bonuses':
                    $marketingOptionService->put(MarketingOptionDto::KEY_ROLES_AVAILABLE_FOR_BONUSES, $v);
                    break;
                case 'order_activation_bonus_delay':
                    $marketingOptionService->put(MarketingOptionDto::KEY_ORDER_ACTIVATION_BONUS_DELAY, (int) $v);
                    break;
                case 'max_debit_percentage_for_product':
                    $marketingOptionService->put(MarketingOptionDto::KEY_MAX_DEBIT_PERCENTAGE_FOR_PRODUCT, (int) $v);
                    break;
                case 'max_debit_percentage_for_order':
                    $marketingOptionService->put(MarketingOptionDto::KEY_MAX_DEBIT_PERCENTAGE_FOR_ORDER, (int) $v);
                    break;
                case 'activation_bonus':
                    $customerOptionService->put(CustomerOptionDto::KEY_ACTIVATION_BONUS, $v);
                    break;
                case 'bonus_expire_days_notify':
                    $customerOptionService->put(CustomerOptionDto::BONUS_EXPIRE_DAYS_NOTIFY, $v ? (int)$v : null);
                    break;
            }
        }
    }
}
