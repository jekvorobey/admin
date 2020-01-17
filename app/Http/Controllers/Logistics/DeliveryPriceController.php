<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Logistics\Dto\Lists\DeliveryPriceDto;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Dto\Lists\FederalDistrictDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Class DeliveryPriceController
 * @package App\Http\Controllers\Logistics
 */
class DeliveryPriceController extends Controller
{
    /**
     * @param ListsService $listsService
     * @return mixed
     */
    public function index(ListsService $listsService)
    {
        $this->title = 'Стоимость доставки по регионам';
        
        return $this->render('Logistics/DeliveryPrice/Index', [
            'iData' => $this->loadData($listsService),
            'deliveryServices' => DeliveryService::allServices(),
        ]);
    }
    
    /**
     * @param  Request  $request
     * @param  ListsService $listsService
     * @return JsonResponse
     */
    public function save(Request $request, ListsService $listsService): JsonResponse
    {
        $validatedData = $request->validate([
            'id' => 'integer|nullable',
            'federal_district_id' => 'integer|required',
            'region_id' => 'integer|nullable',
            'region_guid' => 'string|nullable',
            'delivery_service' => ['required', Rule::in(array_keys(DeliveryService::allServices()))],
            'price' => 'numeric|required',
        ]);
        
        $deliveryPriceDto = new DeliveryPriceDto($validatedData);
        if ($deliveryPriceDto->id) {
            $listsService->updateDeliveryPrice($deliveryPriceDto->id, $deliveryPriceDto);
        } else {
            $listsService->createDeliveryPrice($deliveryPriceDto);
        }
        
        return response()->json([
            'iData' => $this->loadData($listsService)
        ]);
    }
    
    /**
     * @param RestQuery $restQuery
     * @param ListsService $listsService
     * @return Collection
     */
    protected function loadData(ListsService $listsService): Collection
    {
        $deliveryPrices = $listsService->deliveryPrices();
        $deliveryPricesByFederalDistrict = $deliveryPrices->filter(function (DeliveryPriceDto $deliveryPrice) {
            return !$deliveryPrice->region_id;
        })->groupBy('federal_district_id')->map(function (Collection $pricesByDeliveryService) {
            return $pricesByDeliveryService->keyBy('delivery_service');
        });
        $deliveryPricesByRegions = $deliveryPrices->filter(function (DeliveryPriceDto $deliveryPrice) {
            return $deliveryPrice->region_id;
        })->groupBy('region_id')->map(function (Collection $pricesByDeliveryService) {
            return $pricesByDeliveryService->keyBy('delivery_service');
        });
        
        $federalDistrictsQuery = $listsService->newQuery()
            ->include('regions');
        $federalDistricts = $listsService->federalDistricts($federalDistrictsQuery);
    
        $federalDistricts = $federalDistricts->map(function (FederalDistrictDto $federalDistrict) use ($deliveryPricesByFederalDistrict, $deliveryPricesByRegions) {
            $data = $federalDistrict->toArray();
            if ($deliveryPricesByFederalDistrict->has($data['id'])) {
                $data['deliveryPrices'] =  $deliveryPricesByFederalDistrict[$data['id']];
            } else {
                foreach (DeliveryService::allServices() as $deliveryService) {
                    $emptyDeliveryPriceDto = new DeliveryPriceDto([
                        'federal_district_id' => $federalDistrict->id,
                        'delivery_service' => $deliveryService->id,
                        'price' => '',
                    ]);
                    $data['deliveryPrices'][$deliveryService->id] = $emptyDeliveryPriceDto->toArray();
                }
            }
            
            foreach ($data['regions'] as &$region) {
                if ($deliveryPricesByRegions->has($region['id'])) {
                    $region['deliveryPrices'] = $deliveryPricesByRegions[$region['id']];
                } else {
                    foreach (DeliveryService::allServices() as $deliveryService) {
                        $emptyDeliveryPriceDto = new DeliveryPriceDto([
                            'federal_district_id' => $federalDistrict->id,
                            'region_id' => $region['id'],
                            'region_guid' => $region['guid'],
                            'delivery_service' => $deliveryService->id,
                            'price' => '',
                        ]);
                        $region['deliveryPrices'][$deliveryService->id] = $emptyDeliveryPriceDto->toArray();
                    }
                }
            }
    
            return $data;
        });
        
        return $federalDistricts;
    }
}
