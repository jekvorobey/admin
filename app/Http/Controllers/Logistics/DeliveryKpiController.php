<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Greensight\Logistics\Dto\Lists\DeliveryKpi\DeliveryKpiCtDto;
use Greensight\Logistics\Dto\Lists\DeliveryKpi\DeliveryKpiDto;
use Greensight\Logistics\Dto\Lists\DeliveryKpi\DeliveryKpiPptDto;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Dto\Lists\DeliveryServiceStatus;
use Greensight\Logistics\Services\ListsService\ListsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;

/**
 * Class DeliveryKpiController
 * @package App\Http\Controllers\Logistics
 */
class DeliveryKpiController extends Controller
{
    /**
     * @return mixed
     */
    public function index()
    {
        return $this->render('Logistics/DeliveryKpi/Index');
    }

    public function getMain(ListsService $listsService): JsonResponse
    {
        $deliveryKpiDto = $listsService->getDeliveryKpi();

        return response()->json([
            'deliveryKpi' => [
                'rtg' => $deliveryKpiDto->rtg,
                'ct' => $deliveryKpiDto->ct,
                'ppt' => $deliveryKpiDto->ppt,
            ],
        ]);
    }

    /**
     * @param  ListsService  $listsService
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function setMain(ListsService $listsService): Response
    {
        $data = $this->validate(request(), [
            'rtg' => ['required', 'integer', 'min:0'],
            'ct' => ['required', 'integer', 'min:0'],
            'ppt' => ['required', 'integer', 'min:0'],
        ]);

        $deliveryKpiDto = new DeliveryKpiDto();
        $deliveryKpiDto->rtg = $data['rtg'];
        $deliveryKpiDto->ct = $data['ct'];
        $deliveryKpiDto->ppt = $data['ppt'];

        $listsService->setDeliveryKpi($deliveryKpiDto);

        return response('', 204);
    }

    public function getCt(ListsService $listsService): JsonResponse
    {
        $deliveryKpiCts = $listsService->getDeliveryKpiCt();
        $merchantIds = [];
        foreach ($deliveryKpiCts as $deliveryKpiCtDto) {
            $merchantIds[] = $deliveryKpiCtDto->merchant_id;
        }
        $merchantIds = array_unique($merchantIds);
        $merchants = $this->getAllMerchants();

        return response()->json([
            'deliveryKpiCts' => $deliveryKpiCts->map(function (DeliveryKpiCtDto $deliveryKpiCtDto) use ($merchants) {
                return [
                    'merchant_id' => $deliveryKpiCtDto->merchant_id,
                    'merchant' => $merchants->has($deliveryKpiCtDto->merchant_id)
                        ?
                        $merchants[$deliveryKpiCtDto->merchant_id]
                        : [
                            'id' => $deliveryKpiCtDto->merchant_id,
                            'name' => 'N/A',
                        ],
                    'ct' => $deliveryKpiCtDto->ct,
                ];
            }),
            'merchants' => $merchants->filter(function (MerchantDto $merchantDto) use ($merchantIds) {
                return !in_array($merchantDto->id, $merchantIds);
            })->values(),
        ]);
    }

    public function setCt(ListsService $listsService): JsonResponse
    {
        $data = $this->validate(request(), [
            'merchant_id' => ['required', 'integer'],
            'ct' => ['required', 'integer', 'min:0'],
        ]);

        $deliveryKpiCtDto = new DeliveryKpiCtDto();
        $deliveryKpiCtDto->merchant_id = $data['merchant_id'];
        $deliveryKpiCtDto->ct = $data['ct'];

        $listsService->setDeliveryKpiCt($deliveryKpiCtDto);

        return $this->getCt($listsService);
    }

    public function getPpt(ListsService $listsService): JsonResponse
    {
        $deliveryKpiPpts = $listsService->getDeliveryKpiPpt();
        $merchantIds = [];
        foreach ($deliveryKpiPpts as $deliveryKpiPptDto) {
            $merchantIds[] = $deliveryKpiPptDto->merchant_id;
        }
        $merchantIds = array_unique($merchantIds);
        $merchants = $this->getAllMerchants();

        return response()->json([
            'deliveryKpiPpts' => $deliveryKpiPpts->map(function (DeliveryKpiPptDto $deliveryKpiPptDto) use ($merchants) {
                return [
                    'merchant_id' => $deliveryKpiPptDto->merchant_id,
                    'merchant' => $merchants->has($deliveryKpiPptDto->merchant_id)
                        ?
                        $merchants[$deliveryKpiPptDto->merchant_id]
                        : [
                            'id' => $deliveryKpiPptDto->merchant_id,
                            'name' => 'N/A',
                        ],
                    'ppt' => $deliveryKpiPptDto->ct,
                ];
            }),
            'merchants' => $merchants->filter(function (MerchantDto $merchantDto) use ($merchantIds) {
                return !in_array($merchantDto->id, $merchantIds);
            })->values(),
        ]);
    }

    public function setPpt(ListsService $listsService): JsonResponse
    {
        $data = $this->validate(request(), [
            'merchant_id' => ['required', 'integer'],
            'ppt' => ['required', 'integer', 'min:0'],
        ]);

        $deliveryKpiPptDto = new DeliveryKpiPptDto();
        $deliveryKpiPptDto->merchant_id = $data['merchant_id'];
        $deliveryKpiPptDto->ppt = $data['ppt'];

        $listsService->setDeliveryKpiPpt($deliveryKpiPptDto);

        return $this->getPpt($listsService);
    }

    public function getPct(ListsService $listsService): JsonResponse
    {
        $deliveryServiceQuery = $listsService->newQuery()
            ->addFields(DeliveryService::entity(), 'id', 'name', 'pct')
            ->setFilter('status', DeliveryServiceStatus::ACTIVE);
        $deliveryServices = $listsService->deliveryServices($deliveryServiceQuery);

        return response()->json([
            'deliveryKpiPcts' => $deliveryServices->filter(function (DeliveryService $deliveryService) {
                return $deliveryService->pct > 0;
            })->map(function (DeliveryService $deliveryService) {
                return [
                    'delivery_service_id' => $deliveryService->id,
                    'delivery_service' => [
                        'id' => $deliveryService->id,
                        'name' => $deliveryService->name,
                    ],
                    'pct' => $deliveryService->pct,
                ];
            }),
            'deliveryServices' => $deliveryServices->filter(function (DeliveryService $deliveryService) {
                return !$deliveryService->pct;
            })->map(function (DeliveryService $deliveryService) {
                return [
                    'id' => $deliveryService->id,
                    'name' => $deliveryService->name,
                ];
            }),
        ]);
    }

    public function setPct(ListsService $listsService): JsonResponse
    {
        $data = $this->validate(request(), [
            'delivery_service_id' => ['required', 'integer'],
            'pct' => ['required', 'integer', 'min:0'],
        ]);

        $deliveryService = new DeliveryService();
        $deliveryService->pct = $data['pct'];

        $listsService->updateDeliveryService($data['delivery_service_id'], $deliveryService);

        return $this->getPct($listsService);
    }

    /**
     * @return Collection|MerchantDto[]
     */
    protected function getAllMerchants(): Collection
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);
        $merchantQuery = $merchantService->newQuery()
            ->addFields(MerchantDto::entity(), 'id', 'legal_name');

        return $merchantService->merchants($merchantQuery)->keyBy('id');
    }
}
