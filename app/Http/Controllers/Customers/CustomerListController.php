<?php

namespace App\Http\Controllers\Customers;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;

class CustomerListController extends Controller
{
    const PER_PAGE = 10;
    public function list()
    {
        $this->title = 'Клиентская база';
        return $this->render('Customer/List', [
            'statuses' => CustomerDto::statuses(),
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
        })->filter();

        return response()->json([
            'users' => $result->forPage(request('page', 1), static::PER_PAGE),
            'count' => $result->count()
        ]);
    }
}