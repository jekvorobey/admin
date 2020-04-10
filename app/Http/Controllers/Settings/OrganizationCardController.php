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

        if ($data['short_name']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_SHORT_NAME, $data['short_name']);
        }
        if ($data['full_name']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_FULL_NAME, $data['full_name']);
        }

        if ($data['inn']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_INN, $data['inn']);
        }
        if ($data['kpp']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_KPP, $data['kpp']);
        }
        if ($data['okpo']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_OKPO, $data['okpo']);
        }
        if ($data['ogrn']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_OGRN, $data['ogrn']);
        }

        if ($data['fact_address']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_FACT_ADDRESS, $data['fact_address']);
        }
        if ($data['legal_address']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_LEGAL_ADDRESS, $data['legal_address']);
        }

        if ($data['payment_account']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_PAYMENT_ACCOUNT, $data['payment_account']);
        }
        if ($data['bank_bik']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_BANK_BIK, $data['bank_bik']);
        }
        if ($data['bank_name']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_BANK_NAME, $data['bank_name']);
        }
        if ($data['correspondent_account']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CORRESPONDENT_ACCOUNT, $data['correspondent_account']);
        }

        if ($data['ceo_last_name']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CEO_LAST_NAME, $data['ceo_last_name']);
        }
        if ($data['ceo_first_name']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CEO_FIRST_NAME, $data['ceo_first_name']);
        }
        if ($data['ceo_middle_name']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CEO_MIDDLE_NAME, $data['ceo_middle_name']);
        }
        if ($data['ceo_document_number']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CEO_DOCUMENT_NUMBER, $data['ceo_document_number']);
        }

        if ($data['contact_centre_phone']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_CONTACT_CENTRE_PHONE, $data['contact_centre_phone']);
        }
        if ($data['social_phone']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_SOCIAL_PHONE, $data['social_phone']);
        }
        if ($data['email_for_merchant']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_EMAIL_FOR_MERCHANT, $data['email_for_merchant']);
        }
        if ($data['common_email']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_COMMON_EMAIL, $data['common_email']);
        }
        if ($data['email_for_claim']) {
            $optionService->put(OptionDto::KEY_ORGANIZATION_CARD_EMAIL_FOR_CLAIM, $data['email_for_claim']);
        }

        return response()->json();
    }
}