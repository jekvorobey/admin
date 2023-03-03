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
    protected $title = '';
    protected bool $loadCustomerStatus = false;
    protected bool $loadCommunicationChannelTypes = false;
    protected bool $loadCommunicationChannels = false;
    protected bool $loadCommunicationThemes = false;
    protected bool $loadCommunicationStatuses = false;
    protected bool $loadCommunicationTypes = false;
    protected bool $loadMerchantStatuses = false;
    protected bool $loadMerchantCommissionTypes = false;
    protected bool $loadMerchantVatTypes = false;
    protected bool $loadMerchantDocumentTypes = false;
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
    protected bool $loadAllPaymentMethods = false;
    protected bool $loadDeliveryStatuses = false;
    protected bool $loadShipmentStatuses = false;
    protected bool $loadCargoStatuses = false;
    protected bool $loadDeliveryTypes = false;
    protected bool $loadDeliveryMethods = false;
    protected bool $loadDeliveryServices = false;
    protected bool $loadOfferSaleStatuses = false;
    protected bool $loadBillingReportTypes = false;
    protected bool $loadBillingReportStatuses = false;
    protected bool $loadPropertyTypes = false;

    public function render($componentName, $props = [])
    {
        return (new ViewRender($componentName, $props))
            ->setTitle($this->title)
            ->loadUserFronts()
            ->loadBlocks()
            ->loadBlockPermissions()
            ->loadProductImagesTypes()
            ->loadExtSystemDriver()
            ->loadCustomerStatus($this->loadCustomerStatus)
            ->loadCommunicationChannelTypes($this->loadCommunicationChannelTypes)
            ->loadCommunicationChannels($this->loadCommunicationChannels)
            ->loadCommunicationThemes($this->loadCommunicationThemes)
            ->loadCommunicationStatuses($this->loadCommunicationStatuses)
            ->loadCommunicationTypes($this->loadCommunicationTypes)
            ->loadMerchantStatuses($this->loadMerchantStatuses)
            ->loadMerchantCommissionTypes($this->loadMerchantCommissionTypes)
            ->loadMerchantVatTypes($this->loadMerchantVatTypes)
            ->loadMerchantDocumentTypes($this->loadMerchantDocumentTypes)
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
            ->loadAllPaymentMethods($this->loadAllPaymentMethods)
            ->loadDeliveryStatuses($this->loadDeliveryStatuses)
            ->loadShipmentStatuses($this->loadShipmentStatuses)
            ->loadCargoStatuses($this->loadCargoStatuses)
            ->loadDeliveryTypes($this->loadDeliveryTypes)
            ->loadDeliveryMethods($this->loadDeliveryMethods)
            ->loadDeliveryServices($this->loadDeliveryServices)
            ->loadOfferSaleStatuses($this->loadOfferSaleStatuses)
            ->loadBillingReportStatuses($this->loadBillingReportStatuses)
            ->loadBillingReportTypes($this->loadBillingReportTypes)
            ->loadPropertyTypes($this->loadPropertyTypes)
            ->render();
    }

    protected function validate(Request $request, array $rules, array $customAttributes = [], array $messages = []): array
    {
        $data = $request->all();
        $validator = Validator::make($data, $rules, $messages, $customAttributes);
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
                ->addFields(MerchantDto::entity(), 'id', 'name');
            if ($merchantIds) {
                $merchantQuery->setFilter('id', $merchantIds);
            }
            $merchants = $merchantService->merchants($merchantQuery)->keyBy('id');
        }

        return $merchants;
    }

    protected function canView(int|array $blocks): bool
    {
        foreach ((array) $blocks as $block) {
            if (resolve(RequestInitiator::class)->canView($block)) {
                return true;
            }
        }

        abort(403, 'Недостаточно прав');
    }

    protected function canUpdate(int|array $blocks): bool
    {
        foreach ((array) $blocks as $block) {
            if (resolve(RequestInitiator::class)->canUpdate($block)) {
                return true;
            }
        }

        abort(403, 'Недостаточно прав');
    }

    /**
     * Проверить есть ли роль/роли у пользователя
     * @param int|int[] $roleId
     */
    protected function hasRole(array|int $roleId): bool
    {
        $state = resolve(RequestInitiator::class)->hasRole($roleId);
        if ($state === false) {
            abort(403, 'Недостаточно прав');
        }

        return true;
    }
}
