<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\OptionDto;
use Cms\Services\OptionService\OptionService;

class OrganizationCardController extends Controller
{
    public const MAPPING_KEYS = [
        'short_name' => OptionDto::KEY_ORGANIZATION_CARD_SHORT_NAME,
        'full_name' => OptionDto::KEY_ORGANIZATION_CARD_FULL_NAME,

        'inn' => OptionDto::KEY_ORGANIZATION_CARD_INN,
        'kpp' => OptionDto::KEY_ORGANIZATION_CARD_KPP,
        'okpo' => OptionDto::KEY_ORGANIZATION_CARD_OKPO,
        'ogrn' => OptionDto::KEY_ORGANIZATION_CARD_OGRN,

        'fact_address' => OptionDto::KEY_ORGANIZATION_CARD_FACT_ADDRESS,
        'legal_address' => OptionDto::KEY_ORGANIZATION_CARD_LEGAL_ADDRESS,

        'payment_account' => OptionDto::KEY_ORGANIZATION_CARD_PAYMENT_ACCOUNT,
        'bank_bik' => OptionDto::KEY_ORGANIZATION_CARD_BANK_BIK,
        'bank_name' => OptionDto::KEY_ORGANIZATION_CARD_BANK_NAME,
        'correspondent_account' => OptionDto::KEY_ORGANIZATION_CARD_CORRESPONDENT_ACCOUNT,

        'ceo_last_name' => OptionDto::KEY_ORGANIZATION_CARD_CEO_LAST_NAME,
        'ceo_first_name' => OptionDto::KEY_ORGANIZATION_CARD_CEO_FIRST_NAME,
        'ceo_middle_name' => OptionDto::KEY_ORGANIZATION_CARD_CEO_MIDDLE_NAME,
        'ceo_document_number' => OptionDto::KEY_ORGANIZATION_CARD_CEO_DOCUMENT_NUMBER,

        'logistics_manager_last_name' => OptionDto::KEY_ORGANIZATION_CARD_LOGISTICS_MANAGER_LAST_NAME,
        'logistics_manager_first_name' => OptionDto::KEY_ORGANIZATION_CARD_LOGISTICS_MANAGER_FIRST_NAME,
        'logistics_manager_middle_name' => OptionDto::KEY_ORGANIZATION_CARD_LOGISTICS_MANAGER_MIDDLE_NAME,
        'logistics_manager_phone' => OptionDto::KEY_ORGANIZATION_CARD_LOGISTICS_MANAGER_PHONE,
        'logistics_manager_email' => OptionDto::KEY_ORGANIZATION_CARD_LOGISTICS_MANAGER_EMAIL,

        'contact_centre_phone' => OptionDto::KEY_ORGANIZATION_CARD_CONTACT_CENTRE_PHONE,
        'social_phone' => OptionDto::KEY_ORGANIZATION_CARD_SOCIAL_PHONE,
        'email_for_merchant' => OptionDto::KEY_ORGANIZATION_CARD_EMAIL_FOR_MERCHANT,
        'common_email' => OptionDto::KEY_ORGANIZATION_CARD_COMMON_EMAIL,
        'email_for_claim' => OptionDto::KEY_ORGANIZATION_CARD_EMAIL_FOR_CLAIM,
    ];

    /**
     * Страница Карточка организации
     * @param OptionService $optionService
     * @return mixed
     * @throws CmsException
     */
    public function index(OptionService $optionService)
    {
        $this->title = 'Карточка организации iBT.Studio';

        $organizationCardKeys = array_values(self::MAPPING_KEYS);
        $options = $optionService->get($organizationCardKeys);
        $data = collect(self::MAPPING_KEYS)
            ->map(function ($item) use ($options) {
                return $options[$item];
            })
            ->all();

        return $this->render('Settings/OrganizationCard', $data);
    }

    public function update(OptionService $optionService)
    {
        $data = $this->validate(request(), [
            'short_name' => 'string',
            'full_name' => 'string',

            'inn' => 'string|size:10',
            'kpp' => 'string|size:9',
            'okpo' => 'string|size:8',
            'ogrn' => 'string|size:13',

            'fact_address' => 'string',
            'legal_address' => 'string',

            'payment_account' => 'string|size:20',
            'bank_bik' => 'string|size:9',
            'bank_name' => 'string',
            'correspondent_account' => 'string|size:20',

            'ceo_last_name' => 'string',
            'ceo_first_name' => 'string',
            'ceo_middle_name' => 'string',
            'ceo_document_number' => 'string',

            'logistics_manager_last_name' => 'string',
            'logistics_manager_first_name' => 'string',
            'logistics_manager_middle_name' => 'string',
            'logistics_manager_phone' => 'regex:/\+\d\(\d{3}\)\s\d{3}-\d{2}-\d{2}/',
            'logistics_manager_email' => 'email',

            'contact_centre_phone' => 'regex:/\+\d\(\d\d\d\)\s\d\d\d-\d\d-\d\d/',
            'social_phone' => 'regex:/\+\d\(\d\d\d\)\s\d\d\d-\d\d-\d\d/',
            'email_for_merchant' => 'email',
            'common_email' => 'email',
            'email_for_claim' => 'email',
        ]);

        $data = collect($data)
            ->map(function ($item, $key) {
                return [
                    'newKey' => self::MAPPING_KEYS[$key],
                    'value' => $item,
                ];
            })
            ->keyBy('newKey')
            ->map(function ($item) {
                return $item['value'];
            })
            ->all();

        $optionService->put($data);

        return response()->json();
    }
}
