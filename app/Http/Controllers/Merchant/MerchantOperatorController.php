<?php

namespace App\Http\Controllers\Merchant;

use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
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
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

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
        $props['roles'] = Helpers::getOptionRoles(false, Front::FRONT_MAS);

        return $this->render('Merchant/Operator/CreateEdit', $props);
    }

    public function indexEdit(
        int $operatorId,
        OperatorService $operatorService,
        UserService $userService,
        MerchantService $merchantService
    ) {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        /** @var OperatorDto $operator */
        $operator = $operatorService->operators((new RestQuery())->setFilter('id', $operatorId))
            ->first();

        /** @var UserDto $user */
        $user = $userService->users((new RestQuery())->setFilter('id', $operator->user_id))
            ->first();

        $operator = [
            'id' => $operator->id,
            'user_id' => $operator->user_id,
            'merchant_id' => $operator->merchant_id,
            'last_name' => $user->last_name,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'login' => $user->login,
            'position' => $operator->position,
            'communication_method' => $operator->communication_method,
            'roles' => $user->roles,
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
            'roles' => Helpers::getOptionRoles(false, Front::FRONT_MAS),
        ]);
    }

    public function save(Request $request, UserService $userService, OperatorService $operatorService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $userData = $request->validate([
            'last_name' => 'string|required',
            'first_name' => 'string|required',
            'middle_name' => 'string|nullable',
            'email' => 'email|required',
            'login' => 'string|required',
            'login_email' => 'email|required',
            'phone' => 'string|required',
            'password' => 'string|nullable',
            'roles' => 'array|required',
            'roles.' => Rule::in(array_keys(Helpers::getRoles(Front::FRONT_MAS))),
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
        UserService $userService
    ) {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

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
            'roles' => 'required|array',
            'roles.*' => ['integer', Rule::in(array_keys(Helpers::getRoles(Front::FRONT_MAS)))],
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
            if ($userRolesData) {
                $userService->addRoles($operator->user_id, $userRolesData['roles']);
            }
        }

        $operatorData['id'] = $operatorId;
        $operatorService->update($operatorData['id'], new OperatorDto($operatorData));

        return response('', 204);
    }

    public function delete(Request $request, OperatorService $operatorService, UserService $userService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $request->validate([
            'operator_ids' => 'required|array',
        ]);

        $users = $operatorService->operators((new RestQuery())->setFilter('id', $data['operator_ids']));

        $userForDeleteIds = [];
        /** @var UserDto $user */
        foreach ($users as $user) {
            if (array_diff($user->fronts, [Front::FRONT_MAS])) {
                unset($user->fronts[Front::FRONT_MAS]);
                $userData['fronts'] = $user->fronts;
                $userService->update(new UserDto($userData));
            } else {
                $userForDeleteIds[] = $user->id;
            }
        }
        if ($userForDeleteIds) {
            $userService->deleteArray($userForDeleteIds);
        }
        $operatorService->deleteArray($data['operator_ids']);

        return response('', 204);
    }

    /** TODO изменить на выбор ролей из базы */
    public function changeRoles(Request $request, UserService $userService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $userRolesData = $request->validate([
            'user_id' => 'integer',
            'roles' => 'required|array',
            'roles.*' => ['integer', Rule::in(array_keys(Helpers::getRoles(Front::FRONT_MAS)))],
        ]);

        $userService->addRoles($userRolesData['user_id'], $userRolesData['roles']);

        return response('', 204);
    }
}
