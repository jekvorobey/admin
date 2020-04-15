<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Cms\Dto\OptionDto;
use Cms\Services\OptionService\OptionService;

class OrganizationCardController extends Controller
{
    public function index(OptionService $optionService)
    {
        $this->title = 'Карточка организации iBT.Studio';

        return $this->render('Settings/OrganizationCard', [
            'short_name' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_SHORT_NAME),
            'full_name' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_FULL_NAME),

            'inn' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_INN),
            'kpp' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_KPP),
            'okpo' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_OKPO),
            'ogrn' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_OGRN),

            'fact_address' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_FACT_ADDRESS),
            'legal_address' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_LEGAL_ADDRESS),

            'payment_account' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_PAYMENT_ACCOUNT),
            'bank_bik' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_BANK_BIK),
            'bank_name' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_BANK_NAME),
            'correspondent_account' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_CORRESPONDENT_ACCOUNT),

            'ceo_last_name' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_CEO_LAST_NAME),
            'ceo_first_name' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_CEO_FIRST_NAME),
            'ceo_middle_name' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_CEO_MIDDLE_NAME),
            'ceo_document_number' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_CEO_DOCUMENT_NUMBER),

            'contact_centre_phone' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_CONTACT_CENTRE_PHONE),
            'social_phone' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_SOCIAL_PHONE),
            'email_for_merchant' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_EMAIL_FOR_MERCHANT),
            'common_email' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_COMMON_EMAIL),
            'email_for_claim' => $optionService->get(OptionDto::KEY_ORGANIZATION_CARD_EMAIL_FOR_CLAIM),
        ]);
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

            'contact_centre_phone' => 'regex:/\+\d\(\d\d\d\)\s\d\d\d-\d\d-\d\d/',
            'social_phone' => 'regex:/\+\d\(\d\d\d\)\s\d\d\d-\d\d-\d\d/',
            'email_for_merchant' => 'email',
            'common_email' => 'email',
            'email_for_claim' => 'email',
        ]);

        if (array_key_exists('short_name', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_SHORT_NAME, $data['short_name']);
        }
        if (array_key_exists('full_name', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_FULL_NAME, $data['full_name']);
        }

        if (array_key_exists('inn', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_INN, $data['inn']);
        }
        if (array_key_exists('kpp', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_KPP, $data['kpp']);
        }
        if (array_key_exists('okpo', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_OKPO, $data['okpo']);
        }
        if (array_key_exists('ogrn', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_OGRN, $data['ogrn']);
        }

        if (array_key_exists('fact_address', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_FACT_ADDRESS, $data['fact_address']);
        }
        if (array_key_exists('legal_address', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_LEGAL_ADDRESS, $data['legal_address']);
        }

        if (array_key_exists('payment_account', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_PAYMENT_ACCOUNT, $data['payment_account']);
        }
        if (array_key_exists('bank_bik', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_BANK_BIK, $data['bank_bik']);
        }
        if (array_key_exists('bank_name', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_BANK_NAME, $data['bank_name']);
        }
        if (array_key_exists('correspondent_account', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CORRESPONDENT_ACCOUNT, $data['correspondent_account']);
        }

        if (array_key_exists('ceo_last_name', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CEO_LAST_NAME, $data['ceo_last_name']);
        }
        if (array_key_exists('ceo_first_name', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CEO_FIRST_NAME, $data['ceo_first_name']);
        }
        if (array_key_exists('ceo_middle_name', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CEO_MIDDLE_NAME, $data['ceo_middle_name']);
        }
        if (array_key_exists('ceo_document_number', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CEO_DOCUMENT_NUMBER, $data['ceo_document_number']);
        }

        if (array_key_exists('contact_centre_phone', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CONTACT_CENTRE_PHONE, $data['contact_centre_phone']);
        }
        if (array_key_exists('social_phone', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_SOCIAL_PHONE, $data['social_phone']);
        }
        if (array_key_exists('email_for_merchant', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_EMAIL_FOR_MERCHANT, $data['email_for_merchant']);
        }
        if (array_key_exists('common_email', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_COMMON_EMAIL, $data['common_email']);
        }
        if (array_key_exists('email_for_claim', $data)) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_EMAIL_FOR_CLAIM, $data['email_for_claim']);
        }

        return response()->json();
    }
}