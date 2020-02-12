<?php

namespace App\Http\Controllers\Customers;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Dto\SocialUserLinkDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\Customer\Dto\CustomerCertificateDto;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Dto\CustomerPortfolioDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\Payment\PaymentDto;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\PaymentService\PaymentService;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerDetailController extends Controller
{
    public function detail($id, CustomerService $customerService, UserService $userService, OrderService $orderService)
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
        $portfolios = $customerService->portfolios($customer->user_id);

        $socials = $userService->socials($customer->user_id);

        $orders = $orderService->orders((new RestQuery())->setFilter('customer_id', $customer->id)->addFields(OrderDto::entity(), 'price'));

        $this->title = $user->full_name;
        $referral = $user->hasRole(UserDto::SHOWCASE__REFERRAL_PARTNER);
        return $this->render('Customer/Detail', [
            'iCustomer' => [
                'id' => $customer->id,
                'user_id' => $customer->user_id,
                'status' => $customer->status,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at,
                'portfolios' => $portfolios->map(function (CustomerPortfolioDto $portfolio) {
                    return [
                        'link' => $portfolio->link,
                        'name' => $portfolio->name,
                    ];
                }),
                'socials' => $socials->map(function (SocialUserLinkDto $socials) {
                    return [
                        'name' => $socials->socialTitle,
                        'driver' => $socials->getDriverTitle(),
                    ];
                }),
                'referral' => $referral,
                'role_date' => $user->roles[$referral ? UserDto::SHOWCASE__REFERRAL_PARTNER : UserDto::SHOWCASE__PROFESSIONAL]['created_at'],
                'comment_internal' => $customer->comment_internal,
                'manager_id' => $customer->manager_id,
            ],
            'statuses' => CustomerDto::statuses(),
            'order' => [
                'count' => $orders->count(),
                'price' => number_format($orders->sum('price'), 2, '.', ' '),
            ],
        ]);
    }

    public function save($id, CustomerService $customerService)
    {
        $customer = request('customer');
        if ($customer) {
            $customer = new CustomerDto($customer);
            $customerService->updateCustomer($id, $customer);
        }
        return response('', 204);
    }

    public function infoMain($id, CustomerService $customerService, FileService $fileService, UserService $userService)
    {
        $certificates = $customerService->certificates($id);
        $files = [];
        if ($certificates) {
            $files = $fileService->getFiles($certificates->pluck('file_id')->all())->keyBy('id');
        }

        $managers = $userService->users((new RestQuery())->setFilter('role', UserDto::ADMIN__MANAGER_CLIENT));

        return response()->json([
            'certificates' => $certificates->map(function (CustomerCertificateDto $certificate) use ($files) {
                /** @var FileDto $file */
                $file = $files->get($certificate->file_id);
                if (!$file) {
                    return false;
                }
                return [
                    'url' => $file->absoluteUrl(),
                    'name' => $file->original_name,
                ];
            })->filter(),
            'managers' => $managers->mapWithKeys(function (UserDto $user) {
                return [$user->id => $user->full_name];
            }),
        ]);
    }

    public function infoSubscribe($id)
    {
        return response()->json([
        ]);
    }

    public function infoPreference($id)
    {
        return response()->json([
        ]);
    }

    public function infoOrder($id, OrderService $orderService, PaymentService $paymentService, DeliveryService $deliveryService)
    {
        $orders = $orderService->orders((new RestQuery())->setFilter('customer_id', $id));
        if ($orders) {
            $orderIds = $orders->pluck('id')->all();
            $payments = $paymentService->payments($orderIds)->groupBy('order_id');

            $deliveries = $deliveryService
                ->deliveries((new RestQuery())->setFilter('order_id', $orderIds))
                ->groupBy('order_id');

            $orders = $orders->map(function (OrderDto $order) use ($payments, $deliveries) {
                /** @var Collection|PaymentDto[] $ps */
                $ps = $payments->get($order->id, collect());
                /** @var Collection|DeliveryDto[] $ds */
                $ds = $deliveries->get($order->id, collect());
                $ar = $order->toArray();
                $ar['status'] = $order->status();
                $ar['isPayed'] = $order->isPayed();
                $ar['deliveryType'] = $order->deliveryType();
                $ar['paymentMethod'] = $ps->map(function (PaymentDto $payment) {
                    return $payment->paymentMethod()->name;
                })->unique()->join(', ');
                $ar['deliveryMethod'] = $ds->map(function (DeliveryDto $delivery) {
                    return DeliveryMethod::methodById($delivery->delivery_method)->name;
                })->unique()->join(', ');
                $ar['deliverySystems'] = $ds->map(function (DeliveryDto $delivery) {
                    return \Greensight\Logistics\Dto\Lists\DeliveryService::serviceById($delivery->delivery_service)->name;
                })->unique()->join(', ');
                $ar['deliveryCount'] = $ds->count();
                $ar['deliveryDate'] = $ds->map(function (DeliveryDto $delivery) {
                    return explode(' ', $delivery->delivery_at)[0];
                })->unique()->join(', ');
                return $ar;
            });
        }
        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function infoLog($id)
    {
        return response()->json([
        ]);
    }
}