<?php

namespace App\Http\Controllers\Customers;

use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\RoleDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CustomerListController extends Controller
{
    public const PER_PAGE = 10;

    /**
     * Отображаем всех пользователей
     * @return mixed
     */
    public function listProfessional()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        return $this->list('Клиентская база');
    }

    /**
     * Отображаем только РП
     * @return mixed
     */
    public function listReferralPartner()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        return $this->list('Список реферальных партнеров', true);
    }

    protected function list($title, $isReferral = null)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $this->title = $title;

        return $this->render('Customer/List', [
            'statuses' => CustomerDto::statusesName(),
            'isReferral' => $isReferral,
            'perPage' => self::PER_PAGE,
            'roles' => $isReferral === null ? Helpers::getOptionRoles(true) : null,
        ]);
    }

    public function filter(UserService $userService, CustomerService $customerService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $filter = request()->validate([
            'status' => 'nullable',
            'phone' => 'nullable',
            'gender' => 'nullable',
            'last_name' => 'nullable',
            'first_name' => 'nullable',
            'middle_name' => 'nullable',
            'full_name' => 'nullable',
            'created_at' => 'nullable',
            'created_between' => 'nullable',
            'isReferral' => 'required|boolean',
            'page' => 'nullable',
            'role' => 'nullable',
        ]);

        $restQueryUser = new RestQuery();

        if (isset($filter['phone']) && $filter['phone']) {
            $restQueryUser->setFilter('phone', phone_format($filter['phone']));
        }
        if (isset($filter['last_name']) && $filter['last_name']) {
            $restQueryUser->setFilter('last_name', $filter['last_name']);
        }
        if (isset($filter['first_name']) && $filter['first_name']) {
            $restQueryUser->setFilter('first_name', $filter['first_name']);
        }
        if (isset($filter['middle_name']) && $filter['middle_name']) {
            $restQueryUser->setFilter('middle_name', $filter['middle_name']);
        }
        if (!empty($filter['full_name'])) {
            $restQueryUser->setFilter('full_name', $filter['full_name']);
        }
        if (!empty($filter['created_at'])) {
            $restQueryUser->setFilter('created_at', 'like', "{$filter['created_at']}%");
        }
        if (!empty($filter['created_between'])) {
            $restQueryUser->setFilter('created_at', '>=', $filter['created_between'][0]);
            $restQueryUser->setFilter('created_at', '<=', $filter['created_between'][1]);
        }

        if (!empty($filter['role'])) {
            $restQueryUser->setFilter('role', $filter['role']);
        } elseif (isset($filter['isReferral']) && $filter['isReferral']) {
            $restQueryUser->setFilter('role', RoleDto::ROLE_SHOWCASE_REFERRAL_PARTNER);
        }

        if (isset($filter['status']) && $filter['status']) {
            $restQueryUser->setFilter('status', $filter['status']);
        }
        if (!empty($filter['gender'])) {
            $restQueryUser->setFilter('gender', '=', $filter['gender']);
        }

        $restQueryUser->setFilter('front', Front::FRONT_SHOWCASE)
            ->addSort('id', 'desc')
            ->pageNumber(request('page', 1), self::PER_PAGE);
        $users = $userService->users($restQueryUser)->keyBy('id');
        if ($users->isEmpty()) {
            return response()->json([
                'users' => [],
            ]);
        }

        $usersCount = $userService->count($restQueryUser);

        $restQueryCustomer = $customerService->newQuery()
            ->addFields(CustomerDto::entity(), 'id', 'user_id', 'status', 'created_at', 'gender')
            ->setFilter('user_id', $users->pluck('id')->toArray())
            ->addSort('id', 'desc');

        $customers = $customerService->customers($restQueryCustomer);

        $result = $customers->map(function (CustomerDto $customer) use ($users) {
            /** @var UserDto $user */
            $user = $users->get($customer->user_id);

            if (!$user) {
                return false;
            }

            return [
                'id' => $customer->id,
                'full_name' => $user->full_name,
                'phone' => $user->phone,
                'status' => $customer->status,
                'register_date' => $customer->created_at,
                'email' => $user->email,
                'segment' => '', //TODO
                'last_visit' => '', //TODO
                'gender' => $customer->gender,
            ];
        })->filter()->values();

        return response()->json([
            'users' => $result,
            'count' => $usersCount['total'],
        ]);
    }

    public function create(UserService $userService, CustomerService $customerService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $data = $this->validate(request(), [
            'phone' => 'required|regex:/^\+7\d{10}$/',
            'password' => 'required|confirmed',
        ]);
        $customerId = null;

        $exists = $userService->exists($data['phone'], Front::FRONT_SHOWCASE);
        if ($exists) {
            throw new BadRequestHttpException('Пользователь с таким номером телефона уже сущетсвует');
        }

        $user = new UserDto();
        $user->login = $data['phone'];
        $user->phone = $data['phone'];
        $user->password = $data['password'];
        $user->fronts = [Front::FRONT_SHOWCASE];

        $id = $userService->create($user);
        if ($id) {
            $userService->addRoles($id, [RoleDto::ROLE_SHOWCASE_PROFESSIONAL]);

            $customerId = $customerService->createCustomer(new CustomerDto(['user_id' => $id]));
        }

        if (!$customerId) {
            throw new BadRequestHttpException('Ошибка создания пользователя');
        }

        return response()->json([
            'redirect' => route('customers.detail', ['id' => $customerId]),
        ]);
    }
}
