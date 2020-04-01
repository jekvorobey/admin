<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use MerchantManagement\Core\MerchantException;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\MerchantStatus;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MerchantListController extends Controller
{
    public function registration()
    {
        $this->title = 'Заявки на регистрацию';

        return $this->list(false);
    }

    public function active()
    {
        $this->title = 'Список мерчантов';

        return $this->list(true);
    }

    protected function list($done)
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        $query = $this->makeQuery($done);

        return $this->render('Merchant/List', [
            'done' => $done,
            'iMerchants' => $this->loadItems($query),
            'iPager' => $merchantService->merchantsCount($query),
            'iCurrentPage' => request()->get('page', 1),
            'iFilter' => request()->get('filter', []),
            'options' => [
                'statuses' => MerchantStatus::allStatuses(),
            ],
        ]);
    }

    public function page(MerchantService $merchantService)
    {
        $query = $this->makeQuery(request('done'));
        $data = [
            'items' => $this->loadItems($query),
        ];
        if (1 == request()->get('page', 1)) {
            $data['pager'] = $merchantService->merchantsCount($query);
        }

        return response()->json($data);
    }

    protected function loadItems(RestQuery $query)
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);
        /** @var OperatorService $operatorService */
        $operatorService = resolve(OperatorService::class);
        /** @var UserService $userService */
        $userService = resolve(UserService::class);


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

    protected function makeQuery($done)
    {
        $query = new RestQuery();
        $page = request()->get('page', 1);
        $query->setFilter('status', $done ? '=' : '!=', MerchantStatus::STATUS_DONE);
        $query->pageNumber($page, 10);

        $filter = request()->get('filter');

        if (isset($filter['status'])) {
            $query->setFilter('status', $filter['status']);
        }

        if (isset($filter['name'])) {
            $query->setFilter('display_name', 'like', "%{$filter['name']}%");
        }

        $query->addSort('id', 'desc');

        return $query;
    }

    /**
     * Создать нового мерчанта
     * @param Request $request
     * @param MerchantService $merchantService
     * @return \Illuminate\Http\JsonResponse
     * @throws MerchantException
     */
    public function createMerchant(
        Request $request,
        MerchantService $merchantService
    )
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'company' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|regex:/\+\d\(\d\d\d\)\s\d\d\d-\d\d-\d\d/',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }

        $merchantService->registerNewMerchant(
            $data['company'],
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'],
            $data['email'],
            $data['email'],
            phone_format($data['phone']),
            $data['password']
        );

        return response()->json([]);
    }

    public function checkEmailExists(Request $request, UserService $userService)
    {
        $email = $request->get('email', false);
        if (!$email) {
            throw new BadRequestHttpException('email is empty');
        }

        $exists = $userService->exists($email, Front::FRONT_MAS);

        return response()->json([
            'exists' => $exists
        ]);
    }
}