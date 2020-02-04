<?php

namespace App\Http\Controllers\Customers;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerDetailController extends Controller
{
    public function detail($id, CustomerService $customerService, UserService $userService)
    {
        /** @var CustomerDto $customer */
        $customer = $customerService->customers((new RestQuery())->setFilter('id', $id))->first();
        if (!$customer) {
            throw new NotFoundHttpException();
        }

        /** @var UserDto $user */
        $user = $userService->users((new RestQuery())->setFilter('id', $customer->user_id))->first();
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $this->title = $user->full_name;
        return $this->render('Customer/Detail', [
            'customer' => [
                'id' => $customer->id,
                'user_id' => $customer->user_id,
                'status' => $customer->status,
            ],
            'statuses' => CustomerDto::statuses(),
        ]);
    }

    public function save($id, CustomerService $customerService)
    {
        $customer = new CustomerDto();
        $customer->status = request('status');
        $customerService->updateCustomer($id, $customer);
        return response('', 204);
    }

    public function infoMain($id)
    {
        return response()->json([
            'kpis' => [
                $this->kpi('Количество заказов', 0)
            ]
        ]);
    }

    public function infoSubscribe($id)
    {
        return response()->json([
            'kpis' => []
        ]);
    }

    public function infoPreference($id)
    {
        return response()->json([
            'kpis' => []
        ]);
    }

    public function infoOrder($id)
    {
        return response()->json([
            'kpis' => []
        ]);
    }

    public function infoLog($id)
    {
        return response()->json([
            'kpis' => []
        ]);
    }

    protected function kpi($title, $value)
    {
        return [
            'title' => $title,
            'value' => $value,
        ];
    }
}