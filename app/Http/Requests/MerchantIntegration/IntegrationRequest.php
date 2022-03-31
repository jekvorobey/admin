<?php

namespace App\Http\Requests\MerchantIntegration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\Integration\ExtSystemDriver;

class IntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'merchantId' => 'required|int',
            'token' => [
                Rule::requiredIf(function () {
                    return $this->input('driver') === ExtSystemDriver::DRIVER_MOY_SKLAD && !$this->input('login');
                }),
            ],
            'login' => [
                Rule::requiredIf(function () {
                    return $this->input('driver') === ExtSystemDriver::DRIVER_MOY_SKLAD && !$this->input('token');
                }),
            ],
            'password' => [
                Rule::requiredIf(function () {
                    return $this->input('driver') === ExtSystemDriver::DRIVER_MOY_SKLAD && $this->input('login');
                }),
            ],
            'settingPriceValue' => [
                Rule::requiredIf(function () {
                    return $this->input('driver') === ExtSystemDriver::DRIVER_MOY_SKLAD;
                }),
            ],
            'settingOrganizationValue' => [
                Rule::requiredIf(function () {
                    return $this->input('driver') === ExtSystemDriver::DRIVER_MOY_SKLAD;
                }),
            ],
            'host' => [
                Rule::requiredIf(function () {
                    return $this->input('driver') === ExtSystemDriver::DRIVER_FILE_SHARING;
                }),
            ],
            'port' => [
                Rule::requiredIf(function () {
                    return $this->input('driver') === ExtSystemDriver::DRIVER_FILE_SHARING;
                }),
                'integer',
            ],
            'driver' => [
                'required',
                'integer',
                Rule::in(array_keys(ExtSystemDriver::allDrivers())),
            ],
            'integrationParams' => 'required|array',
        ];
    }
}
