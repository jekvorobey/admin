<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
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
        OperatorService $operatorService,
        UserService $userService
    )
    {
        $this->title = 'Заявки на регистрацию';

        $query = $this->makeQuery($request);

        return $this->render('Merchant/RegistrationList', [
            'iMerchants' => $this->loadItems($query, $merchantService, $operatorService, $userService),
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
        OperatorService $operatorService,
        UserService $userService
    )
    {
        $query = $this->makeQuery($request);
        $data = [
            'items' => $this->loadItems($query, $merchantService, $operatorService, $userService),
        ];
        if (1 == $request->get('page', 1)) {
            $data['pager'] = $merchantService->merchantsCount($query);
        }
        return response()->json($data);
    }

    protected function loadItems(
        RestQuery $query,
        MerchantService $merchantService,
        OperatorService $operatorService,
        UserService $userService
    )
    {
        $merchants = $merchantService->merchants($query);
        $merchantIds = $merchants->pluck('id')->all();
        $operatorsQuery = (new RestQuery())
            ->setFilter('merchant_id', $merchantIds)
            ->addFields(OperatorDto::entity(), 'id', 'merchant_id', 'user_id');
        $operators = $operatorService
            ->operators($operatorsQuery)
            ->groupBy('merchant_id')
            ->mapWithKeys(function (Collection $operators) {
                /** @var OperatorDto $first */
                $first = $operators->sortBy('id')->first();
                return [$first->merchant_id => $first];
            });

        $users = $userService
            ->users((new RestQuery())->setFilter('id', $operators->pluck('user_id')->all()))
            ->keyBy('id');

        return $merchants->map(function (MerchantDto $merchant) use ($operators, $users) {
            /** @var OperatorDto $operator */
            $operator = $operators->get($merchant->id);
            $merchant['operator'] = $operator;
            $merchant['user'] = $users->get($operator->user_id);
            return $merchant;
        });
    }

    protected function makeQuery(Request $request)
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->setFilter('status', '!=', MerchantStatus::STATUS_DONE);
        $query->pageNumber($page, 10);

        $filter = $request->get('filter');

        if (isset($filter['status'])) {
            $query->setFilter('status', $filter['status']);
        }

        if (isset($filter['name'])) {
            $query->setFilter('display_name', 'like', "%{$filter['name']}%");
        }

        $query->addSort('id', 'desc');

        return $query;
    }
}