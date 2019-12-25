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
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'display_name')->merchants()
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
    public function createPage()
    {
        $this->title = 'Добавление склада';
        
        return $this->render('Store/MerchantStore/Create', []);
    }
    
    /**
     * @param int $id
     * @param Request $request
     * @param StoreService $storeService
     * @return mixed
     */
    public function detailPage(int $id, Request $request, StoreService $storeService)
    {
        $this->title = 'Редактирование склада';
        
        $restQuery = new RestQuery();
        $restQuery->setFilter('id', $id)
            ->include('storePickupTime', 'storeWorking', 'storeContact');
        
        $stores = $this->loadStores($request, $restQuery, $storeService);
        
        $store = $stores->first();
        
        return $this->render('Store/MerchantStore/Detail', [
            'iStore' => $store,
        ]);
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
            
                    if (!is_null($store->storePickupTime())) {
                        $dayHasPickupTime = false;
                        foreach (DeliveryService::allServices() as $deliveryService) {
                            /** @var StorePickupTimeDto $pickupTimeDto */
                            $pickupTimeDto = $store->storePickupTime()->filter(function (StorePickupTimeDto $item) use (
                                $day,
                                $deliveryService
                            ) {
                                return $item->day == $day && $item->delivery_service == $deliveryService->id;
                            })->first();
                            if (is_null($pickupTimeDto)) {
                                $pickupTimeDto = $store->storePickupTime()->filter(function (StorePickupTimeDto $item
                                ) use (
                                    $day
                                ) {
                                    return $item->day == $day;
                                })->first();
                            }
                    
                            $data['storePickupTime'][$day][$deliveryService->id] = is_null($pickupTimeDto) ? [] : $pickupTimeDto->toArray();
                            if (!is_null($pickupTimeDto)) {
                                $dayHasPickupTime = true;
                            }
                        }
                        $data['storePickupTime'][$day]['hasPickupTime'] = $dayHasPickupTime;
                    }
                }
            }
            
            return $data;
        });
        
        return $stores;
    }
}
