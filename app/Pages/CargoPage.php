<?php

namespace App\Pages;

use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Oms\Dto\Delivery\CargoDto;
use Greensight\Oms\Services\CargoService\CargoService;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CargoPage
 * @package App\Pages
 */
class CargoPage extends AbstractPage
{
    use WithOmsHistory;

    /** @var int - id груза */
    private $id;

    /** @var bool */
    private $cargoLoaded = false;
    /** @var CargoDto */
    private $cargo;

    /**
     * OrderPage constructor.
     */
    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    /**
     * Загрузить груз
     * @return array
     */
    public function loadCargo(): array
    {
        if (!$this->cargoLoaded) {
            /** @var CargoService $cargoService */
            $cargoService = resolve(CargoService::class);

            $restQuery = $cargoService
                ->newQuery()
                ->addFields(
                    CargoDto::entity(),
                    'id',
                    'store_id',
                    'cdek_intake_number',
                    'xml_id',
                    'error_xml_id',
                    'status',
                    'delivery_service',
                    'package_qty',
                    'created_at',
                    'updated_at'
                )
                ->include('shipments.basketItems');
            $this->cargo = $cargoService->cargo($this->id, $restQuery);
            if (!$this->cargo) {
                throw new NotFoundHttpException();
            }

            $this->cargoLoaded = true;
        }

        $cargo = $this->cargo->toArray();
        $cargo = array_merge(
            $cargo,
            $this->priceQtyInfo($this->cargo),
            $this->loadHistory()
        );
        try {
            foreach (['created_at', 'updated_at'] as $date) {
                $cargo[$date] = (new Carbon($date))->format('H:i:s Y-m-d');
            }
        } catch (\Throwable $e) {
            //
        }

        // TODO: Проверку заявки на вызов курьера поддерживает только CDEK //
        if ($cargo['delivery_service']['id'] == DeliveryService::SERVICE_CDEK) {
            $cargo['delivery_service']['support_courier_check'] = true;
        }

        return [
            'cargo' => $cargo,
        ];
    }

    protected function getHistory(): Collection
    {
        /** @var CargoService $cargoService */
        $cargoService = resolve(CargoService::class);

        return $cargoService->cargoHistory($this->id);
    }

    /**
     * Информация о цене, складе, кол-ве товара и кол-ве единиц товара отправления
     * @return array
     */
    public function priceQtyInfo(CargoDto $cargo): array
    {
        /** @var StoreService $storeService */
        $storeService = resolve(StoreService::class);

        $restQuery = $storeService->newQuery();
        $restQuery->addFields(StoreDto::entity(), 'id', 'name');
        $store = $storeService->store($cargo->store_id, $restQuery);

        $cost = $discount = $totalQty = $weight = 0;
        if ($cargo->shipments()) {
            foreach ($cargo->shipments() as $shipment) {
                $cost += $shipment->cost;
                $weight += $shipment->weight;

                foreach ($shipment->basketItems() as $basketItem) {
                    $discount += $basketItem->discount;
                    $totalQty += $basketItem->qty;
                }
            }
        }

        return [
            'status' => $cargo->status()->toArray(),
            'delivery_service' => DeliveryService::allServices()[$cargo->delivery_service]->toArray(),
            'store' => $store->toArray(),
            'total_qty' => $totalQty,
            'cost' => $cost,
            'weight' => $weight,
            'discount' => $discount,
            'cost_without_discount' => $cost + $discount,
        ];
    }
}
