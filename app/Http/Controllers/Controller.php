<?php

namespace App\Http\Controllers;

use App\Core\ViewRender;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Controller extends BaseController
{
    protected $title = '';
    protected $loadUserRoles = false;
    protected $loadCustomerStatus = false;
    protected $loadCommunicationChannelTypes = false;
    protected $loadCommunicationChannels = false;
    protected $loadCommunicationThemes = false;
    protected $loadCommunicationStatuses = false;
    protected $loadCommunicationTypes = false;
    protected $loadMerchantStatuses = false;
    protected $loadMerchantCommissionTypes = false;
    protected $loadMerchantVatTypes = false;
    protected $loadPublicEventTypes = false;
    protected $loadPublicEventMediaTypes = false;
    protected $loadPublicEventMediaCollections = false;
    protected $loadPublicEventStatus = false;
    protected $loadPublicEventSprintStatus = false;
    protected $loadDiscountTypes = false;
    protected $loadPromoCodeTypes = false;
    protected $loadPromoCodeStatus = false;
    protected $loadBonusValueTypes = false;
    protected $loadBonusTypes = false;
    protected $loadCustomerBonusStatus = false;
    protected $loadOrderStatuses = false;
    protected $loadBasketTypes = false;
    protected $loadPaymentStatuses = false;
    protected $loadPaymentMethods = false;
    protected $loadDeliveryStatuses = false;
    protected $loadShipmentStatuses = false;
    protected $loadCargoStatuses = false;
    protected $loadDeliveryTypes = false;
    protected $loadDeliveryMethods = false;
    protected $loadDeliveryServices = false;
    protected $loadOfferSaleStatuses = false;
    protected $loadPropertyTypes = false;

    public function render($componentName, $props = [])
    {
        return (new ViewRender($componentName, $props))
            ->setTitle($this->title)
            ->loadUserRoles($this->loadUserRoles)
            ->loadCustomerStatus($this->loadCustomerStatus)
            ->loadCommunicationChannelTypes($this->loadCommunicationChannelTypes)
            ->loadCommunicationChannels($this->loadCommunicationChannels)
            ->loadCommunicationThemes($this->loadCommunicationThemes)
            ->loadCommunicationStatuses($this->loadCommunicationStatuses)
            ->loadCommunicationTypes($this->loadCommunicationTypes)
            ->loadMerchantStatuses($this->loadMerchantStatuses)
            ->loadMerchantCommissionTypes($this->loadMerchantCommissionTypes)
            ->loadMerchantVatTypes($this->loadMerchantVatTypes)
            ->loadPublicEventTypes($this->loadPublicEventTypes)
            ->loadPublicEventMediaTypes($this->loadPublicEventMediaTypes)
            ->loadPublicEventMediaCollections($this->loadPublicEventMediaCollections)
            ->loadPublicEventSprintStatus($this->loadPublicEventSprintStatus)
            ->loadPublicEventStatus($this->loadPublicEventStatus)
            ->loadDiscountTypes($this->loadDiscountTypes)
            ->loadPromoCodeTypes($this->loadPromoCodeTypes)
            ->loadPromoCodeStatus($this->loadPromoCodeStatus)
            ->loadBonusValueTypes($this->loadBonusValueTypes)
            ->loadBonusTypes($this->loadBonusTypes)
            ->loadCustomerBonusStatus($this->loadCustomerBonusStatus)
            ->loadOrderStatuses($this->loadOrderStatuses)
            ->loadBasketTypes($this->loadBasketTypes)
            ->loadPaymentStatuses($this->loadPaymentStatuses)
            ->loadDeliveryStatuses($this->loadDeliveryStatuses)
            ->loadShipmentStatuses($this->loadShipmentStatuses)
            ->loadCargoStatuses($this->loadCargoStatuses)
            ->loadDeliveryTypes($this->loadDeliveryTypes)
            ->loadDeliveryMethods($this->loadDeliveryMethods)
            ->loadDeliveryServices($this->loadDeliveryServices)
            ->loadPaymentMethods($this->loadPaymentMethods)
            ->loadOfferSaleStatuses($this->loadOfferSaleStatuses)
            ->loadPropertyTypes($this->loadPropertyTypes)
            ->render();
    }

    protected function validate(Request $request, array $rules, array $customAttributes = []): array
    {
        $data = $request->all();
        $validator = Validator::make($data, $rules, [], $customAttributes);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }
        return $data;
    }

    /**
     * @param array|null $merchantIds
     * @return Collection|MerchantDto[]
     */
    protected function getMerchants(?array $merchantIds = null): Collection
    {
        $merchants = collect();

        if (is_null($merchantIds) || count($merchantIds) > 0) {
            /** @var MerchantService $merchantService */
            $merchantService = resolve(MerchantService::class);
            $merchantQuery = $merchantService->newQuery()
                ->addFields(MerchantDto::entity(), 'id', 'legal_name');
            if ($merchantIds) {
                $merchantQuery->setFilter('id', $merchantIds);
            }
            $merchants = $merchantService->merchants($merchantQuery)->keyBy('id');
        }

        return $merchants;
    }
}
