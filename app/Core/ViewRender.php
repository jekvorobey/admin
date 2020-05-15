<?php


namespace App\Core;


use Exception;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use Greensight\Customer\Dto\CustomerBonusDto;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Marketing\Dto\Bonus\BonusDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeOutDto;
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
use Pim\Dto\PublicEvent\PublicEventSprintStatus;
use Pim\Dto\PublicEvent\PublicEventStatus;
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
    private $publicEventStatus = [];
    private $publicEventSprintStatus = [];

    private $discountTypes = [];
    private $promoCodeTypes = [];
    private $promoCodeStatus = [];
    private $bonusValueTypes = [];
    private $bonusTypes = [];
    private $customerBonusStatus = [];

    public function __construct($componentName, $props)
    {
        $this->componentName = $componentName;
        $this->props = $props;
    }
    
    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    public function loadUserRoles($load = false): self
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

    public function loadCustomerStatus($load = false): self
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

    public function loadCommunicationChannelTypes($load = false): self
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

    public function loadCommunicationChannels($load = false): self
    {
        if ($load) {
            $this->communicationChannels = resolve(CommunicationService::class)->channels()->keyBy('id');
        }

        return $this;
    }

    public function loadCommunicationThemes($load = false): self
    {
        if ($load) {
            $this->communicationThemes = resolve(CommunicationThemeService::class)->themes()->keyBy('id');
        }

        return $this;
    }

    public function loadCommunicationStatuses($load = false): self
    {
        if ($load) {
            $this->communicationStatuses = resolve(CommunicationStatusService::class)->statuses()->keyBy('id');
        }

        return $this;
    }

    public function loadCommunicationTypes($load = false): self
    {
        if ($load) {
            $this->communicationTypes = resolve(CommunicationTypeService::class)->types()->keyBy('id');
        }

        return $this;
    }

    public function loadMerchantStatuses($load = false): self
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

    public function loadMerchantCommissionTypes($load = false): self
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

    public function loadPublicEventStatus(bool $load = false): self
    {
        if ($load) {
            $this->publicEventStatus = [
                'created' => PublicEventStatus::CREATED,
                'disabled' => PublicEventStatus::DISABLED,
                'active' => PublicEventStatus::ACTIVE,
            ];
        }
        return $this;
    }

    public function loadPublicEventSprintStatus(bool $load = false): self
    {
        if ($load) {
            $this->publicEventSprintStatus = [
                'created' => PublicEventSprintStatus::CREATED,
                'disabled' => PublicEventSprintStatus::DISABLED,
                'ready' => PublicEventSprintStatus::READY,
                'in_process' => PublicEventSprintStatus::IN_PROCESS,
                'done' => PublicEventSprintStatus::DONE,
            ];
        }
        return $this;
    }

    public function loadDiscountTypes(bool $load = false): self
    {
        if ($load) {
            $this->discountTypes = [
                'offer' => DiscountTypeDto::TYPE_OFFER,
                'bundleOffer' => DiscountTypeDto::TYPE_BUNDLE_OFFER,
                'bundleMasterclass' => DiscountTypeDto::TYPE_BUNDLE_MASTERCLASS,
                'brand' => DiscountTypeDto::TYPE_BRAND,
                'category' => DiscountTypeDto::TYPE_CATEGORY,
                'delivery' => DiscountTypeDto::TYPE_DELIVERY,
                'cartTotal' => DiscountTypeDto::TYPE_CART_TOTAL,
                'anyOffer' => DiscountTypeDto::TYPE_ANY_OFFER,
                'anyBundle' => DiscountTypeDto::TYPE_ANY_BUNDLE,
                'anyBrand' => DiscountTypeDto::TYPE_ANY_BRAND,
                'anyCategory' => DiscountTypeDto::TYPE_ANY_CATEGORY,
            ];
        }
        return $this;
    }

    public function loadPromoCodeTypes(bool $load = false): self
    {
        if ($load) {
            $this->promoCodeTypes = [
                'discount' => PromoCodeOutDto::TYPE_DISCOUNT,
                'delivery' => PromoCodeOutDto::TYPE_DELIVERY,
                'gift' => PromoCodeOutDto::TYPE_GIFT,
                'bonus' => PromoCodeOutDto::TYPE_BONUS,
            ];
        }
        return $this;
    }

    public function loadPromoCodeStatus(bool $load = false): self
    {
        if ($load) {
            $this->promoCodeStatus = [
                'created' => PromoCodeOutDto::STATUS_CREATED,
                'sent' => PromoCodeOutDto::STATUS_SENT,
                'checking' => PromoCodeOutDto::STATUS_ON_CHECKING,
                'active' => PromoCodeOutDto::STATUS_ACTIVE,
                'rejected' => PromoCodeOutDto::STATUS_REJECTED,
                'paused' => PromoCodeOutDto::STATUS_PAUSED,
                'expired' => PromoCodeOutDto::STATUS_EXPIRED,
                'test' => PromoCodeOutDto::STATUS_TEST,
            ];
        }
        return $this;
    }

    /**
     * @param bool $load
     *
     * @return $this
     */
    public function loadBonusTypes(bool $load = false): self
    {
        if ($load) {
            $this->bonusTypes = [
                'offer' => BonusDto::TYPE_OFFER,
                'brand' => BonusDto::TYPE_BRAND,
                'category' => BonusDto::TYPE_CATEGORY,
                'service' => BonusDto::TYPE_SERVICE,
                'cartTotal' => BonusDto::TYPE_CART_TOTAL,
                'anyOffer' => BonusDto::TYPE_ANY_OFFER,
                'anyBrand' => BonusDto::TYPE_ANY_BRAND,
                'anyCategory' => BonusDto::TYPE_ANY_CATEGORY,
                'anyService' => BonusDto::TYPE_ANY_SERVICE,
            ];
        }

        return $this;
    }

    public function loadBonusValueTypes(bool $load = false): self
    {
        if ($load) {
            $this->bonusValueTypes = [
                'percent' => BonusDto::VALUE_TYPE_PERCENT,
                'absolute' => BonusDto::VALUE_TYPE_ABSOLUTE
            ];
        }

        return $this;
    }

    /**
     * @param bool $load
     *
     * @return $this
     */
    public function loadCustomerBonusStatus(bool $load = false): self
    {
        if ($load) {
            $this->customerBonusStatus = [
                'onHold' => CustomerBonusDto::STATUS_ON_HOLD,
                'active' => CustomerBonusDto::STATUS_ACTIVE,
                'expired' => CustomerBonusDto::STATUS_EXPIRED,
                'debited' => CustomerBonusDto::STATUS_DEBITED,
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
                'publicEventSprintStatus' => $this->publicEventSprintStatus,
                'publicEventStatus' => $this->publicEventStatus,

                'discountTypes' => $this->discountTypes,

                'promoCodeTypes' => $this->promoCodeTypes,
                'promoCodeStatus' => $this->promoCodeStatus,
                'bonusValueTypes' => $this->bonusValueTypes,
                'bonusTypes' => $this->bonusTypes,
                'customerBonusStatus' => $this->customerBonusStatus,
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
