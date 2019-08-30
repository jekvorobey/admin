<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\MerchantStatus;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;

class RegistrationRequestController extends Controller
{
    public function index(
        Request $request,
        MerchantService $merchantService,
        OperatorService $operatorService
    )
    {
        $this->title = 'Заявки на регистрацию';
        $this->breadcrumbs = 'merchant.registrationList';

        $query = $this->makeQuery($request);

        return $this->render('Merchant/RegistrationList', [
            'iMerchants' => $this->loadItems($query, $merchantService, $operatorService),
            'iPager' => $merchantService->merchantsCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'statuses' => MerchantStatus::allStatuses()
            ]
        ]);
    }

    public function page(
        Request $request,
        MerchantService $merchantService,
        OperatorService $operatorService
    )
    {
        $query = $this->makeQuery($request);
        $data = [
            'items' => $this->loadItems($query, $merchantService, $operatorService),
        ];
        if (1 == $request->get('page', 1)) {
            $data['pager'] = $merchantService->merchantsCount($query);
        }
        return response()->json($data);
    }

    protected function loadItems(
        RestQuery $query,
        MerchantService $merchantService,
        OperatorService $operatorService
    )
    {
        $merchants = $merchantService->merchants($query);
        $merchantIds = $merchants->pluck('id')->all();
        $operatorsQuery = (new RestQuery())
            ->setFilter('merchant_id', $merchantIds)
            ->addFields(OperatorDto::entity(), 'id', 'name', 'merchant_id');
        $operators = $operatorService
            ->operators($operatorsQuery)
            ->groupBy('merchant_id')
            ->mapWithKeys(function (Collection $operators) {
                /** @var OperatorDto $first */
                $first = $operators->sortBy('id')->first();
                return [$first->merchant_id => $first];
            });

        $items = $merchants->map(function (MerchantDto $merchant) use ($operators) {
            $merchant['operator'] = $operators->get($merchant->id);
            return $merchant;
        });
        return $items;
    }

    protected function makeQuery(Request $request)
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->setFilter('status', '!=', MerchantStatus::STATUS_DONE);
        $query->pageNumber($page, 3);

        $filter = $request->get('filter');

        if (isset($filter['status'])) {
            $query->setFilter('status', $filter['status']);
        }

        if (isset($filter['name'])) {
            $query->setFilter('display_name', 'like', "%{$filter['name']}%");
        }

        return $query;
    }
}