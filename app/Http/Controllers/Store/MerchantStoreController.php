<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
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
    public function index(Request $request, StoreService $storeService, MerchantService $merchantService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_STORES);

        $this->title = 'Склады мерчантов';

        $page = $request->get('page', 1);

        $restQuery = new RestQuery();
        $restQuery->pageNumber($page, 20);

        $stores = $this->loadStores($restQuery, $storeService);
        $pager = $storeService->storesCount($restQuery);

        return $this->render('Store/MerchantStore/List', [
            'iStores' => $stores,
            'iFilter' => $this->getFilter() ?: null,
            'iCurrentPage' => (int) $page,
            'pager' => $pager,
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'legal_name')->merchants(),
        ]);
    }

    public function page(Request $request, StoreService $storeService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_STORES);

        $page = $request->get('page', 1);

        $restQuery = new RestQuery();
        $restQuery->pageNumber($page, 20);

        $stores = $this->loadStores($restQuery, $storeService);

        return response()->json([
            'iStores' => $stores,
        ]);
    }

    protected function getFilter(): array
    {
        return Validator::make(request('filter') ?? [], [
            'id' => 'integer|someone',
            'merchant_id' => 'integer|someone',
            'name' => 'string|someone',
            'address.city' => 'string|someone',
            'cdek_address.city' => 'string|someone',
        ])->attributes();
    }

    /**
     * @return mixed
     */
    public function createPage(MerchantService $merchantService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_STORES);

        $this->title = 'Добавление склада мерчанта';

        return $this->render('Store/MerchantStore/Create', [
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'legal_name')->merchants(),
        ]);
    }

    public function detailPage(int $id, StoreService $storeService, MerchantService $merchantService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_STORES);

        $this->title = 'Редактирование склада мерчанта';
        $this->loadDeliveryServices = true;

        return $this->render('Store/MerchantStore/Detail', [
            'iStore' => $this->getStore($id, $storeService),
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'legal_name')->merchants(),
            'pickupTimes' => $this->getPickupTimes(),
        ]);
    }

    protected function getStore(int $storeId, StoreService $storeService): array
    {
        $restQuery = new RestQuery();
        $restQuery->setFilter('id', $storeId)
            ->include('storePickupTime', 'storeWorking', 'storeContact');
        $stores = $this->loadStores($restQuery, $storeService);
        $store = $stores->first();
        if (!$store['cdek_address']) {
            $store['cdek_address'] = ['address_string' => ''];
        }

        return $store;
    }

    protected function getPickupTimes(): array
    {
        return [
            DeliveryService::SERVICE_B2CPL => B2CplCourierCallTime::all(),
        ];
    }

    public function create(Request $request, StoreService $storeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_STORES);

        $addressValidate = $this->getAddressValidate();
        $validate = array_merge([
            'merchant_id' => 'integer|required',
            'name' => 'string|required',
            'xml_id' => 'string|nullable',
        ], $addressValidate);
        $validatedData = $request->validate($validate);

        $storeId = $storeService->createStore(new StoreDto($validatedData));

        return response()->json(['id' => $storeId]);
    }

    public function update(int $id, Request $request, StoreService $storeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_STORES);

        $addressValidate = $this->getAddressValidate();
        $validate = array_merge([
            'id' => 'integer|required',
            'merchant_id' => 'integer|required',
            'xml_id' => 'string|nullable',
            'active' => 'boolean',
            'name' => 'string|required',
        ], $addressValidate);
        $validatedData = $request->validate($validate);

        $validatedData['id'] = $id;
        $storeService->updateStore($validatedData['id'], new StoreDto($validatedData));

        return response()->json([]);
    }

    private function getAddressValidate(): array
    {
        return [
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

            'cdek_address.address_string' => 'string|nullable',
            'cdek_address.country_code' => 'string|required_with:cdek_address.address_string',
            'cdek_address.post_index' => 'string|nullable',
            'cdek_address.region' => 'string|required_with:cdek_address.address_string',
            'cdek_address.region_guid' => 'string|required_with:cdek_address.address_string',
            'cdek_address.city' => 'string|required_with:cdek_address.address_string',
            'cdek_address.city_guid' => 'string|required_with:cdek_address.address_string',
            'cdek_address.street' => 'string|nullable',
            'cdek_address.house' => 'string|nullable',
            'cdek_address.block' => 'string|nullable',
            'cdek_address.flat' => 'string|nullable',
            'cdek_address.porch' => 'string|nullable',
        ];
    }

    public function delete(int $id, StoreService $storeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_STORES);

        $storeService->deleteStore($id);

        return response()->json([]);
    }

    public function deleteArray(Request $request, StoreService $storeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_STORES);

        $data = $request->validate([
            'ids' => 'array|required',
            'ids.*' => 'integer',
        ]);

        $storeService->deleteStores($data['ids']);

        return response()->json([]);
    }

    public function updateWorking(int $id, Request $request, StoreService $storeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_STORES);

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

    public function pickupTime(Request $request, StoreService $storeService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_STORES);

        $validatedData = $request->validate([
            'store_id' => 'integer|required',
        ]);

        return response()->json([
            'pickupTimes' => $this->getStore($validatedData['store_id'], $storeService)['pickupTimes'],
        ]);
    }

    public function savePickupTime(Request $request, StoreService $storeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_STORES);

        $validatedData = $request->validate([
            'id' => 'integer|nullable',
            'day' => 'integer|required',
            'store_id' => 'integer|required',
            'pickup_time_code' => 'string|nullable',
            'pickup_time_start' => 'string|nullable',
            'pickup_time_end' => 'string|nullable',
            'cargo_export_time' => 'string|nullable',
            'delivery_service' => ['sometimes', Rule::in(array_keys(DeliveryService::allServices()))],
        ]);

        $storePickupTimeDto = new StorePickupTimeDto($validatedData);
        if ($storePickupTimeDto->id) {
            $storeService->updateStorePickupTime($storePickupTimeDto->id, $storePickupTimeDto);
        } else {
            $storeService->createStorePickupTime($storePickupTimeDto->store_id, $storePickupTimeDto);
        }

        return response()->json([]);
    }

    public function createContact(int $id, Request $request, StoreService $storeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_STORES);

        $validatedData = $request->validate([
            'store_id' => 'integer|required',
            'name' => 'string|nullable',
            'phone' => 'string|nullable',
            'email' => 'email|nullable',
        ]);

        $result = $storeService->createContact($id, new StoreContactDto($validatedData));

        return response()->json(['id' => $result]);
    }

    public function updateContact(int $id, Request $request, StoreService $storeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_STORES);

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

    public function deleteContact(int $id, StoreService $storeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_STORES);

        $storeService->deleteContact($id);

        return response()->json([]);
    }

    protected function loadStores(RestQuery $restQuery, StoreService $storeService): Collection
    {
        $filter = $this->getFilter();
        foreach ($filter as $key => $value) {
            if ($value) {
                switch ($key) {
                    case 'name':
                        $restQuery->setFilter($key, 'like', "%{$value}%");
                        break;
                    case 'cdek_address':
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

        return $stores->map(function (StoreDto $store) use ($merchants) {
            $data = $store->toArray();

            $data['merchant'] = $merchants->has($store->merchant_id) ? $merchants[$store->merchant_id] : [];
            $data['storeWorking'] = $store->storeWorking ? $store->storeWorking->keyBy('day') : [];

            if ($store->storePickupTime) {
                foreach ([1, 2, 3, 4, 5, 6, 7] as $day) {
                    $dayHasPickupTime = false;
                    foreach (DeliveryService::allServices() as $deliveryService) {
                        $pickupTimeDto = null;
                        $allDeliveryServicePickupTimeDto = null;
                        if ($store->storePickupTime->isNotEmpty()) {
                            /** @var StorePickupTimeDto $pickupTimeDto */
                            $pickupTimeDto = $store->storePickupTime
                                ->filter(function (StorePickupTimeDto $item) use ($day, $deliveryService) {
                                    return $item->day == $day && $item->delivery_service == $deliveryService->id;
                                })
                                ->first();

                            $allDeliveryServicePickupTimeDto = $store->storePickupTime->filter(function (
                                StorePickupTimeDto $item
                            ) use ($day) {
                                return $item->day == $day && !$item->delivery_service;
                            })->first();
                        }

                        $emptyPickupTimeDto = new StorePickupTimeDto([
                            'day' => $day,
                            'store_id' => $store->id,
                        ]);
                        $data['pickupTimes'][$day][$deliveryService->id] = array_filter(is_null($pickupTimeDto) ?
                            array_merge($emptyPickupTimeDto->toArray(), ['delivery_service' => $deliveryService->id])
                            : $pickupTimeDto->toArray());
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
    }
}
