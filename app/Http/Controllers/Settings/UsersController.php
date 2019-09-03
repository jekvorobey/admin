<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    public function saveUser(Request $request, UserService $userService)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'front' => ['required', Rule::in(array_keys(Front::allFronts()))],
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }
        $newUser = new UserDto($data);
        $userService->create($newUser);
        return response()->json([]);
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