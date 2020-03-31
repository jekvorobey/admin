<?php

namespace App\Http\Controllers\Customers;


use App\Http\Controllers\Controller;use Greensight\CommonMsa\Dto\Front;use Greensight\CommonMsa\Dto\UserDto;use Greensight\CommonMsa\Rest\RestQuery;use Greensight\CommonMsa\Services\AuthService\UserService;use Greensight\Customer\Dto\CustomerDto;use Greensight\Customer\Services\CustomerService\CustomerService;use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CustomerListController extends Controller
{
    const PER_PAGE = 10;
    public function listProfessional()
    {
        return $this->list('Клиентская база', false);
    }

    public function listReferralPartner()
    {
        return $this->list('Список реферальных партнеров', true);
    }

    protected function list($title, $isReferral)
    {
        $this->title = $title;
        return $this->render('Customer/List', [
            'statuses' => CustomerDto::statusesName(),
            'isReferral' => $isReferral,
            'perPage' => static::PER_PAGE
        ]);
    }

    public function filter(UserService $userService, CustomerService $customerService)
    {
        $filter = request()->validate([
            'status' => 'nullable',
            'phone' => 'nullable',
            'last_name' => 'nullable',
            'first_name' => 'nullable',
            'middle_name' => 'nullable',
            'isReferral' => 'required|boolean',
            'page' => 'nullable'
        ]);

        $restQueryCustomer = new RestQuery();
        if (isset($filter['status']) && $filter['status']) {
            $restQueryCustomer->setFilter('status', $filter['status']);
        }
        $customers = $customerService->customers($restQueryCustomer);
        if (!$customers) {
            return response()->json([
                'users' => []
            ]);
        }

        $restQueryUser = new RestQuery();
        $restQueryUser->setFilter('id', $customers->pluck('user_id')->all());
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
        $restQueryUser->setFilter('role', request('isReferral') ? UserDto::SHOWCASE__REFERRAL_PARTNER : UserDto::SHOWCASE__PROFESSIONAL);
        $users = $userService->users($restQueryUser)->keyBy('id');

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
                'status' => $customer->status
            ];
        })->filter()->sortByDesc('id')->values();

        return response()->json([
            'users' => $result->forPage(request('page', 1), static::PER_PAGE),
            'count' => $result->count()
        ]);
    }

    public function create(UserService $userService, CustomerService $customerService)
    {
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
        $user->front = Front::FRONT_SHOWCASE;


        $id = $userService->create($user);
        if ($id) {
            $userService->addRoles($id, [UserDto::SHOWCASE__PROFESSIONAL]);

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