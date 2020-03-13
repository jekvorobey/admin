<?php

namespace App\Http\Controllers\Customers;


use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Dto\SocialUserLinkDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
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
use Illuminate\Validation\Rule;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerDetailController extends Controller
{
    public function detail($id, CustomerService $customerService, UserService $userService, OrderService $orderService, FileService $fileService)
    {
        $this->loadChannelTypes = true;

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
        $portfolios = $customerService->portfolios($customer->id);

        $socials = $userService->socials($customer->user_id);

        $orders = $orderService->orders((new RestQuery())->setFilter('customer_id', $customer->id)->addFields(OrderDto::entity(), 'price'));

        $this->title = $user->full_name ?: $user->phone ?: "Пользователь: {$customer->id}";
        $referral = $user->hasRole(UserDto::SHOWCASE__REFERRAL_PARTNER);
        $birthday = $customer->birthday ? Carbon::createFromFormat('Y-m-d H:i:s', $customer->birthday) : null;

        $avatar = null;
        if ($customer->avatar) {
            /** @var FileDto $avatar */
            $avatar = $fileService->getFiles([$customer->avatar])->first();
        }

        return $this->render('Customer/Detail', [
            'iCustomer' => [
                'id' => $customer->id,
                'avatar' => $avatar ? $avatar->id : null,
                'user_id' => $customer->user_id,
                'status' => $customer->status,
                'comment_problem_status' => $customer->comment_problem_status,
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $customer->gender,
                'birthday' => $birthday ? $birthday->format('Y-m-d') : null,
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
            'statusProblem' => CustomerDto::STATUS_PROBLEM,
            'order' => [
                'count' => $orders->count(),
                'price' => number_format($orders->sum('price'), 2, '.', ' '),
            ],
        ]);
    }

    public function save($id, CustomerService $customerService, UserService $userService, RequestInitiator $requestInitiator)
    {
        $this->validate(request(), [
            'customer' => 'nullable|array',
            'customer.avatar' => 'nullable',
            'customer.comment_internal' => 'nullable',
            'customer.manager_id' => 'nullable',
            'customer.gender' => ['nullable', Rule::in([CustomerDto::GENDER_MALE, CustomerDto::GENDER_FEMALE])],
            'customer.birthday' => 'nullable|date_format:Y-m-d',
            'customer.status' => ['nullable', Rule::in(array_keys(CustomerDto::statuses()))],
            'customer.comment_problem_status' => 'nullable',

            'activities' => 'nullable|array',
            'activities.*' => 'numeric',

            'user' => 'nullable|array',
            'user.id' => 'numeric',
            'user.last_name' => 'nullable',
            'user.first_name' => 'nullable',
            'user.middle_name' => 'nullable',
            'user.email' => 'nullable|email',
            'user.phone' => 'nullable|regex:/^\+7\d{10}$/',
        ]);

        $customer = request('customer');
        $user = request('user');
        $activities = request('activities');

        // Если пользователь не суперадмин, то запрещаем изменять телефон и почту
        if ($user && !$requestInitiator->hasRole(UserDto::ADMIN__SUPER)) {
            unset($user['phone']);
            unset($user['email']);
        }

        $userDto = null;
        if ($user && (array_key_exists('phone', $user) || (array_key_exists('email', $user) && $user['email']))) {
            /** @var UserDto $userDto */
            $userDto = $userService->users((new RestQuery())->setFilter('id', $user['id']))->first();
        }

        if ($user && array_key_exists('phone', $user)) {
            // Если пользователю меняют телефон на новый, ото проверяем что такой телефон ещё не зареган
            if ($user['phone'] && $user['phone'] != $userDto->phone) {
                $count = $userService->count((new RestQuery())->setFilter('phone', $user['phone']));
                if ($count['total'] > 0) {
                    throw new BadRequestHttpException("Пользователь с таким телефоном уже существует");
                }
            }

            // Если пользователь использует телефон для авторизации, то меняем пользователю логин
            if ($userDto->hasPassword()) {
                if (!$user['phone']) {
                    throw new BadRequestHttpException("Невозможно удалить телефон. Он используется в качестве логина");
                } else {
                    $user['login'] = $user['phone'];
                }
            }
        }

        if ($user && array_key_exists('email', $user) && $user['email']) {
            if ($user['email'] != $userDto->email) {
                $count = $userService->count((new RestQuery())->setFilter('email', $user['email']));
                if ($count['total'] > 0) {
                    throw new BadRequestHttpException("Пользователь с такой почтой уже существует");
                }
            }
        }

        if ($customer) {
            $customerService->updateCustomer($id, new CustomerDto($customer));
        }
        if ($user) {
            $userService->update(new UserDto($user));
        }
        if ($activities) {
            $customerService->putActivities($id, $activities);
        }
        return response('', 204);
    }

    public function createCertificate(int $id, int $file_id, CustomerService $customerService)
    {
        $certificateDto = new CustomerCertificateDto();
        $certificateDto->file_id = $file_id;
        $id = $customerService->createCertificate($id, $certificateDto);

        return response()->json([
            'id' => $id,
        ]);
    }

    public function deleteCertificate(int $id, int $certificate_id, CustomerService $customerService)
    {
        $customerService->deleteCertificate($id, $certificate_id);

        return response('', 204);
    }

    public function putPortfolios(int $id, CustomerService $customerService)
    {
        $portfolios = request('portfolios');
        $portfolioDtos = collect();
        foreach ($portfolios as $portfolio) {
            $portfolioDto = new CustomerPortfolioDto();
            $portfolioDto->name = $portfolio['name'];
            $portfolioDto->link = $portfolio['link'];
            $portfolioDtos->push($portfolioDto);
        }
        $customerService->updatePortfolio($id, $portfolioDtos);

        return response('', 204);
    }

    public function putBrands(int $id, CustomerService $customerService)
    {
        $this->validate(request(), [
            'brands' => 'array',
            'brands.*' => 'numeric',
        ]);

        $customerService->updateBrands($id, request('brands'));

        return response('', 204);
    }

    public function putCategories(int $id, CustomerService $customerService)
    {
        $this->validate(request(), [
            'categories' => 'array',
            'categories.*' => 'numeric',
        ]);

        $customerService->updateCategories($id, request('categories'));

        return response('', 204);
    }

    public function infoMain(int $id, CustomerService $customerService, FileService $fileService, UserService $userService)
    {
        $certificates = $customerService->certificates($id);
        $files = [];
        if ($certificates) {
            $files = $fileService->getFiles($certificates->pluck('file_id')->all())->keyBy('id');
        }

        $managers = $userService->users((new RestQuery())->setFilter('role', UserDto::ADMIN__MANAGER_CLIENT));

        $activities = $customerService->activities()->setCustomerIds([$id])->load();
        $activitiesAll = $customerService->activities()->load();

        return response()->json([
            'certificates' => $certificates->map(function (CustomerCertificateDto $certificate) use ($files) {
                /** @var FileDto $file */
                $file = $files->get($certificate->file_id);
                if (!$file) {
                    return false;
                }
                return [
                    'id' => $certificate->id,
                    'url' => $file->absoluteUrl(),
                    'name' => $file->original_name,
                ];
            })->filter(),
            'managers' => $managers->mapWithKeys(function (UserDto $user) {
                return [$user->id => $user->full_name];
            }),
            'activities' => $activities->pluck('id'),
            'activitiesAll' => $activitiesAll,
        ]);
    }

    public function infoSubscribe($id)
    {
        return response()->json([
        ]);
    }

    public function infoPreference(
        $id,
        BrandService $brandService,
        CategoryService $categoryService,
        CustomerService $customerService
    )
    {
        $brands = $brandService->brands((new RestQuery())->addFields(BrandDto::entity(), 'id', 'name'));
        $categories = $categoryService->categories((new RestQuery())->addFields(CategoryDto::entity(), 'id', 'name', '_lft', '_rgt', 'parent_id'));
        /** @var CustomerDto $customer */
        $customer = $customerService->customers((new RestQuery())->setFilter('id', $id))->first();

        return response()->json([
            'brands' => $brands->keyBy('id'),
            'categories' => $categories->keyBy('id'),
            'customer' => [
                'brands' => $customer->brands,
                'categories' => $customer->categories,
            ],
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