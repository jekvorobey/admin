<?php

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\MerchantStatus;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
use MerchantManagement\Services\MerchantService\Dto\RegisterNewMerchantDto;
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
        $this->loadMerchantStatuses = true;
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);
        /** @var UserService $userService */
        $userService = resolve(UserService::class);

        $managers = $userService->users((new RestQuery())->setFilter('role', UserDto::ADMIN__MANAGER_MERCHANT));

        $query = $this->makeQuery($done);

        return $this->render('Merchant/List', [
            'done' => $done,
            'iMerchants' => $this->loadItems($query),
            'iPager' => is_null($query) ? 0 : $merchantService->merchantsCount($query),
            'iCurrentPage' => request()->get('page', 1),
            'iFilter' => request()->get('filter', []),
            'managers' => $managers->mapWithKeys(function (UserDto $user) {
                return [$user->id => $user->full_name];
            }),
            'options' => [
                'statuses' => MerchantStatus::statusesByMode(!$done),
                'ratings' => $merchantService->ratings(),
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
            $data['pager'] = is_null($query) ? 0 : $merchantService->merchantsCount($query);
        }

        return response()->json($data);
    }

    public function status(MerchantService $merchantService)
    {
        $data = $this->validate(request(), [
            'ids' => 'array',
            'ids.' => 'numeric',
            'status' => Rule::in(array_keys(MerchantStatus::allStatuses()))
        ]);

        foreach ($data['ids'] as $id) {
            $merchantService->update(new MerchantDto([
                'id' => $id,
                'status' => $data['status']
            ]));
        }

        return response('', 204);
    }

    protected function loadItems(?RestQuery $query)
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);
        /** @var OperatorService $operatorService */
        $operatorService = resolve(OperatorService::class);
        /** @var UserService $userService */
        $userService = resolve(UserService::class);

        if (is_null($query)) {
            return collect();
        }


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
        $query->setFilter('status', array_keys(MerchantStatus::statusesByMode(!$done)));
        $query->include('rating');
        $query->pageNumber($page, 10);

        $filter = request()->get('filter');

        if (isset($filter['created_at']) && array_filter($filter['created_at'])) {
            $query->setFilter('created_at', '>=', $filter['created_at'][0]);
            $query->setFilter('created_at', '<=', $filter['created_at'][1]);
        }
        if (isset($filter['rating'])) {
            $query->setFilter('rating_id', $filter['rating']);
        }
        if (isset($filter['status'])) {
            $query->setFilter('status', $filter['status']);
        }
        if (isset($filter['legal_name'])) {
            $query->setFilter('legal_name', 'like', "%{$filter['legal_name']}%");
        }
        if (isset($filter['operator_first_name']) || isset($filter['operator_last_name']) || isset($filter['operator_middle_name']) || isset($filter['operator_email']) || isset($filter['operator_phone'])) {
            $userQuery = new RestQuery();

            if (isset($filter['operator_first_name'])) {
                $userQuery->setFilter('first_name', 'like', "%{$filter['operator_first_name']}%");
            }
            if (isset($filter['operator_last_name'])) {
                $userQuery->setFilter('last_name', 'like', "%{$filter['operator_last_name']}%");
            }
            if (isset($filter['operator_middle_name'])) {
                $userQuery->setFilter('middle_name', 'like', "%{$filter['operator_middle_name']}%");
            }
            if (isset($filter['operator_email'])) {
                $userQuery->setFilter('email', $filter['operator_email']);
            }
            if (isset($filter['operator_phone'])) {
                $userQuery->setFilter('phone', phone_format($filter['operator_phone']));
            }
            /** @var UserService $userService */
            $userService = resolve(UserService::class);
            $users = $userService
                ->users(
                    $userQuery->setFilter('role', [UserDto::MAS__MERCHANT_ADMIN])
                )
                ->pluck('id')
                ->all();

            if (!$users) {
                return null;
            }

            /** @var OperatorService $operatorService */
            $operatorService = resolve(OperatorService::class);
            $merchants = $operatorService
                ->operators(
                    (new RestQuery())->setFilter('user_id', $users)->addFields(OperatorDto::entity(), 'merchant_id')
                )
                ->pluck('merchant_id')
                ->all();

            if (!$merchants) {
                return null;
            }
            $query->setFilter('id', $merchants);
        }
        if (isset($filter['id'])) {
            $query->setFilter('id', $filter['id']);
        }

        $query->addSort('id', 'desc');

        if (isset($filter['manager_id'])) {
            $query->setFilter('manager_id', $filter['manager_id']);
        }

        return $query;
    }

    /**
     * Создать нового мерчанта
     * @param MerchantService $merchantService
     * @param UserService $userService
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMerchant(
        MerchantService $merchantService,
        UserService $userService
    )
    {
        $data = $this->validate(request(), [
            'legal_name' => 'required|string',
            'inn' => 'required|string|size:10',
            'kpp' => 'required|string|size:9',
            'legal_address' => 'required|string',
            'fact_address' => 'required|string',

            'payment_account' => 'required|string|size:20',
            'bank' => 'required|string',
            'bank_address' => 'required|string',
            'bank_bik' => 'required|string|size:9',
            'correspondent_account' => 'required|string|size:20',

            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|regex:/\+\d\(\d\d\d\)\s\d\d\d-\d\d-\d\d/',
            'password' => 'required|string|min:8|confirmed',

            'storage_address' => 'required|string',
            'site' => 'required|string',
            'can_integration' => 'boolean',
            'sale_info' => 'required|string',
        ]);

        $merchantId = $merchantService->createMerchant(
            (new RegisterNewMerchantDto())
                ->setLegalName($data['legal_name'])
                ->setInn($data['inn'])
                ->setKpp($data['kpp'])
                ->setLegalAddress($data['legal_address'])
                ->setFactAddress($data['fact_address'])
                ->setPaymentAccount($data['payment_account'])
                ->setBank($data['bank'])
                ->setBankAddress($data['bank_address'])
                ->setBankBik($data['bank_bik'])
                ->setCorrespondentAccount($data['correspondent_account'])
                ->setFirstName($data['first_name'])
                ->setLastName($data['last_name'])
                ->setMiddleName($data['middle_name'])
                ->setEmail($data['email'])
                ->setPhone(phone_format($data['phone']))
                ->setPassword($data['password'])
                ->setStorageAddress($data['storage_address'])
                ->setSite($data['site'])
                ->setCanIntegration((bool)$data['can_integration'])
                ->setSaleInfo($data['sale_info'])
        );

        $query = $this->makeQuery(true)
            ->setFilter('id', $merchantId);

        $this->loadItems($query);

        return response()->json([
            'merchant' => $this->loadItems($query),
        ]);
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