<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\RoleService;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UsersController extends Controller
{
    public function index(Request $request, UserService $userService)
    {
        $this->title = 'Список пользователей';
        $this->breadcrumbs = 'settings.userList';

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

    public function detail(int $id, UserService $userService, RoleService $roleService)
    {
        $userQuery = new RestQuery();
        $userQuery->setFilter('id', $id);
        /** @var UserDto $user */
        $user = $userService->users($userQuery)->first();

        if (!$user) {
            throw new NotFoundHttpException('user not found');
        }

        $this->title = "Пользователь № {$user->id}";
        $this->breadcrumbs = ['settings.userDetail', $user->id];

        $userRoles = $userService->userRoles($id);

        return $this->render('Settings/UserDetail', [
            'iUser' => $user,
            'iRoles' => $userRoles,
            'options' => [
                'fronts' => Front::allFronts(),
                'roles' => $roleService->roles()
            ]
        ]);
    }

    public function saveUser(Request $request, UserService $userService)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'id' => 'nullable|integer',
            'email' => 'required|email',
            'front' => ['required', Rule::in(array_keys(Front::allFronts()))],
            'password' => 'required_without:id',
        ]);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }
        $newUser = new UserDto($data);
        if ($data['id']) {
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