<?php

namespace App\Http\Controllers\Logistics\DeliveryService;

use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Logistics\Dto\Lists\DeliveryService;
use Greensight\Logistics\Dto\Lists\DeliveryServiceStatus;
use Greensight\Logistics\Services\ListsService\ListsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class DeliveryServiceListController
 * @package App\Http\Controllers\Logistics\DeliveryService
 */
class DeliveryServiceListController extends Controller
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request, ListsService $listsService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_LOGISTICS);

        $this->title = 'Логистические операторы';
        $restQuery = $this->makeRestQuery($listsService, true);
        $pager = $listsService->deliveryServicesCount($restQuery);
        $deliveryServices = $this->loadDeliveryServices($restQuery, $listsService);

        return $this->render('Logistics/DeliveryService/List', [
            'iDeliveryServices' => $deliveryServices,
            'iCurrentPage' => $this->getPage(),
            'iFilter' => $this->getFilter(true),
            'iPager' => $pager,
            'deliveryServiceStatuses' => DeliveryServiceStatus::allStatuses(),
            'iSort' => $request->get('sort', 'created_at'),
        ]);
    }

    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    /**
     * @throws Exception
     */
    public function page(ListsService $listsService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_LOGISTICS);

        $restQuery = $this->makeRestQuery($listsService);
        $deliveryServices = $this->loadDeliveryServices($restQuery, $listsService);
        $result = [
            'deliveryServices' => $deliveryServices,
        ];
        if ($this->getPage() == 1) {
            $result['pager'] = $listsService->deliveryServicesCount($restQuery);
        }

        return response()->json($result);
    }

    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::make(
            request('filter') ??
            ($withDefault ?
                [
                    'status' => [DeliveryServiceStatus::ACTIVE],
                ] : []),
            [
                'id' => 'integer|someone',
                'name' => 'string|someone',
                'status' => Rule::in(array_keys(DeliveryServiceStatus::allStatuses())),
            ]
        )->attributes();
    }

    /**
     * @return Collection|DeliveryService[]
     */
    protected function loadDeliveryServices(DataQuery $restQuery, ListsService $listsService): Collection
    {
        $restQuery->addFields(
            DeliveryService::entity(),
            'id',
            'name',
            'status',
            'priority',
            'pickup_priority'
        );
        $deliveryServices = $listsService->deliveryServices($restQuery);

        return $deliveryServices->map(function (DeliveryService $deliveryService) {
            $data = $deliveryService->toArray();

            $data['status'] = $deliveryService->status()->toArray();

            return $data;
        });
    }

    /**
     * @throws Exception
     */
    protected function makeRestQuery(ListsService $listsService, bool $withDefaultFilter = false): DataQuery
    {
        /** @var RestQuery $restQuery */
        $restQuery = $listsService->newQuery();

        $page = $this->getPage();
        $restQuery->pageNumber($page, 20)->addSort('priority', 'asc');

        $filter = $this->getFilter($withDefaultFilter);
        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'name':
                        $restQuery->setFilter($key, 'like', "%{$value}%");
                        break;

                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
        }

        return $restQuery;
    }
}
