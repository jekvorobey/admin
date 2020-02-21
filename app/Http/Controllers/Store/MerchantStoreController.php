<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Logistics\Dto\Lists\CourierCallTime\B2CplCourierCallTime;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Store\Dto\StoreContactDto;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Dto\StorePickupTimeDto;
use Greensight\Store\Dto\StoreWorkingDto;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;

/**
 * Class MerchantStoreController
 * @package App\Http\Controllers\Store
 */
class MerchantStoreController extends Controller
{
    /**
     * @param Request $request
     * @param StoreService $storeService
     * @return mixed
     */
    public function index(Request $request, StoreService $storeService, MerchantService $merchantService)
    {
        $this->title = 'Склады мерчантов';
        
        $page = $request->get('page', 1);
        
        $restQuery = new RestQuery();
        $restQuery->pageNumber($page, 20);
        
        $stores = $this->loadStores($restQuery, $storeService);
        
        $pager = $storeService->storesCount($restQuery);
        
        return $this->render('Store/MerchantStore/List', [
            'iStores' => $stores,
            'iFilter' => $this->getFilter() ? : null,
            'iCurrentPage' => (int) $page,
            'pager' => $pager,
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'display_name')->merchants(),
        ]);
    }
    
    /**
     * @param Request $request
     * @param StoreService $storeService
     * @return \Illuminate\Http\JsonResponse
     */
    public function page(Request $request, StoreService $storeService): JsonResponse
    {
        $page = $request->get('page', 1);
        
        $restQuery = new RestQuery();
        $restQuery->pageNumber($page, 20);
        
        $stores = $this->loadStores($restQuery, $storeService);
        
        return response()->json([
            'iStores' => $stores,
        ]);
    }
    
    /**
     * @return array
     */
    protected function getFilter(): array
    {
        return Validator::make(request('filter') ?? [], [
            'id' => 'integer|someone',
            'merchant_id' => 'integer|someone',
            'name' => 'string|someone',
            'address.city' => 'string|someone',
        ])->attributes();
    }
    
    /**
     * @return mixed
     */
    public function createPage(MerchantService $merchantService)
    {
        $this->title = 'Добавление склада мерчанта';
        
        return $this->render('Store/MerchantStore/Create', [
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'display_name')->merchants(),
        ]);
    }
    
    /**
     * @param int $id
     * @param StoreService $storeService
     * @return mixed
     */
    public function detailPage(int $id, StoreService $storeService, MerchantService $merchantService)
    {
        $this->title = 'Редактирование склада мерчанта';
        
        return $this->render('Store/MerchantStore/Detail', [
            'iStore' => $this->getStore($id, $storeService),
            'iDeliveryServices' => DeliveryService::allServices(),
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'display_name')->merchants(),
            'pickupTimes' => $this->getPickupTimes(),
        ]);
    }
    
    /**
     * @param  int  $storeId
     * @param  StoreService  $storeService
     * @return array
     */
    protected function getStore(int $storeId, StoreService $storeService): array
    {
        $restQuery = new RestQuery();
        $restQuery->setFilter('id', $storeId)
            ->include('storePickupTime', 'storeWorking', 'storeContact');
        $stores = $this->loadStores($restQuery, $storeService);
        
        return $stores->first();
    }
    
    /**
     * @return array
     */
    protected function getPickupTimes(): array
    {
        return [
            DeliveryService::SERVICE_B2CPL => B2CplCourierCallTime::all(),
        ];
    }
    
    /**
     * @param  Request  $request
     * @param  StoreService  $storeService
     * @return JsonResponse
     */
    public function create(Request $request, StoreService $storeService): JsonResponse
    {
        $validatedData = $request->validate([
            'merchant_id' => 'integer|required',
            'name' => 'string|required',
            'address.address_string' => 'string|required',
            'address.country_code' => 'string|required',
            'address.post_index' => 'string|required',
            'address.region' => 'string|required',
            'address.region_guid' => 'string|required',
            'address.city' => 'string|required',
            'address.city_guid' => 'string|required',
            'address.street' => 'string|nullable',
            'address.house' => 'string|nullable',
            'address.block' => 'string|nullable',
            'address.flat' => 'string|nullable',
            'address.porch' => 'string|nullable',
            'address.intercom' => 'string|nullable',
            'address.floor' => 'string|nullable',
            'address.comment' => 'string|nullable',
        ]);
        
        $result = $storeService->createStore(new StoreDto($validatedData));
        
        return response()->json(['status' => $result ? 'ok' : 'fail']);
    }
    
    /**
     * @param int $id
     * @param Request $request
     * @param StoreService $storeService
     * @return JsonResponse
     */
    public function update(int $id, Request $request, StoreService $storeService): JsonResponse
    {
        $validatedData = $request->validate([
            'id' => 'integer|required',
            'merchant_id' => 'integer|required',
            'xml_id' => 'string|nullable',
            'active' => 'boolean',
            'name' => 'string|required',
            'address.address_string' => 'string|required',
            'address.country_code' => 'string|required',
            'address.post_index' => 'string|required',
            'address.region' => 'string|required',
            'address.region_guid' => 'string|required',
            'address.city' => 'string|required',
            'address.city_guid' => 'string|required',
            'address.street' => 'string|nullable',
            'address.house' => 'string|nullable',
            'address.block' => 'string|nullable',
            'address.flat' => 'string|nullable',
            'address.porch' => 'string|nullable',
            'address.intercom' => 'string|nullable',
            'address.floor' => 'string|nullable',
            'address.comment' => 'string|nullable',
        ]);
        
        $validatedData['id'] = $id;
        
        $storeService->updateStore($validatedData['id'], new StoreDto($validatedData));
        
        return response()->json([]);
    }
    
    /**
     * @param int $id
     * @param StoreService $storeService
     * @return JsonResponse
     */
    public function delete(int $id, StoreService $storeService)
    {
        $storeService->deleteStore($id);
        return response()->json([]);
    }
    
    /**
     * @param int $id
     * @param Request $request
     * @param StoreService $storeService
     * @return JsonResponse
     */
    public function updateWorking(int $id, Request $request, StoreService $storeService): JsonResponse
    {
        $validatedData = $request->validate([
            'id' => 'integer|required',
            'day' => 'integer|required',
            'store_id' => 'integer|required',
            'working_start_time' => 'string|nullable',
            'working_end_time' => 'string|nullable',
            'active' => 'boolean',
        ]);
        
        $validatedData['id'] = $id;
        
        $storeService->updateStoreWorking($validatedData['id'], new StoreWorkingDto($validatedData));
        
        return response()->json([]);
    }
    
    /**
     * @param  Request  $request
     * @param  StoreService  $storeService
     * @return JsonResponse
     */
    public function savePickupTime(Request $request, StoreService $storeService): JsonResponse
    {
        $validatedData = $request->validate([
            'id' => 'integer|nullable',
            'day' => 'integer|required',
            'store_id' => 'integer|required',
            'pickup_time_code' => 'string|nullable',
            'cargo_export_time' => 'string|nullable',
            'delivery_service' => ['sometimes', Rule::in(array_keys(DeliveryService::allServices()))],
        ]);
        
        $storePickupTimeDto = new StorePickupTimeDto($validatedData);
        if ($storePickupTimeDto->id) {
            $storeService->updateStorePickupTime($storePickupTimeDto->id, $storePickupTimeDto);
        } else {
            $storeService->createStorePickupTime($storePickupTimeDto->store_id, $storePickupTimeDto);
        }
        
        return response()->json([
            'pickupTimes' => $this->getStore($storePickupTimeDto->store_id, $storeService)['pickupTimes']
        ]);
    }
    
    
    /**
     * @param int $id
     * @param Request $request
     * @param StoreService $storeService
     * @return JsonResponse
     */
    public function createContact(int $id, Request $request, StoreService $storeService)
    {
        $validatedData = $request->validate([
            'store_id' => 'integer|required',
            'name' => 'string|nullable',
            'phone' => 'string|nullable',
            'email' => 'email|nullable',
        ]);
        
        $result = $storeService->createContact($id, new StoreContactDto($validatedData));
        
        return response()->json(['id' => $result]);
    }
    
    /**
     * @param int $id
     * @param Request $request
     * @param StoreService $storeService
     * @return JsonResponse
     */
    public function updateContact(int $id, Request $request, StoreService $storeService): JsonResponse
    {
        $validatedData = $request->validate([
            'id' => 'integer|required',
            'name' => 'string|required',
            'phone' => 'string|required',
            'email' => 'email|nullable',
        ]);
        
        $validatedData['id'] = $id;
        
        $storeService->updateContact($validatedData['id'], new StoreContactDto($validatedData));
        return response()->json([]);
    }
    
    /**
     * @param int $id
     * @param StoreService $storeService
     * @return JsonResponse
     */
    public function deleteContact(int $id, StoreService $storeService)
    {
        $storeService->deleteContact($id);
        return response()->json([]);
    }
    
    
    /**
     * @param RestQuery $restQuery
     * @param StoreService $storeService
     * @return Collection
     */
    protected function loadStores(RestQuery $restQuery, StoreService $storeService): Collection
    {
        $filter = $this->getFilter();
    
        foreach ($filter as $key => $value) {
            if ($value) {
                switch ($key) {
                    case 'name':
                        $restQuery->setFilter($key, 'like', "%{$value}%");
                        break;
                    case 'address':
                        foreach ($value as $key1 => $value1) {
                            $field = $key . '->' . $key1;
                            
                            if ($value1) {
                                switch ($key1) {
                                    case 'city':
                                        $restQuery->setFilter($field, 'like', "%{$value1}%");
                                        break;
                                }
                            }
                        }
                        break;
        
                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
        }
        
        $stores = $storeService->stores($restQuery);
        
        $merchantIds = $stores->pluck('merchant_id')->unique()->all();
        $merchants = $this->getMerchants($merchantIds);
        
        $stores = $stores->map(function (StoreDto $store) use ($merchants) {
            $data = $store->toArray();
            
            $data['merchant'] = $merchants->has($store->merchant_id) ? $merchants[$store->merchant_id] : [];
    
            if (!is_null($store->storeWorking()) || !is_null($store->storePickupTime())) {
                foreach ([1, 2, 3, 4, 5, 6, 7] as $day) {
                    $data['days'][$day] = [];
            
                    if (!is_null($store->storeWorking())) {
                        $workingDay = $store->storeWorking()->pluck('day')->search($day);
                        if ($workingDay !== false) {
                            $data['days'][$day] = $store['storeWorking'][$workingDay];
                        }
                    }
                    
                    $dayHasPickupTime = false;
                    foreach (DeliveryService::allServices() as $deliveryService) {
                        $pickupTimeDto = null;
                        $allDeliveryServicePickupTimeDto = null;
                        if (!is_null($store->storePickupTime())) {
                            /** @var StorePickupTimeDto $pickupTimeDto */
                            $pickupTimeDto = $store->storePickupTime()->filter(function (StorePickupTimeDto $item) use (
                                $day,
                                $deliveryService
                            ) {
                                return $item->day == $day && $item->delivery_service == $deliveryService->id;
                            })->first();
    
                            $allDeliveryServicePickupTimeDto = $store->storePickupTime()->filter(function (
                                StorePickupTimeDto $item
                            ) use (
                                $day
                            ) {
                                return $item->day == $day && !$item->delivery_service;
                            })->first();
                        }
                
                        $emptyPickupTimeDto = new StorePickupTimeDto([
                            'day' => $day,
                            'store_id' => $store->id,
                        ]);
                        $data['pickupTimes'][$day][$deliveryService->id] = array_filter(is_null($pickupTimeDto) ?
                            array_merge($emptyPickupTimeDto->toArray(), ['delivery_service' => $deliveryService->id]) : $pickupTimeDto->toArray());
                        $data['pickupTimes'][$day]['all'] = array_filter(is_null($allDeliveryServicePickupTimeDto) ?
                            $emptyPickupTimeDto->toArray() : $allDeliveryServicePickupTimeDto->toArray());
                        
                        if (!is_null($pickupTimeDto) || !is_null($allDeliveryServicePickupTimeDto)) {
                            $dayHasPickupTime = true;
                        }
                    }
                    $data['pickupTimes'][$day]['hasPickupTime'] = $dayHasPickupTime;
                }
            }
            
            return $data;
        });
        
        return $stores;
    }
}
