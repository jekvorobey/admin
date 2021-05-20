<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Services\AuthService\UserService;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\OperatorCommunicationMethod;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;

class MerchantOperatorController extends Controller
{
    public function indexCreate(Request $request, MerchantService $merchantService)
    {
        $data = $request->validate([
            'merchant_id' => 'integer|sometimes',
        ]);

        $this->title = 'Создание оператора';
        $props = [];
        if (array_key_exists('merchant_id', $data)) {
            $props['merchantId'] = intval($data['merchant_id']);
        } else {
            $merchants = $merchantService->merchants(
                (new RestQuery())->addFields(MerchantDto::entity(), 'id', 'legal_name')
            )->all();
            $props['merchants'] = $merchants;
        }
        $props['communicationMethods'] = OperatorCommunicationMethod::allMethods();
        $props['roles'] = UserDto::rolesByFrontIds([Front::FRONT_MAS]);
        return $this->render('Merchant/Operator/CreateEdit', $props);
    }

    public function indexEdit(
        int $operatorId,
        OperatorService $operatorService,
        UserService $userService,
        MerchantService $merchantService
    ) {
        /** @var OperatorDto $operator */
        $operator = $operatorService->operators((new RestQuery())->setFilter('id', $operatorId))
            ->first();

        /** @var UserDto $user */
        $user = $userService->users((new RestQuery())->setFilter('id', $operator->user_id))
            ->first();

        $operator = [
            'id' => $operator->id,
            'merchant_id' => $operator->merchant_id,
            'last_name' => $user->last_name,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'login' => $user->login,
            'position' => $operator->position,
            'communication_method' => $operator->communication_method,
            'roles' => collect($user->roles)->map(function ($item, $roleId) {
                return $roleId;
            })->values()
                ->all(),
            'active' => $user->active,
        ];

        $merchants = $merchantService->merchants(
            (new RestQuery())->addFields(MerchantDto::entity(), 'id', 'legal_name')
        )->all();

        $this->title = 'Редактирование менеджера';
        return $this->render('Merchant/Operator/CreateEdit', [
            'operatorProp' => $operator,
            'merchants' => $merchants,
            'communicationMethods' => OperatorCommunicationMethod::allMethods(),
            'roles' => UserDto::rolesByFrontIds([Front::FRONT_MAS]),
        ]);
    }

    public function save(Request $request, UserService $userService, OperatorService $operatorService)
    {
        $userData = $request->validate([
            'last_name' => 'string|required',
            'first_name' => 'string|required',
            'middle_name' => 'string|required',
            'email' => 'email|required',
            'phone' => 'string|required',
            'login' => 'string|required',
            'password' => 'string|required',
            'roles' => 'array|required',
            'roles.' => Rule::in(UserDto::rolesByFrontIds([Front::FRONT_MAS])),
            'active' => 'bool|required',
        ]);

        $operatorData = $request->validate([
            'merchant_id' => 'integer|required',
            'position' => 'string|nullable',
            'communication_method' => [
                Rule::in(array_keys(OperatorCommunicationMethod::allMethods())),
                'required',
            ],
        ]);

        $userData['phone'] = phone_format($userData['phone']);
        $userData['front'] = Front::FRONT_MAS;
        $userId = $userService->create(new UserDto($userData));
        $userService->addRoles($userId, $userData['roles']);

        $operatorData['user_id'] = $userId;
        $operatorService->create(new OperatorDto($operatorData));

        return response('', 201);
    }

    public function update(
        int $operatorId,
        Request $request,
        OperatorService $operatorService,
        UserService $userService,
        MerchantService $merchantService
    ) {
        $userData = $request->validate([
            'last_name' => 'string',
            'first_name' => 'string',
            'middle_name' => 'string',
            'email' => 'email',
            'phone' => 'string',
            'login' => 'string',
            'password' => 'string',
            'active' => 'bool',
        ]);

        $userRolesData = $request->validate([
            'roles' => 'array',
            'roles.add' => 'array',
            'roles.delete' => 'array',
            'roles.add.' => Rule::in(UserDto::rolesByFrontIds([Front::FRONT_MAS])),
            'roles.delete.' => Rule::in(UserDto::rolesByFrontIds([Front::FRONT_MAS])),
        ]);

        $operatorData = $request->validate([
            'merchant_id' => 'integer',
            'position' => 'string',
            'communication_method' => Rule::in(array_keys(OperatorCommunicationMethod::allMethods())),
        ]);

        if ($userData || $userRolesData) {
            /** @var OperatorDto $operator */
            $operator = $operatorService->operators(
                (new RestQuery())->setFilter('id', $operatorId)
            )->first();

            if ($userData) {
                $userData['id'] = $operator->user_id;
                $userService->update(new UserDto($userData));
            }
            if ($userRolesData && $userRolesData['roles']['add']) {
                $userService->addRoles($operator->user_id, $userRolesData['roles']['add']);
            }
            if ($userRolesData && $userRolesData['roles']['delete']) {
                $userService->deleteRoles($operator->user_id, $userRolesData['roles']['delete']);
            }
        }

        $operatorData['id'] = $operatorId;
        $operatorService->update($operatorData['id'], new OperatorDto($operatorData));

        return response('', 204);
    }

    public function delete(Request $request, OperatorService $operatorService, UserService $userService)
    {
        $data = $request->validate([
            'operator_ids' => 'required|array',
        ]);

        $userIds = $operatorService->operators((new RestQuery())->setFilter('id', $data['operator_ids']))
            ->pluck('user_id')
            ->all();

        $userService->deleteArray($userIds);
        $operatorService->deleteArray($data['operator_ids']);

        return response('', 204);
    }

    public function changeRoles(Request $request, UserService $userService)
    {
        $userRolesData = $request->validate([
            'user_id' => 'integer',
            'roles' => 'array',
            'roles.add' => 'array',
            'roles.delete' => 'array',
            'roles.add.' => Rule::in(UserDto::rolesByFrontIds([Front::FRONT_MAS])),
            'roles.delete.' => Rule::in(UserDto::rolesByFrontIds([Front::FRONT_MAS])),
        ]);

        if ($userRolesData['roles'] && $userRolesData['roles']['add']) {
            $userService->addRoles($userRolesData['user_id'], $userRolesData['roles']['add']);
        }
        if ($userRolesData['roles'] && $userRolesData['roles']['delete']) {
            $userService->deleteRoles($userRolesData['user_id'], $userRolesData['roles']['delete']);
        }

        return response('', 204);
    }
}
