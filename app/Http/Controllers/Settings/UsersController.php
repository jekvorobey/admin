<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRolesAddRequest;
use App\Http\Requests\UserRolesDeleteRequest;
use App\Http\Requests\UserSaveRequest;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\RoleDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\CommonMsa\Services\RoleService\RoleService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\OperatorService\OperatorService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UsersController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function index(Request $request, UserService $userService, RoleService $roleService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        $this->title = 'Список пользователей';

        $query = $this->makeQuery($request);

        return $this->render('Settings/UserList', [
            'iUsers' => $this->loadItems($query, $userService),
            'iPager' => $userService->count($query),
            'iCurrentPage' => $request->get('page', 1),
            'options' => [
                'fronts' => Front::allFronts(),
                'roles' => $roleService->roles(),
            ],
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function page(Request $request, UserService $userService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        $query = $this->makeQuery($request);
        $data = [
            'items' => $this->loadItems($query, $userService),
        ];
        if ($request->get('page', 1) == 1) {
            $data['pager'] = $userService->count($query);
        }
        return response()->json($data);
    }

    /**
     * @return mixed
     */
    public function detail(int $id, UserService $userService, RoleService $roleService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        $userQuery = new RestQuery();
        $userQuery->setFilter('id', $id);
        /** @var UserDto $user */
        $user = $userService->users($userQuery)->first();

        if (!$user) {
            throw new NotFoundHttpException('user not found');
        }

        $this->title = "Пользователь: {$user->full_name}";

        $userRoles = $userService->userRoles($id);
        $roles = $roleService->roles();

        return $this->render('Settings/UserDetail', [
            'iUser' => $user,
            'iRoles' => $userRoles,
            'options' => [
                'fronts' => Front::allFronts(),
                'roles' => $roles,
            ],
        ]);
    }

    public function saveUser(
        UserSaveRequest $request,
        UserService $userService,
        CustomerService $customerService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $newUser = new UserDto($request->all());
        if (isset($request->id)) {
            $userId = $request->id;
            $userService->update($newUser);
        } else {
            $userId = $userService->create($newUser);
        }
        $userService->addRoles($userId, $request->roles);
        if (in_array(Front::FRONT_SHOWCASE, $request->fronts)) {
            $customer = $customerService->customers((new RestQuery())->setFilter('id', $userId))->first();
            if ($customer) {
                $customerService->createCustomer(new CustomerDto(['user_id' => $userId]));
            }
        }

        return response()->json([]);
    }

    public function addRoles(int $id, UserRolesAddRequest $request, UserService $userService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $userService->addRoles($id, $request->roles);

        return response()->json([
            'roles' => $userService->userRoles($id),
        ]);
    }

    public function deleteRoles(int $id, UserRolesDeleteRequest $request, UserService $userService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $userService->deleteRoles($id, $request->roles);

        return response()->json([
            'roles' => $userService->userRoles($id),
        ]);
    }

    public function banUser(int $id, UserService $userService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $userService->ban($id);

        return response()->json([]);
    }

    public function unBanUser(int $id, UserService $userService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $userService->unban($id);

        return response()->json([]);
    }

    /**
     * Получение пользователей по массиву ролей
     */
    public function usersByRoles(
        UserService $userService,
        OperatorService $operatorService,
        RequestInitiator $user
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        $data = $this->validate(request(), [
            'role_ids' => 'required|array',
            'role_ids.' => 'integer',
        ]);

        $users = $userService->users(
            (new RestQuery())
                ->addFields(UserDto::class, 'id', 'full_name', 'phone', 'email')
                ->setFilter('id', '!=', $user->userId())
                ->setFilter('role', $data['role_ids'])
        );
        $user_ids = $users->keyBy('id')->keys()->toArray();

        // У сотрудников мерчанта подгружается информация о доступности SMS-канала //
        $operators = null;
        if (
            in_array(RoleDto::ROLE_MAS_MERCHANT_OPERATOR, $data['role_ids'])
            || (in_array(RoleDto::ROLE_MAS_MERCHANT_ADMIN, $data['role_ids']))
        ) {
            $operators = $operatorService->operators(
                (new RestQuery())->setFilter('user_id', $user_ids)
            )->keyBy('user_id');
        }

        $users = $users->map(function (UserDto $user) use ($operators) {
            $sms_status = null;
            $merchant_id = null;
            if (isset($operators)) {
                /** @var OperatorDto $operator */
                foreach ($operators as $operator) {
                    if ($user->id == $operator->user_id) {
                        $sms_status = $operator->is_receive_sms;
                        $merchant_id = $operator->merchant_id;
                    }
                }
            }
            return [
                'id' => $user->id,
                'title' => $user->getTitle(),
                'email' => $user->email,
                'receive_sms' => $sms_status,
                'merchant_id' => $merchant_id,
            ];
        })->keyBy('id')->all();

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * @throws ValidationException
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::validate(
            request('filter') ?? [],
            [
                'id' => 'integer',
                'full_name' => 'string',
                'email' => 'string',
                'phone' => 'string',
                'front' => 'integer',
                'role' => 'integer',
            ]
        );
    }

    /**
     * @throws ValidationException
     */
    protected function makeQuery(Request $request, bool $withDefaultFilter = false): RestQuery
    {
        $restQuery = new RestQuery();
        $restQuery->pageNumber($request->get('page', 1), 10);
        $filter = $this->getFilter($withDefaultFilter);
        foreach ($filter as $key => $value) {
            switch ($key) {
                case 'front':
                    if ($value) {
                        $restQuery->setFilter('fronts', 'like', "%{$value}%");
                    }
                    break;
                case 'phone':
                    $value = phone_format($value);
                    $restQuery->setFilter($key, $value);
                    break;
                default:
                    $restQuery->setFilter($key, $value);
            }
        }

        return $restQuery;
    }

    protected function loadItems(RestQuery $query, UserService $userService)
    {
        return $userService->users($query);
    }
}
