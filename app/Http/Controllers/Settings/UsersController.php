<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\OperatorService\OperatorService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UsersController extends Controller
{
    public function index(Request $request, UserService $userService)
    {
        $this->title = 'Список пользователей';

        $query = $this->makeQuery($request);

        return $this->render('Settings/UserList', [
            'iUsers' => $this->loadItems($query, $userService),
            'iPager' => $userService->count($query),
            'iCurrentPage' => $request->get('page', 1),
            'options' => [
                'fronts' => Front::allFronts()
            ]
        ]);
    }

    public function page(Request $request, UserService $userService)
    {
        $query = $this->makeQuery($request);
        $data = [
            'items' => $this->loadItems($query, $userService),
        ];
        if (1 == $request->get('page', 1)) {
            $data['pager'] = $userService->count($query);
        }
        return response()->json($data);
    }

    public function detail(int $id, UserService $userService)
    {
        $userQuery = new RestQuery();
        $userQuery->setFilter('id', $id);
        /** @var UserDto $user */
        $user = $userService->users($userQuery)->first();

        if (!$user) {
            throw new NotFoundHttpException('user not found');
        }

        $this->title = "Пользователь № {$user->id}";

        $userRoles = $userService->userRoles($id);

        return $this->render('Settings/UserDetail', [
            'iUser' => $user,
            'iRoles' => $userRoles,
            'options' => [
                'fronts' => Front::allFronts(),
                'roles' => UserDto::roles()
            ]
        ]);
    }

    public function saveUser(Request $request, UserService $userService)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'id' => 'nullable|integer',
            'login' => 'required',
            'front' => ['required', Rule::in(array_keys(Front::allFronts()))],
            'password' => 'required_without:id',
            'infinity_sip_extension' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }
        $newUser = new UserDto($data);
        if (isset($data['id'])) {
            $userService->update($newUser);
        } else {
            $userService->create($newUser);
        }

        return response()->json([]);
    }

    public function addRole(int $id, Request $request, UserService $userService)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'role' => 'required|integer',
            'expires' => 'nullable|date'
        ]);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }

        $userService->addRoles($id, [$data['role']], $data['expires'] ?? null);
        return response()->json([
            'roles' => $userService->userRoles($id)
        ]);
    }

    public function deleteRole(int $id, Request $request, UserService $userService)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'role' => 'required|integer',
        ]);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }

        $userService->deleteRole($id, $data['role']);
        return response()->json([
            'roles' => $userService->userRoles($id)
        ]);
    }

    /**
     * Получение пользователей по массиву ролей
     *
     * @param UserService $userService
     * @param OperatorService $operatorService
     * @param RequestInitiator $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function usersByRoles(
        UserService $userService,
        OperatorService $operatorService,
        RequestInitiator $user)
    {
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
            (in_array(UserDto::MAS__MERCHANT_OPERATOR, $data['role_ids']))
            ||(in_array(UserDto::MAS__MERCHANT_ADMIN, $data['role_ids']))
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

    protected function makeQuery(Request $request): RestQuery
    {
        $restQuery = new RestQuery();
        $restQuery->pageNumber($request->get('page',1), 10);
        return $restQuery;
    }

    protected function loadItems(RestQuery $query, UserService $userService)
    {
        $users = $userService->users($query);
        return $users;
    }
}