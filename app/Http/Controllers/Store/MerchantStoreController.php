<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Store\Dto\StoreContactDto;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Dto\StorePickupTimeDto;
use Greensight\Store\Dto\StoreWorkingDto;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $this->title = 'Склады';
        
        $page = $request->get('page', 1);
        
        $restQuery = new RestQuery();
        $restQuery->pageNumber($page, 20);
        
        $stores = $this->loadStores($request, $restQuery, $storeService);
        
        $pager = $storeService->storesCount($restQuery);
        
        return $this->render('Store/MerchantStore/List', [
            'iStores' => $stores,
            'iFilter' => $this->getFilter(),
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
        
        $stores = $this->loadStores($request, $restQuery, $storeService);
        
        return response()->json([
            'iStores' => $stores,
        ]);
    }
    
    /**
     * @return array|null
     */
    protected function getFilter(): ?array
    {
        return request()->get('filter');
    }
    
    /**
     * @return mixed
     */
    public function createPage(MerchantService $merchantService)
    {
        $this->title = 'Добавление склада';
        
        return $this->render('Store/MerchantStore/Create', [
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'display_name')->merchants(),
        ]);
    }
    
    /**
     * @param int $id
     * @param Request $request
     * @param StoreService $storeService
     * @return mixed
     */
    public function detailPage(int $id, Request $request, StoreService $storeService, MerchantService $merchantService)
    {
        $this->title = 'Редактирование склада';
        
        return $this->render('Store/MerchantStore/Detail', [
            'iStore' => $this->getStore($id, $request, $storeService),
            'iDeliveryServices' => DeliveryService::allServices(),
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'display_name')->merchants(),
        ]);
    }
    
    /**
     * @param  int  $storeId
     * @param  Request  $request
     * @param  StoreService  $storeService
     * @return array
     */
    protected function getStore(int $storeId, Request $request, StoreService $storeService): array
    {
        $restQuery = new RestQuery();
        $restQuery->setFilter('id', $storeId)
            ->include('storePickupTime', 'storeWorking', 'storeContact');
        $stores = $this->loadStores($request, $restQuery, $storeService);
        
        return $stores->first();
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
            'zip' => 'string|required',
            'city' => 'string|required',
            'street' => 'string|required',
            'house' => 'string|nullable',
            'flat' => 'string|nullable',
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
            'zip' => 'string|required',
            'city' => 'string|required',
            'street' => 'string|required',
            'house' => 'string|nullable',
            'flat' => 'string|nullable',
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
            'pickup_time' => 'string|nullable',
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
            'pickupTimes' => $this->getStore($storePickupTimeDto->store_id, $request, $storeService)['pickupTimes']
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
     * @param Request $request
     * @param RestQuery $restQuery
     * @param StoreService $storeService
     * @return Collection
     */
    protected function loadStores(Request $request, RestQuery $restQuery, StoreService $storeService): Collection
    {
        $filters = $request->get('filter', []);
        
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'name':
                    $restQuery->setFilter('name', 'like', "%{$value}%");
                    break;
                default:
                    $restQuery->setFilter($key, $value);
            }
        }
        
        $stores = $storeService->stores($restQuery);
        $merchantIds = $stores->pluck('merchant_id')->all();
        $merchants = collect();
        if ($merchantIds) {
            /** @var MerchantService $merchantService */
            $merchantService = resolve(MerchantService::class);
            $merchantQuery = $merchantService->newQuery()
                ->setFilter('id', $merchantIds)
                ->addFields(MerchantDto::entity(), 'id', 'display_name');
            $merchants = $merchantService->merchants($merchantQuery)->keyBy('id');
        }
        
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
                        /** @var StorePickupTimeDto $pickupTimeDto */
                        $pickupTimeDto = $store->storePickupTime()->filter(function (StorePickupTimeDto $item) use (
                            $day,
                            $deliveryService
                        ) {
                            return $item->day == $day && $item->delivery_service == $deliveryService->id;
                        })->first();
                        
                        $allDeliveryServicePickupTimeDto = $store->storePickupTime()->filter(function (StorePickupTimeDto $item
                        ) use (
                            $day
                        ) {
                            return $item->day == $day && !$item->delivery_service;
                        })->first();
                
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
