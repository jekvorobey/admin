<?php


namespace App\Core;


use Exception;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Message\Dto\Communication\CommunicationChannelDto;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\CommunicationStatusService;
use Greensight\Message\Services\CommunicationService\CommunicationThemeService;
use Greensight\Message\Services\CommunicationService\CommunicationTypeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use MerchantManagement\Dto\CommissionDto;
use MerchantManagement\Dto\MerchantStatus;
use Pim\Dto\PublicEvent\PublicEventDto;
use Pim\Dto\PublicEvent\PublicEventMediaDto;
use Pim\Dto\PublicEvent\PublicEventTypeDto;
use Pim\Services\PublicEventTypeService\PublicEventTypeService;

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
    private $merchantCommissionTypes = [];
    private $publicEventTypes = [];
    private $publicEventMediaTypes = [];
    private $publicEventMediaCollections = [];

    private $discountTypes = [];

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

    public function loadMerchantCommissionTypes($load = false)
    {
        if ($load) {
            $this->merchantCommissionTypes = [
                'global' => CommissionDto::TYPE_GLOBAL,
                'rating' => CommissionDto::TYPE_RATING,
                'merchant' => CommissionDto::TYPE_MERCHANT,
                'brand' => CommissionDto::TYPE_BRAND,
                'category' => CommissionDto::TYPE_CATEGORY,
                'sku' => CommissionDto::TYPE_SKU,
            ];
        }

        return $this;
    }
    
    public function loadPublicEventTypes(bool $load = false): self
    {
        if ($load) {
            /** @var PublicEventTypeService $publicEventTypeService */
            $publicEventTypeService = resolve(PublicEventTypeService::class);
            /** @var Collection $typesCollection */
            try {
                $typesCollection = $publicEventTypeService
                    ->query()
                    ->get();
            } catch (Exception $e) {
                logger()->error('Error while load public event types', ['exception' => $e]);
                $typesCollection = collect();
            }
            $this->publicEventTypes = $typesCollection
                ->map(function (PublicEventTypeDto $type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'code' => $type->code,
                    ];
                })
                ->all();
        }
        return $this;
    }
    
    public function loadPublicEventMediaTypes(bool $load = false): self
    {
        if ($load) {
            $this->publicEventMediaTypes = [
                'image' => PublicEventMediaDto::TYPE_IMAGE,
                'video' => PublicEventMediaDto::TYPE_VIDEO,
                'youtube' => PublicEventMediaDto::TYPE_YOUTUBE,
            ];
        }
        return $this;
    }
    
    public function loadPublicEventMediaCollections(bool $load = false): self
    {
        if ($load) {
            $this->publicEventMediaCollections = [
                'catalog' => PublicEventDto::MEDIA_CATALOG,
                'detail' => PublicEventDto::MEDIA_DETAIL,
                'gallery' => PublicEventDto::MEDIA_GALLERY,
                'description' => PublicEventDto::MEDIA_DESCRIPTION,
                'history' => PublicEventDto::MEDIA_HISTORY,
            ];
        }
        return $this;
    }

    public function loadDiscountTypes(bool $load = false): self
    {
        if ($load) {
            $this->discountTypes = [
                'offer' => DiscountTypeDto::TYPE_OFFER,
                'bundle' => DiscountTypeDto::TYPE_BUNDLE,
                'brand' => DiscountTypeDto::TYPE_BRAND,
                'category' => DiscountTypeDto::TYPE_CATEGORY,
                'delivery' => DiscountTypeDto::TYPE_DELIVERY,
                'cartTotal' => DiscountTypeDto::TYPE_CART_TOTAL,
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
                'merchantCommissionTypes' => $this->merchantCommissionTypes,
                'publicEventTypes' => $this->publicEventTypes,
                'publicEventMediaTypes' => $this->publicEventMediaTypes,
                'publicEventMediaCollections' => $this->publicEventMediaCollections,

                'discountTypes' => $this->discountTypes,
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