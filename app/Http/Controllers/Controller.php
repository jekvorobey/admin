<?php

namespace App\Http\Controllers;

use App\Core\ViewRender;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Controller extends BaseController
{
    protected string $title = '';
    protected bool $loadUserRoles = false;
    protected bool $loadCustomerStatus = false;
    protected bool $loadCommunicationChannelTypes = false;
    protected bool $loadCommunicationChannels = false;
    protected bool $loadCommunicationThemes = false;
    protected bool $loadCommunicationStatuses = false;
    protected bool $loadCommunicationTypes = false;
    protected bool $loadMerchantStatuses = false;
    protected bool $loadMerchantCommissionTypes = false;
    protected bool $loadMerchantVatTypes = false;
    protected bool $loadPublicEventTypes = false;
    protected bool $loadPublicEventMediaTypes = false;
    protected bool $loadPublicEventMediaCollections = false;
    protected bool $loadPublicEventStatus = false;
    protected bool $loadPublicEventSprintStatus = false;
    protected bool $loadDiscountTypes = false;
    protected bool $loadPromoCodeTypes = false;
    protected bool $loadPromoCodeStatus = false;
    protected bool $loadBonusValueTypes = false;
    protected bool $loadBonusTypes = false;
    protected bool $loadCustomerBonusStatus = false;
    protected bool $loadOrderStatuses = false;
    protected bool $loadBasketTypes = false;
    protected bool $loadPaymentStatuses = false;
    protected bool $loadPaymentMethods = false;
    protected bool $loadDeliveryStatuses = false;
    protected bool $loadShipmentStatuses = false;
    protected bool $loadCargoStatuses = false;
    protected bool $loadDeliveryTypes = false;
    protected bool $loadDeliveryMethods = false;
    protected bool $loadDeliveryServices = false;
    protected bool $loadOfferSaleStatuses = false;
    protected bool $loadPropertyTypes = false;

    public function render($componentName, $props = [])
    {
        return (new ViewRender($componentName, $props))
            ->setTitle($this->title)
            ->loadUserRoles($this->loadUserRoles)
            ->loadBlocks()
            ->loadBlockPermissions()
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

    protected function canView(int $block): bool
    {
        $state = resolve(RequestInitiator::class)->canView($block);
        if ($state === false) {
            abort(403, 'Недостаточно прав');
        }

        return true;
    }

    protected function canUpdate(int $block): bool
    {
        $state = resolve(RequestInitiator::class)->canUpdate($block);
        if ($state === false) {
            abort(403, 'Недостаточно прав');
        }

        return true;
    }
}
