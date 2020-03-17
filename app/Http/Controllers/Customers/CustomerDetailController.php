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
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Dto\CustomerPortfolioDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerDetailController extends Controller
{
    public function detail($id, CustomerService $customerService, UserService $userService, OrderService $orderService, FileService $fileService)
    {
        $this->loadUserRoles = true;
        $this->loadCustomerStatus = true;
        $this->loadChannelTypes = true;

        /** @var CustomerDto $customer */
        $customer = $customerService->customers((new RestQuery())->setFilter('id', $id))->first();
        if (!$customer) {
            throw new NotFoundHttpException();
        }

        $user_ids = [$customer->user_id];
        $referrer = null;
        if ($customer->referrer_id) {
            /** @var CustomerDto $referrer */
            $referrer = $customerService->customers((new RestQuery())->setFilter('id', $customer->referrer_id))->first();
            if ($referrer) {
                $user_ids[] = $referrer->user_id;
            }
        }

        $users = $userService->users((new RestQuery())->setFilter('id', $user_ids))->keyBy('id');
        if (!$users->has($customer->user_id)) {
            throw new NotFoundHttpException();
        }
        /** @var UserDto $user */
        $user = $users[$customer->user_id];

        $portfolios = $customerService->portfolios($customer->id);

        $socials = $userService->socials($customer->user_id);

        $orders = $orderService->orders((new RestQuery())->setFilter('customer_id', $customer->id)->addFields(OrderDto::entity(), 'price'));

        $this->title = $user->getTitle();
        $referral = $user->hasRole(UserDto::SHOWCASE__REFERRAL_PARTNER);
        $birthday = $customer->birthday ? Carbon::createFromFormat('Y-m-d H:i:s', $customer->birthday) : null;

        $avatar = null;
        if ($customer->avatar) {
            /** @var FileDto $avatar */
            $avatar = $fileService->getFiles([$customer->avatar])->first();
        }

        /** @var UserDto $referrer_user */
        $referrer_user = $referrer ? $users->get($referrer->user_id) : null;
        return $this->render('Customer/Detail', [
            'iCustomer' => [
                'id' => $customer->id,
                'avatar' => $avatar ? $avatar->id : null,
                'user_id' => $customer->user_id,
                'status' => $customer->status,
                'comment_status' => $customer->comment_status,
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
                'referrer' => $referrer_user ? [
                    'id' => $referrer->id,
                    'title' => $referrer_user->getTitle(),
                ] : null,
                'role_date' => $user->roles[$referral ? UserDto::SHOWCASE__REFERRAL_PARTNER : UserDto::SHOWCASE__PROFESSIONAL]['created_at'],
                'comment_internal' => $customer->comment_internal,
                'manager_id' => $customer->manager_id,
            ],
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
            'customer.status' => ['nullable', Rule::in(array_keys(CustomerDto::statusesName()))],
            'customer.comment_status' => 'nullable',

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

    public function referral($id, ReferralService $referralService)
    {
        $referralService->makeReferral($id);

        return response('', 204);
    }

    public function professional($id, ReferralService $referralService)
    {
        $referralService->makeProfessional($id);

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
}