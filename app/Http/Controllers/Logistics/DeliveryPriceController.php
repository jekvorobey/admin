<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Logistics\Dto\Lists\DeliveryPriceDto;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Dto\Lists\FederalDistrictDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Illuminate\Support\Collection;

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
        
        $deliveryPrices = $this->loadDeliveryPrices($listsService);
        
        return $this->render('Logistics/DeliveryPrice/Index', [
            'iDeliveryPrices' => $deliveryPrices,
            'deliveryServices' => DeliveryService::allServices(),
        ]);
    }
    
    
    /**
     * @param RestQuery $restQuery
     * @param ListsService $listsService
     * @return Collection
     */
    protected function loadDeliveryPrices(ListsService $listsService): Collection
    {
        $deliveryPrices = $listsService->deliveryPrices();
        $deliveryPricesByFederalDistrict = $deliveryPrices->filter(function (DeliveryPriceDto $deliveryPrice) {
            return !$deliveryPrice->region_id;
        })->groupBy('federal_district_id', 'delivery_service');
        $deliveryPricesByRegions = $deliveryPrices->filter(function (DeliveryPriceDto $deliveryPrice) {
            return $deliveryPrice->region_id;
        })->groupBy('region_id', 'delivery_service');
        
        $federalDistrictsQuery = $listsService->newQuery()
            ->include('regions');
        $federalDistricts = $listsService->federalDistricts($federalDistrictsQuery);
    
        $federalDistricts = $federalDistricts->map(function (FederalDistrictDto $federalDistrict) use ($deliveryPricesByFederalDistrict, $deliveryPricesByRegions) {
            $data = $federalDistrict->toArray();
            $data['deliveryPrices'] = $deliveryPricesByFederalDistrict->has($data['id']) ? $deliveryPricesByFederalDistrict[$data['id']] : [];
            
            foreach ($data['regions'] as &$region) {
                $region['deliveryPrices'] = $deliveryPricesByRegions->has($region['id']) ? $deliveryPricesByRegions[$region['id']] : [];
            }
    
            return $data;
        });
        
        return $federalDistricts;
    }
}
