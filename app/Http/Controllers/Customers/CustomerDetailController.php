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
        $portfolios = $customerService->portfolios($customer->user_id);

        $socials = $userService->socials($customer->user_id);

        $this->title = $user->full_name;
        $referral = $user->hasRole(UserDto::SHOWCASE__REFERRAL_PARTNER);
        return $this->render('Customer/Detail', [
            'customer' => [
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
            'managers' => [],
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
            'kpis' => [
                $this->kpi('Количество заказов', 0),
                $this->kpi('Сумма заказов накопительным итогом', 0),
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