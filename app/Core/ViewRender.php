<?php

namespace App\Core;

use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\PermissionDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\CommonMsa\Services\RoleService\RoleService;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use Greensight\Customer\Dto\CustomerBonusDto;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Marketing\Dto\Bonus\BonusDto;
use Greensight\Marketing\Dto\Discount\DiscountTypeDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeOutDto;
use Greensight\Message\Dto\Communication\CommunicationChannelDto;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\CommunicationStatusService;
use Greensight\Message\Services\CommunicationService\CommunicationThemeService;
use Greensight\Message\Services\CommunicationService\CommunicationTypeService;
use Greensight\Oms\Dto\BasketDto;
use Greensight\Oms\Dto\Delivery\CargoStatus;
use Greensight\Oms\Dto\Delivery\DeliveryStatus;
use Greensight\Oms\Dto\Delivery\ShipmentStatus;
use Greensight\Oms\Dto\DeliveryType;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Dto\Payment\PaymentMethod;
use Greensight\Oms\Dto\Payment\PaymentStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use MerchantManagement\Dto\CommissionDto;
use MerchantManagement\Dto\VatDto;
use MerchantManagement\Dto\MerchantStatus;
use Pim\Dto\Offer\OfferSaleStatus;
use Pim\Dto\PropertyDto;
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

    private array $userRoles = [];
    private array $blocks = [];
    private array $blockPermissions = [];

    private array $customerStatus = [];
    private array $customerStatusName = [];
    private array $customerStatusByRole = [];

    private array $communicationChannelTypes = [];
    private array $communicationChannels = [];
    private array $communicationThemes = [];
    private array $communicationStatuses = [];
    private array $communicationTypes = [];

    private array $merchantStatuses = [];
    private array $merchantCommissionTypes = [];
    private array $merchantVatTypes = [];

    private array $publicEventTypes = [];
    private array $publicEventMediaTypes = [];
    private array $publicEventMediaCollections = [];
    private array $publicEventStatus = [];
    private array $publicEventSprintStatus = [];

    private array $discountTypes = [];
    private array $promoCodeTypes = [];
    private array $promoCodeStatus = [];
    private array $bonusValueTypes = [];
    private array $bonusTypes = [];
    private array $customerBonusStatus = [];

    private array $orderStatuses = [];
    private array $basketTypes = [];
    private array $paymentStatuses = [];
    private array $paymentMethods = [];
    private array $deliveryStatuses = [];
    private array $shipmentStatuses = [];
    private array $cargoStatuses = [];
    private array $deliveryTypes = [];
    private array $deliveryMethods = [];
    private array $deliveryServices = [];

    private array $offerAllSaleStatuses = [];
    private array $offerCountdownSaleStatuses = [];

    private $propertyTypes = [];

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
        $roles = resolve(RoleService::class)->roles();
        if ($load) {
            $this->userRoles = [
                'admin' => [
                    $roles->where('front', Front::FRONT_ADMIN)->pluck('id')->toArray(),
                ],
                'mas' => [
                    $roles->where('front', Front::FRONT_MAS)->pluck('id')->toArray(),
                ],
                'i_commerce_ml' => [
                    $roles->where('front', Front::FRONT_I_COMMERCE_ML)->pluck('id')->toArray(),
                ],
                'showcase' => [
                    $roles->where('front', Front::FRONT_SHOWCASE)->pluck('id')->toArray(),
                ],
            ];
        }

        return $this;
    }

    public function loadBlocks($load = false): self
    {
        if ($load) {
            $this->blocks = BlockDto::allBlocks();
        }

        return $this;
    }

    public function loadBlockPermissions($load = false): self
    {
        $view = resolve(RequestInitiator::class)->blocksByPermission(PermissionDto::PERMISSION_VIEW);
        $update = resolve(RequestInitiator::class)->blocksByPermission(PermissionDto::PERMISSION_UPDATE);

        if ($load) {
            $this->blockPermissions = [
                'view' => array_merge($view, $update),
                'update' => $update,
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

    public function loadMerchantVatTypes($load = false): self
    {
        if ($load) {
            $this->merchantVatTypes = [
                'global' => VatDto::TYPE_GLOBAL,
                'merchant' => VatDto::TYPE_MERCHANT,
                'brand' => VatDto::TYPE_BRAND,
                'category' => VatDto::TYPE_CATEGORY,
                'sku' => VatDto::TYPE_SKU,
            ];
        }

        return $this;
    }

    public function loadPublicEventTypes(bool $load = false): self
    {
        if ($load) {
            /** @var PublicEventTypeService $publicEventTypeService */
            $publicEventTypeService = resolve(PublicEventTypeService::class);
            try {
                /** @var Collection $typesCollection */
                $typesCollection = $publicEventTypeService
                    ->query()
                    ->get();
            } catch (\Throwable $e) {
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
                'masterclass' => DiscountTypeDto::TYPE_MASTERCLASS,
                'delivery' => DiscountTypeDto::TYPE_DELIVERY,
                'cartTotal' => DiscountTypeDto::TYPE_CART_TOTAL,
                'anyOffer' => DiscountTypeDto::TYPE_ANY_OFFER,
                'anyBundle' => DiscountTypeDto::TYPE_ANY_BUNDLE,
                'anyBrand' => DiscountTypeDto::TYPE_ANY_BRAND,
                'anyCategory' => DiscountTypeDto::TYPE_ANY_CATEGORY,
                'anyMasterclass' => DiscountTypeDto::TYPE_ANY_MASTERCLASS,
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
                'absolute' => BonusDto::VALUE_TYPE_ABSOLUTE,
            ];
        }

        return $this;
    }

    /**
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

    /**
     * @return $this
     */
    public function loadOrderStatuses(bool $load = false): self
    {
        if ($load) {
            $mapOrderStatuses = [
                OrderStatus::CREATED => 'created',
                OrderStatus::AWAITING_CHECK => 'awaitingCheck',
                OrderStatus::CHECKING => 'checking',
                OrderStatus::AWAITING_CONFIRMATION => 'awaitingConfirmation',
                OrderStatus::IN_PROCESSING => 'inProcessing',
                OrderStatus::TRANSFERRED_TO_DELIVERY => 'transferredToDelivery',
                OrderStatus::DELIVERING => 'delivering',
                OrderStatus::READY_FOR_RECIPIENT => 'readyForRecipient',
                OrderStatus::DONE => 'done',
                OrderStatus::RETURNED => 'returned',
                OrderStatus::PRE_ORDER => 'preOrder',
            ];
            foreach (OrderStatus::allStatuses() as $id => $status) {
                if (!isset($mapOrderStatuses[$id])) {
                    continue;
                }
                $this->orderStatuses[$mapOrderStatuses[$id]] = $status->toArray();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function loadPaymentStatuses(bool $load = false): self
    {
        if ($load) {
            $mapPaymentStatuses = [
                PaymentStatus::NOT_PAID => 'notPaid',
                PaymentStatus::PAID => 'paid',
                PaymentStatus::TIMEOUT => 'timeout',
                PaymentStatus::HOLD => 'hold',
                PaymentStatus::ERROR => 'error',
                PaymentStatus::WAITING => 'waiting',
            ];
            foreach (PaymentStatus::allStatuses() as $id => $status) {
                if (!isset($mapPaymentStatuses[$id])) {
                    continue;
                }
                $this->paymentStatuses[$mapPaymentStatuses[$id]] = $status->toArray();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function loadPaymentMethods(bool $load = false): self
    {
        if ($load) {
            $mapPaymentMethods = [
                PaymentMethod::ONLINE => 'online',
            ];
            foreach (PaymentMethod::allMethods() as $id => $method) {
                if (!isset($mapPaymentMethods[$id])) {
                    continue;
                }
                $this->paymentMethods[$mapPaymentMethods[$id]] = $method->toArray();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function loadDeliveryStatuses(bool $load = false): self
    {
        if ($load) {
            $mapDeliveryStatuses = [
                DeliveryStatus::CREATED => 'created',
                DeliveryStatus::AWAITING_CHECK => 'awaitingCheck',
                DeliveryStatus::CHECKING => 'checking',
                DeliveryStatus::AWAITING_CONFIRMATION => 'awaitingConfirmation',
                DeliveryStatus::ASSEMBLING => 'assembling',
                DeliveryStatus::ASSEMBLED => 'assembled',
                DeliveryStatus::SHIPPED => 'shipped',
                DeliveryStatus::ON_POINT_IN => 'onPointIn',
                DeliveryStatus::ARRIVED_AT_DESTINATION_CITY => 'arrivedAtDestinationCity',
                DeliveryStatus::ON_POINT_OUT => 'onPointOut',
                DeliveryStatus::READY_FOR_RECIPIENT => 'readyForRecipient',
                DeliveryStatus::DELIVERING => 'delivering',
                DeliveryStatus::DONE => 'done',
                DeliveryStatus::CANCELLATION_EXPECTED => 'cancellationExpected',
                DeliveryStatus::RETURN_EXPECTED_FROM_CUSTOMER => 'returnExpectedFromCustomer',
                DeliveryStatus::RETURNED => 'returned',
                DeliveryStatus::PRE_ORDER => 'preOrder',
            ];
            foreach (DeliveryStatus::allStatuses() as $id => $status) {
                if (!isset($mapDeliveryStatuses[$id])) {
                    continue;
                }
                $this->deliveryStatuses[$mapDeliveryStatuses[$id]] = $status->toArray();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function loadShipmentStatuses(bool $load = false): self
    {
        if ($load) {
            $mapShipmentStatuses = [
                ShipmentStatus::CREATED => 'created',
                ShipmentStatus::AWAITING_CHECK => 'awaitingCheck',
                ShipmentStatus::CHECKING => 'checking',
                ShipmentStatus::AWAITING_CONFIRMATION => 'awaitingConfirmation',
                ShipmentStatus::ASSEMBLING => 'assembling',
                ShipmentStatus::ASSEMBLED => 'assembled',
                ShipmentStatus::SHIPPED => 'shipped',
                ShipmentStatus::ON_POINT_IN => 'onPointIn',
                ShipmentStatus::ARRIVED_AT_DESTINATION_CITY => 'arrivedAtDestinationCity',
                ShipmentStatus::ON_POINT_OUT => 'onPointOut',
                ShipmentStatus::READY_FOR_RECIPIENT => 'readyForRecipient',
                ShipmentStatus::DELIVERING => 'delivering',
                ShipmentStatus::DONE => 'done',
                ShipmentStatus::CANCELLATION_EXPECTED => 'cancellationExpected',
                ShipmentStatus::RETURN_EXPECTED_FROM_CUSTOMER => 'returnExpectedFromCustomer',
                ShipmentStatus::RETURNED => 'returned',
                ShipmentStatus::PRE_ORDER => 'preOrder',
            ];
            foreach (ShipmentStatus::allStatuses() as $id => $status) {
                if (!isset($mapShipmentStatuses[$id])) {
                    continue;
                }
                $this->shipmentStatuses[$mapShipmentStatuses[$id]] = $status->toArray();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function loadCargoStatuses(bool $load = false): self
    {
        if ($load) {
            $mapCargoStatuses = [
                CargoStatus::CREATED => 'created',
                CargoStatus::SHIPPED => 'shipped',
                CargoStatus::TAKEN => 'taken',
            ];
            foreach (CargoStatus::allStatuses() as $id => $status) {
                if (!isset($mapCargoStatuses[$id])) {
                    continue;
                }
                $this->cargoStatuses[$mapCargoStatuses[$id]] = $status->toArray();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function loadDeliveryTypes(bool $load = false): self
    {
        if ($load) {
            $mapDeliveryTypes = [
                DeliveryType::TYPE_SPLIT => 'split',
                DeliveryType::TYPE_CONSOLIDATION => 'consolidation',
            ];
            foreach (DeliveryType::allTypes() as $id => $type) {
                if (!isset($mapDeliveryTypes[$id])) {
                    continue;
                }
                $this->deliveryTypes[$mapDeliveryTypes[$id]] = $type->toArray();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function loadDeliveryMethods(bool $load = false): self
    {
        if ($load) {
            $mapDeliveryMethods = [
                DeliveryMethod::METHOD_DELIVERY => 'delivery',
                DeliveryMethod::METHOD_PICKUP => 'pickup',
            ];
            foreach (DeliveryMethod::allMethods() as $id => $method) {
                if (!isset($mapDeliveryMethods[$id])) {
                    continue;
                }
                $this->deliveryMethods[$mapDeliveryMethods[$id]] = $method->toArray();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function loadDeliveryServices(bool $load = false): self
    {
        if ($load) {
            $mapDeliveryService = [
                DeliveryService::SERVICE_B2CPL => 'b2cpl',
                DeliveryService::SERVICE_CDEK => 'cdek',
                DeliveryService::SERVICE_BOXBERRY => 'boxberry',
                DeliveryService::SERVICE_DOSTAVISTA => 'dostavista',
            ];
            foreach (DeliveryService::allServices() as $id => $service) {
                if (isset($mapDeliveryService[$id])) {
                    $this->deliveryServices[$mapDeliveryService[$id]] = $service->toArray();
                } else {
                    $this->deliveryServices[] = $service->toArray();
                }
            }
        }

        return $this;
    }

    public function loadOfferSaleStatuses(bool $load = false): self
    {
        if ($load) {
            $mapOfferSaleStatuses = [
                OfferSaleStatus::STATUS_ON_SALE => 'onSale',
                OfferSaleStatus::STATUS_PRE_ORDER => 'preOrder',
                OfferSaleStatus::STATUS_OUT_SALE => 'outSale',
                OfferSaleStatus::STATUS_AVAILABLE_SALE => 'availableSale',
                OfferSaleStatus::STATUS_NOT_AVAILABLE_SALE => 'notAvailableSale',
            ];
            foreach (OfferSaleStatus::allStatuses() as $id => $status) {
                if (!isset($mapOfferSaleStatuses[$id])) {
                    continue;
                }
                $this->offerAllSaleStatuses[$mapOfferSaleStatuses[$id]] = $status->toArray();
            }

            $this->offerCountdownSaleStatuses = OfferSaleStatus::countdownStatuses();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function loadBasketTypes(bool $load = false): self
    {
        if ($load) {
            $this->basketTypes = [
                'product' => BasketDto::TYPE_PRODUCT,
                'master' => BasketDto::TYPE_MASTER,
            ];
        }

        return $this;
    }

    public function loadPropertyTypes(bool $load = false): self
    {
        if ($load) {
            $this->propertyTypes = PropertyDto::getTypes();
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
                /** TODO брать роли из базы */
                'user' => [
                    'isGuest' => resolve(TokenStore::class)->token() == null,
                    'isSuper' => resolve(RequestInitiator::class)->hasRole(UserDto::ADMIN__SUPER),
                ],

                'userRoles' => $this->userRoles,
                'blocks' => $this->blocks,
                'blockPermissions' => $this->blockPermissions,

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
                'merchantVatTypes' => $this->merchantVatTypes,

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

                'orderStatuses' => $this->orderStatuses,
                'basketTypes' => $this->basketTypes,
                'paymentStatuses' => $this->paymentStatuses,
                'paymentMethods' => $this->paymentMethods,
                'deliveryStatuses' => $this->deliveryStatuses,
                'shipmentStatuses' => $this->shipmentStatuses,
                'cargoStatuses' => $this->cargoStatuses,
                'deliveryTypes' => $this->deliveryTypes,
                'deliveryMethods' => $this->deliveryMethods,
                'deliveryServices' => $this->deliveryServices,

                'offerAllSaleStatuses' => $this->offerAllSaleStatuses,
                'offerCountdownSaleStatuses' => $this->offerCountdownSaleStatuses,

                'propertyTypes' => $this->propertyTypes,
            ],
            [
                'title' => $this->title,
                'assets' => $this->getAssets(),
            ]
        );
    }

    private function getAssets(): array
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
                    $js = array_filter((array) $webPack[$this->componentName]['js']);
                }
                if (isset($webPack[$this->componentName]['css'])) {
                    $css = array_filter((array) $webPack[$this->componentName]['css']);
                }
            }

            return [
                'js' => $js,
                'css' => $css,
            ];
        }
    }
}
