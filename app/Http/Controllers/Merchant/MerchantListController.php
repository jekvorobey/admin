<?php

namespace App\Http\Controllers\Merchant;

use App\Core\Helpers;
use App\Core\UserHelper;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\RoleDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Oms\Dto\Payment\PaymentMethod;
use Greensight\Oms\Services\PaymentService\PaymentService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\MerchantStatus;
use MerchantManagement\Dto\OperatorCommunicationMethod;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\MerchantService\Dto\RegisterNewMerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MerchantListController extends Controller
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function registration()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $this->title = 'Заявки на регистрацию';

        return $this->list(false);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function active()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $this->title = 'Список мерчантов';

        return $this->list(true);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function list($done)
    {
        $this->loadMerchantStatuses = true;
        $this->loadCommunicationChannelTypes = true;
        $this->loadCommunicationChannels = true;
        $this->loadCommunicationThemes = true;
        $this->loadCommunicationStatuses = true;
        $this->loadCommunicationTypes = true;
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);
        /** @var UserService $userService */
        $userService = resolve(UserService::class);
        /** @var PaymentService $paymentService */
        $paymentService = resolve(PaymentService::class);

        $managers = $userService->users((new RestQuery())->setFilter('role', RoleDto::ROLE_KAM));
        $paymentMethods = $paymentService->getPaymentMethods(
            (new RestQuery())
                ->addFields(PaymentMethod::entity(), 'id', 'name')
                ->setFilter('active', true)
        );

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
                'communicationMethods' => OperatorCommunicationMethod::allMethods(),
                'paymentMethodList' => Helpers::getSelectOptions($paymentMethods),
            ],
        ]);
    }

    public function page(MerchantService $merchantService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $query = $this->makeQuery(request('done'));
        $data = [
            'items' => $this->loadItems($query),
        ];
        if (request()->get('page', 1) == 1) {
            $data['pager'] = is_null($query) ? 0 : $merchantService->merchantsCount($query);
        }

        return response()->json($data);
    }

    public function status(MerchantService $merchantService): Response|Application|ResponseFactory
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $this->validate(request(), [
            'ids' => 'array',
            'ids.' => 'numeric',
            'status' => Rule::in(array_keys(MerchantStatus::allStatuses())),
        ]);

        foreach ($data['ids'] as $id) {
            $merchantService->update(new MerchantDto([
                'id' => $id,
                'status' => $data['status'],
            ]));
        }

        return response('', 204);
    }

    protected function loadItems(?RestQuery $query): Collection
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);
        /** @var OperatorService $operatorService */
        $operatorService = resolve(OperatorService::class);

        if (is_null($query)) {
            return collect();
        }

        $merchants = $merchantService->merchants($query);
        if ($merchants->isEmpty()) {
            return collect();
        }

        $merchantIds = $merchants->pluck('id')->all();
        $operatorsQuery = (new RestQuery())
            ->setFilter('merchant_id', $merchantIds)
            ->addFields(OperatorDto::entity(), 'id', 'merchant_id', 'user_id');
        $operators = $operatorService
            ->operators($operatorsQuery);
        $operatorsByMerchant = $operators
            ->groupBy('merchant_id');
        $mainOperators = $operatorsByMerchant
            ->mapWithKeys(function (Collection $operators) {
                /** @var OperatorDto $first */
                $first = $operators->where('is_main', true)->first() ? : $operators->sortBy('id')->first();

                return [$first->merchant_id => $first];
            });

        $users = UserHelper::getUsersByIds($operators->pluck('user_id')->all());

        return $merchants->map(function (MerchantDto $merchant) use ($mainOperators, $operatorsByMerchant, $users) {
            /** @var OperatorDto $operator */
            $operator = $mainOperators->get($merchant->id);
            $merchant['operator'] = $operator ? : [];
            $merchant['user'] = $operator ? $users->get($operator->user_id) : [];
            $merchant['users'] = $users->filter(function ($user) use ($operatorsByMerchant, $merchant) {
                $haystack = $operatorsByMerchant->get($merchant->id);
                return in_array($user->id, $haystack ? $haystack->pluck('user_id')->all() : []);
            });

            return $merchant;
        });
    }

    protected function makeQuery($done): ?RestQuery
    {
        $query = new RestQuery();
        $page = request()->get('page', 1);
        $query->setFilter('status', array_keys(MerchantStatus::statusesByMode(!$done)));
        $query->include('rating');
        $query->pageNumber($page, 10);

        $filter = request()->get('filter');

        if (isset($filter['created_at']) && array_filter($filter['created_at'])) {
            $query->setFilter('created_at', '>=', $filter['created_at'][0]);
            $query->setFilter('created_at', '<=', end_of_day_filter($filter['created_at'][1]));
        }
        if (isset($filter['rating'])) {
            $query->setFilter('rating_id', $filter['rating']);
        }
        if (isset($filter['status'])) {
            $query->setFilter('status', $filter['status']);
        }
        if (isset($filter['name'])) {
            $query->setFilter('name', 'like', "%{$filter['name']}%");
        }
        if (isset($filter['operator_full_name']) || isset($filter['operator_email']) || isset($filter['operator_phone'])) {
            $userQuery = new RestQuery();

            if (isset($filter['operator_full_name'])) {
                $userQuery->setFilter('full_name', "%{$filter['operator_full_name']}%");
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
                    $userQuery->setFilter('role', [RoleDto::ROLE_MAS_MERCHANT_ADMIN])
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
     */
    public function createMerchant(MerchantService $merchantService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $this->validate(request(), [
            'legal_name' => 'required|string',
            'inn' => ['required', 'regex:/^\d{10}(\d{2})?$/'],
            'kpp' => 'string|size:9|nullable',
            'legal_address' => 'required|string',
            'fact_address' => 'required|string',

            'payment_account' => 'required|string|size:20',
            'bank' => 'required|string',
            'bank_address' => 'required|string',
            'bank_bik' => 'required|string|size:9',
            'correspondent_account' => 'required|string|size:20',

            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'middle_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|regex:/^\+7\d{10}$/',
            'communication_method' => [
                'nullable',
                Rule::in(array_keys(OperatorCommunicationMethod::allMethods())),
            ],
            //'password' => 'required|string|min:8|confirmed',

            'storage_address' => 'required|string',
            'site' => 'required|string',
            'can_integration' => 'boolean',
            'sale_info' => 'required|string',
            'excluded_payment_methods' => 'array|nullable',
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
                ->setPhone($data['phone'] ? phone_format($data['phone']) : null)
                //->setPassword($data['password'])
                ->setStorageAddress($data['storage_address'])
                ->setSite($data['site'])
                ->setCanIntegration((bool) $data['can_integration'])
                ->setSaleInfo($data['sale_info'])
                ->setCommunicationMethod($data['communication_method'])
                ->setExcludedPaymentMethods($data['excluded_payment_methods'])
        );

        if (!$merchantId) {
            throw new BadRequestHttpException('Ошибка создания мерчанта');
        }

        return response()->json([
            'redirect' => route('merchant.detail', ['id' => $merchantId]),
        ]);
    }
}
