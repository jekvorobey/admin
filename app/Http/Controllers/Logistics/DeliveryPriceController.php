<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
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
            'iDeliveryServices' => DeliveryService::allServices(),
        ]);
    }
    
    
    /**
     * @param RestQuery $restQuery
     * @param ListsService $listsService
     * @return Collection
     */
    protected function loadDeliveryPrices(ListsService $listsService): Collection
    {
        $deliveryPrices = $listsService->deliveryPrices()->groupBy('region_guid', 'delivery_service');
        
        $federalDistrictsQuery = $listsService->newQuery()
            ->include('regions');
        $federalDistricts = $listsService->federalDistricts($federalDistrictsQuery);
    
        $federalDistricts = $federalDistricts->map(function (FederalDistrictDto $federalDistrict) use ($deliveryPrices) {
            $data = $federalDistrict->toArray();
            
            foreach ($data['regions'] as &$region) {
                $region['deliveryPrices'] = $deliveryPrices->has($region['region_guid']) ? $deliveryPrices[$region['region_guid']] : [];
            }
    
            return $data;
        });
        
        return $federalDistricts;
    }
}
