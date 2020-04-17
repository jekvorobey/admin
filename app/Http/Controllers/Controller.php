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
    protected $loadPublicEventTypes = false;
    protected $loadPublicEventMediaTypes = false;
    protected $loadPublicEventMediaCollections = false;
    protected $loadPublicEventSprintStatus = false;
    protected $loadDiscountTypes = false;

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
            ->loadPublicEventTypes($this->loadPublicEventTypes)
            ->loadPublicEventMediaTypes($this->loadPublicEventMediaTypes)
            ->loadPublicEventMediaCollections($this->loadPublicEventMediaCollections)
            ->loadPublicEventSprintStatus($this->loadPublicEventSprintStatus)
            ->loadDiscountTypes($this->loadDiscountTypes)
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
     * @param  array  $merchantIds
     * @return Collection|MerchantDto[]
     */
    protected function getMerchants(array $merchantIds): Collection
    {
        $merchants = collect();
        
        if ($merchantIds) {
            /** @var MerchantService $merchantService */
            $merchantService = resolve(MerchantService::class);
            $merchantQuery = $merchantService->newQuery()
                ->setFilter('id', $merchantIds)
                ->addFields(MerchantDto::entity(), 'id', 'legal_name');
            $merchants = $merchantService->merchants($merchantQuery)->keyBy('id');
        }
        
        return $merchants;
    }
}
