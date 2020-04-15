<?php

namespace App\Http\Controllers\Logistics;


use App\Http\Controllers\Controller;
use Greensight\Logistics\Dto\Lists\DeliveryKpi\DeliveryKpiCtDto;
use Greensight\Logistics\Dto\Lists\DeliveryKpi\DeliveryKpiDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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
    public function index() {
        return $this->render('Logistics/DeliveryKpi/Index');
    }

    /**
     * @param  ListsService  $listsService
     * @return JsonResponse
     */
    public function getMain(ListsService $listsService): JsonResponse
    {
        $deliveryKpiDto = $listsService->getDeliveryKpi();

        return response()->json([
            'deliveryKpi' => [
                'rtg' => $deliveryKpiDto->rtg,
                'ct' => $deliveryKpiDto->ct,
                'ppt' => $deliveryKpiDto->ppt,
            ]
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

        return  response('', 204);
    }

    /**
     * @param  ListsService  $listsService
     * @param  MerchantService  $merchantService
     * @return JsonResponse
     */
    public function getCt(ListsService $listsService, MerchantService $merchantService): JsonResponse
    {
        $deliveryKpiCts = $listsService->getDeliveryKpiCt();
        $merchantIds = [];
        foreach ($deliveryKpiCts as $deliveryKpiCtDto) {
            $merchantIds[] = $deliveryKpiCtDto->merchant_id;
        }
        $merchantIds = array_unique($merchantIds);
        $merchantQuery = $merchantService->newQuery()
            ->addFields(MerchantDto::entity(), 'id', 'legal_name');
        $merchants = $merchantService->merchants($merchantQuery)->keyBy('id');

        return response()->json([
            'deliveryKpiCts' => $deliveryKpiCts->map(function (DeliveryKpiCtDto $deliveryKpiCtDto) use ($merchants) {
                return [
                    'merchant_id' => $deliveryKpiCtDto->merchant_id,
                    'merchant' => $merchants->has($deliveryKpiCtDto->merchant_id)
                        ?
                        $merchants[$deliveryKpiCtDto->merchant_id]
                        : [
                            'id' => $deliveryKpiCtDto->merchant_id,
                            'name' => 'N/A'
                        ],
                    'ct' => $deliveryKpiCtDto->ct,
                ];
            }),
            'merchants' => $merchants->filter(function (MerchantDto $merchantDto) use ($merchantIds) {
                return !in_array($merchantDto->id, $merchantIds);
            })->values(),
        ]);
    }

    /**
     * @param  ListsService  $listsService
     * @param  MerchantService  $merchantService
     * @return JsonResponse
     */
    public function setCt(ListsService $listsService, MerchantService $merchantService): JsonResponse
    {
        $data = $this->validate(request(), [
            'merchant_id' => ['required', 'integer'],
            'ct' => ['required', 'integer', 'min:0'],
        ]);

        $deliveryKpiCtDto = new DeliveryKpiCtDto();
        $deliveryKpiCtDto->merchant_id = $data['merchant_id'];
        $deliveryKpiCtDto->ct = $data['ct'];

        $listsService->setDeliveryKpiCt($deliveryKpiCtDto);

        return $this->getCt($listsService, $merchantService);
    }
}