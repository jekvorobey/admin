<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
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
        $this->loadDeliveryServices = true;
        $this->loadDeliveryMethods = true;

        return $this->render('Logistics/DeliveryPrice/Index', [
            'iData' => $this->loadData($listsService),
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
            'delivery_method' => ['required', Rule::in(array_keys(DeliveryMethod::allMethods()))],
            'price' => 'numeric|required',
        ]);
        
        $deliveryPriceDto = new DeliveryPriceDto($validatedData);
        if ($deliveryPriceDto->id) {
            $listsService->updateDeliveryPrice($deliveryPriceDto->id, $deliveryPriceDto);
        } else {
            $deliveryPriceDto->id = $listsService->createDeliveryPrice($deliveryPriceDto);
        }
        
        return response()->json([
            'deliveryPrice' => $deliveryPriceDto
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
        })->groupBy('federal_district_id')->map(function (Collection $pricesByFederalDistrict) {
            return $pricesByFederalDistrict
                ->groupBy('delivery_service')
                ->map(function (Collection $pricesByDeliveryService) {
                    return $pricesByDeliveryService->keyBy('delivery_method');
                });
        });
        $deliveryPricesByRegions = $deliveryPrices->filter(function (DeliveryPriceDto $deliveryPrice) {
            return $deliveryPrice->region_id;
        })->groupBy('region_id')->map(function (Collection $pricesByRegion) {
            return $pricesByRegion
                ->groupBy('delivery_service')
                ->map(function (Collection $pricesByDeliveryService) {
                    return $pricesByDeliveryService->keyBy('delivery_method');
                });
        });
        
        $federalDistrictsQuery = $listsService->newQuery()
            ->include('regions');
        $federalDistricts = $listsService->federalDistricts($federalDistrictsQuery);
    
        $federalDistricts = $federalDistricts->map(function (FederalDistrictDto $federalDistrict) use ($deliveryPricesByFederalDistrict, $deliveryPricesByRegions) {
            $data = $federalDistrict->toArray();
            foreach (DeliveryService::allServices() as $deliveryService) {
                foreach (DeliveryMethod::allMethods() as $deliveryMethod) {
                    $emptyDeliveryPriceDto = new DeliveryPriceDto([
                        'federal_district_id' => $federalDistrict->id,
                        'delivery_service' => $deliveryService->id,
                        'delivery_method' => $deliveryMethod->id,
                        'price' => '',
                    ]);
                    $data['deliveryPrices'][$deliveryService->id][$deliveryMethod->id] =
                        isset($deliveryPricesByFederalDistrict[$data['id']][$deliveryService->id][$deliveryMethod->id]) ?
                            $deliveryPricesByFederalDistrict[$data['id']][$deliveryService->id][$deliveryMethod->id] : $emptyDeliveryPriceDto->toArray();
                }
            }
            
            foreach ($data['regions'] as &$region) {
                foreach (DeliveryService::allServices() as $deliveryService) {
                    foreach (DeliveryMethod::allMethods() as $deliveryMethod) {
                        $emptyDeliveryPriceDto = new DeliveryPriceDto([
                            'federal_district_id' => $federalDistrict->id,
                            'region_id' => $region['id'],
                            'region_guid' => $region['guid'],
                            'delivery_service' => $deliveryService->id,
                            'delivery_method' => $deliveryMethod->id,
                            'price' => '',
                        ]);
                        $region['deliveryPrices'][$deliveryService->id][$deliveryMethod->id] =
                            isset($deliveryPricesByRegions[$region['id']][$deliveryService->id][$deliveryMethod->id]) ?
                                $deliveryPricesByRegions[$region['id']][$deliveryService->id][$deliveryMethod->id] : $emptyDeliveryPriceDto->toArray();
                    }
                }
            }
    
            return $data;
        });
        
        return $federalDistricts;
    }
}
