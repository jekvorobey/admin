<?php

namespace App\Http\Controllers\Customers;

use App\Core\Helpers;
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
use Greensight\Customer\Dto\CustomerPassportDto;
use Greensight\Customer\Dto\CustomerPortfolioDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Greensight\Marketing\Dto\PromoCode\PromoCodeOutDto;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Validation\Rule;
use Pim\Services\CertificateService\CertificateService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;

class CustomerDetailController extends Controller
{
    /**
     * @param $id
     * @param CustomerService $customerService
     * @param UserService $userService
     * @param OrderService $orderService
     * @param FileService $fileService
     * @param ReferralService $referralService
     * @param CommunicationService $communicationService
     * @return mixed
     */
    public function detail(
        $id,
        CustomerService $customerService,
        UserService $userService,
        OrderService $orderService,
        FileService $fileService,
        ReferralService $referralService,
        CommunicationService $communicationService
    ) {
        $this->loadUserRoles = true;
        $this->loadCustomerStatus = true;
        $this->loadCommunicationChannelTypes = true;
        $this->loadCommunicationChannels = true;
        $this->loadCommunicationThemes = true;
        $this->loadCommunicationStatuses = true;
        $this->loadCommunicationTypes = true;
        $this->loadPromoCodeTypes = true;
        $this->loadPromoCodeStatus = true;
        $this->loadCustomerBonusStatus = true;
        $this->loadOrderStatuses = true;

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

        $passport = $customerService->passport($customer->id);

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

        // Счетчик непрочитанных сообщений от пользователя //
        $unreadMsgCount = $communicationService->unreadCount(
            [$user->id],
            true
        );

        /** @var UserDto $referrer_user */
        $referrer_user = $referrer ? $users->get($referrer->user_id) : null;

        $referralLevels = $referralService->getLevels();
        $commission_route = '';
        if ($referral) {
            $existCustomerCommission = $referralService->existCustomerCommission($customer->id);
            if ($existCustomerCommission) {
                $commission_route = route('referral.levels', ['level_id' => $existCustomerCommission[0]]);
            }
        }

        $availableCertificateAmount = resolve(CertificateService::class)->spendPossibility($customer->id)->amount;

        return $this->render('Customer/Detail', [
            'iCustomer' => [
                'id' => $customer->id,
                'avatar' => $avatar ? $avatar->id : null,
                'user_id' => $customer->user_id,
                'referral_level_id' => $customer->referral_level_id,
                'status' => $customer->status,
                'comment_status' => $customer->comment_status,
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $customer->gender,
                'city' => $customer->city,
                'legal_info_company_name' => $customer->legal_info_company_name,
                'legal_info_company_address' => $customer->legal_info_company_address,
                'legal_info_inn' => $customer->legal_info_inn,
                'legal_info_payment_account' => $customer->legal_info_payment_account,
                'legal_info_bik' => $customer->legal_info_bik,
                'legal_info_bank' => $customer->legal_info_bank,
                'legal_info_bank_city' => $customer->legal_info_bank_city,
                'legal_info_bank_correspondent_account' => $customer->legal_info_bank_correspondent_account,
                'referral_code' => $customer->referral_code,
                'referral_bill' => $customer->referral_bill,
                'promo_page_name' => $customer->promo_page_name,
                'bonus' => Helpers::getPriceFormat($customer->bonus, 0),
                'birthday' => $birthday ? $birthday->format('Y-m-d') : null,
                'created_at' => $user->created_at,
                'passport' => $passport,
                'portfolios' => $portfolios->map(function (CustomerPortfolioDto $portfolio) {
                    return [
                        'link' => $portfolio->link,
                        'name' => $portfolio->name,
                        'duplicated_customer_id' => $portfolio->duplicated_customer_id,
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
                'role_date' => $user->roles[$referral ? UserDto::SHOWCASE__REFERRAL_PARTNER : UserDto::SHOWCASE__PROFESSIONAL]['created_at'] ?? null,
                'comment_internal' => $customer->comment_internal,
                'manager_id' => $customer->manager_id,
                'commission_route' => $commission_route,
            ],
            'order' => [
                'count' => $orders->count(),
                'price' => Helpers::getPriceFormat($orders->sum('price')),
                'availableCertificateAmount' => $availableCertificateAmount,
            ],
            'referralLevels' => $referralLevels,
            'options' => [
                'promoCodeTypes' => PromoCodeOutDto::allTypes(),
                'promoCodeStatuses' => PromoCodeOutDto::allStatuses(),
            ],
            'unreadMsgCount' => $unreadMsgCount,
        ]);
    }

    public function save(
        $id,
        CustomerService $customerService,
        UserService $userService,
        RequestInitiator $requestInitiator
    ) {
        $this->validate(request(), [
            'customer' => 'nullable|array',
            'customer.avatar' => 'nullable',
            'customer.comment_internal' => 'nullable',
            'customer.manager_id' => 'nullable',
            'customer.gender' => ['nullable', Rule::in([CustomerDto::GENDER_MALE, CustomerDto::GENDER_FEMALE])],
            'customer.city' => 'nullable',
            'customer.birthday' => 'nullable|date_format:Y-m-d',
            'customer.status' => ['nullable', Rule::in(array_keys(CustomerDto::statusesName()))],
            'customer.comment_status' => 'nullable',
            'customer.referral_level_id' => 'nullable',
            'customer.legal_info_company_name' => 'nullable',
            'customer.legal_info_company_address' => 'nullable',
            'customer.legal_info_inn' => 'nullable',
            'customer.legal_info_payment_account' => 'nullable',
            'customer.legal_info_bik' => 'nullable',
            'customer.legal_info_bank' => 'nullable',
            'customer.legal_info_bank_correspondent_account' => 'nullable',
            'customer.referral_code' => 'nullable',
            'customer.promo_page_name' => 'nullable',

            'activities' => 'nullable|array',
            'activities.*' => 'numeric',

            'passport' => 'nullable|array',

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
        $passport = request('customer.passport');

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
                    throw new BadRequestHttpException(
                        "Невозможно удалить телефон. Он используется в качестве логина"
                    );
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
        if ($passport) {
            $customerService->savePassport($id, new CustomerPassportDto($passport));
        }
        return response('', 204);
    }

    public function referral($id, ReferralService $referralService)
    {
        $referralService->makeReferral($id);

        $referralLevels = $referralService->getLevels();
        $defaultLevel = $referralLevels->sortBy('sort')->first()->id;

        return response()->json([
            'defaultLevel' => $defaultLevel,
        ]);
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

    public function dial(
        int $id,
        Request $request,
        CustomerService $customerService,
        RequestInitiator $requestInitiator
    ) {
        $customerService->dial($id, $requestInitiator->userId(), $request->input('provider'));

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
