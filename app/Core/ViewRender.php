<?php


namespace App\Core;


use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Message\Dto\Communication\CommunicationChannelDto;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\CommunicationStatusService;
use Greensight\Message\Services\CommunicationService\CommunicationThemeService;
use Greensight\Message\Services\CommunicationService\CommunicationTypeService;
use Illuminate\Support\Facades\View;
use MerchantManagement\Dto\MerchantStatus;

class ViewRender
{
    private $componentName;
    private $props;
    private $title;

    private $userRoles = [];

    private $customerStatus = [];
    private $customerStatusName = [];
    private $customerStatusByRole = [];

    private $communicationChannelTypes = [];
    private $communicationChannels = [];
    private $communicationThemes = [];
    private $communicationStatuses = [];
    private $communicationTypes = [];

    private $merchantStatuses = [];

    public function __construct($componentName, $props)
    {
        $this->componentName = $componentName;
        $this->props = $props;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function loadUserRoles($load = false)
    {
        if ($load) {
            $this->userRoles = [
                'admin' => [
                    'super' => UserDto::ADMIN__SUPER,
                    'admin' => UserDto::ADMIN__ADMIN,
                    'manager_merchant' => UserDto::ADMIN__MANAGER_MERCHANT,
                    'manager_client' => UserDto::ADMIN__MANAGER_CLIENT,
                ],
                'mas' => [
                    'merchant_operator' => UserDto::MAS__MERCHANT_OPERATOR,
                    'merchant_admin' => UserDto::MAS__MERCHANT_ADMIN,
                ],
                'i_commerce_ml' => [
                    'external_system' => UserDto::I_COMMERCE_ML__EXTERNAL_SYSTEM,
                ],
                'showcase' => [
                    'professional' => UserDto::SHOWCASE__PROFESSIONAL,
                    'referral_partner' => UserDto::SHOWCASE__REFERRAL_PARTNER,
                ],
            ];
        }

        return $this;
    }

    public function loadCustomerStatus($load = false)
    {
        if ($load) {
            $this->customerStatus = [
                'created' => CustomerDto::STATUS_CREATED,
                'new' => CustomerDto::STATUS_NEW,
                'consideration' => CustomerDto::STATUS_CONSIDERATION,
                'rejected' => CustomerDto::STATUS_REJECTED,
                'active' => CustomerDto::STATUS_ACTIVE,
                'problem' => CustomerDto::STATUS_PROBLEM,
                'block' => CustomerDto::STATUS_BLOCK,
                'potential_rp' => CustomerDto::STATUS_POTENTIAL_RP,
                'temporarily_suspended' => CustomerDto::STATUS_TEMPORARILY_SUSPENDED,
            ];
            $this->customerStatusName = CustomerDto::statusesName();
            $this->customerStatusByRole = CustomerDto::statusesByRole();
        }

        return $this;
    }

    public function loadCommunicationChannelTypes($load = false)
    {
        if ($load) {
            $this->communicationChannelTypes = [
                'internal_message' => CommunicationChannelDto::CHANNEL_INTERNAL_MESSAGE,
                'infinity' => CommunicationChannelDto::CHANNEL_INFINITY,
                'smsc' => CommunicationChannelDto::CHANNEL_SMSC,
                'livetex_viber' => CommunicationChannelDto::CHANNEL_LIVETEX_VIBER,
                'livetex_telegram' => CommunicationChannelDto::CHANNEL_LIVETEX_TELEGRAM,
                'livetex_fb' => CommunicationChannelDto::CHANNEL_LIVETEX_FB,
                'livetex_vk' => CommunicationChannelDto::CHANNEL_LIVETEX_VK,
                'internal_email' => CommunicationChannelDto::CHANNEL_INTERNAL_EMAIL,
            ];
        }

        return $this;
    }

    public function loadCommunicationChannels($load = false)
    {
        if ($load) {
            $this->communicationChannels = resolve(CommunicationService::class)->channels()->keyBy('id');
        }

        return $this;
    }

    public function loadCommunicationThemes($load = false)
    {
        if ($load) {
            $this->communicationThemes = resolve(CommunicationThemeService::class)->themes()->keyBy('id');
        }

        return $this;
    }

    public function loadCommunicationStatuses($load = false)
    {
        if ($load) {
            $this->communicationStatuses = resolve(CommunicationStatusService::class)->statuses()->keyBy('id');
        }

        return $this;
    }

    public function loadCommunicationTypes($load = false)
    {
        if ($load) {
            $this->communicationTypes = resolve(CommunicationTypeService::class)->types()->keyBy('id');
        }

        return $this;
    }

    public function loadMerchantStatuses($load = false)
    {
        if ($load) {
            $this->merchantStatuses = [
                'created' => MerchantStatus::STATUS_CREATED,
                'review' => MerchantStatus::STATUS_REVIEW,
                'cancel' => MerchantStatus::STATUS_CANCEL,
                'terms' => MerchantStatus::STATUS_TERMS,
                'activation' => MerchantStatus::STATUS_ACTIVATION,
                'work' => MerchantStatus::STATUS_WORK,
                'stop' => MerchantStatus::STATUS_STOP,
                'close' => MerchantStatus::STATUS_CLOSE,
            ];
        }

        return $this;
    }

    public function render()
    {
        return View::component(
            $this->componentName,
            $this->props,
            [
                'menu' => Menu::getMenuItems(),
                'user' => [
                    'isGuest' => resolve(TokenStore::class)->token() == null,
                    'isSuper' => resolve(RequestInitiator::class)->hasRole(UserDto::ADMIN__SUPER),
                ],

                'userRoles' => $this->userRoles,

                'customerStatusByRole' => $this->customerStatusByRole,
                'customerStatusName' => $this->customerStatusName,
                'customerStatus' => $this->customerStatus,

                'communicationChannelTypes' => $this->communicationChannelTypes,
                'communicationChannels' => $this->communicationChannels,
                'communicationThemes' => $this->communicationThemes,
                'communicationStatuses' => $this->communicationStatuses,
                'communicationTypes' => $this->communicationTypes,

                'merchantStatuses' => $this->merchantStatuses,
            ],
            [
                'title' => $this->title,
                'assets' => $this->getAssets(),
            ]
        );
    }

    private function getAssets()
    {
        if (frontend()->isInDevMode()) {
            return [
                'js' => [
                    "scripts/{$this->componentName}.js",
                ],
                'css' => [],
            ];
        } else {
            $webPack = json_decode(file_get_contents(public_path('/assets/webpack-assets.json')), true);
            $js = [];
            $css = [];

            if (isset($webPack[$this->componentName])) {
                if (isset($webPack[$this->componentName]['js'])) {
                    $js = array_filter((array)$webPack[$this->componentName]['js']);
                }
                if (isset($webPack[$this->componentName]['css'])) {
                    $css = array_filter((array)$webPack[$this->componentName]['css']);
                }
            }
            return [
                'js' => $js,
                'css' => $css,
            ];
        }
    }
}