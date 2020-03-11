<?php

namespace App\Http\Controllers;

use App\Core\Menu;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use Greensight\Customer\Dto\CustomerDto;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Controller extends BaseController
{
    protected $title = '';
    protected $loadUserRoles = false;
    protected $loadCustomerStatus = false;

    public function render($componentName, $props = [])
    {
        $userRoles = [];
        if ($this->loadUserRoles) {
            $userRoles = [
                'admin' => [
                    'super' => UserDto::ADMIN__SUPER,
                    'admin' => UserDto::ADMIN__ADMIN,
                    'manager_merchant' => UserDto::ADMIN__MANAGER_MERCHANT,
                    'manager_client' => UserDto::ADMIN__MANAGER_CLIENT,
                ],
                'mas' => [
                    'merchant_operator' => UserDto::MAS__MERCHANT_OPERATOR,
                    'merchant_admin' => UserDto::MAS__MERCHANT_ADMIN,
                ],
                'i_commerce_ml' => [
                    'external_system' => UserDto::I_COMMERCE_ML__EXTERNAL_SYSTEM,
                ],
                'showcase' => [
                    'professional' => UserDto::SHOWCASE__PROFESSIONAL,
                    'referral_partner' => UserDto::SHOWCASE__REFERRAL_PARTNER,
                ],
            ];
        }

        $customerStatus = [];
        $customerStatusName = [];
        $customerStatusByRole = [];
        if ($this->loadCustomerStatus) {
            $customerStatus = [
                'created' => CustomerDto::STATUS_CREATED,
                'new' => CustomerDto::STATUS_NEW,
                'consideration' => CustomerDto::STATUS_CONSIDERATION,
                'rejected' => CustomerDto::STATUS_REJECTED,
                'active' => CustomerDto::STATUS_ACTIVE,
                'problem' => CustomerDto::STATUS_PROBLEM,
                'block' => CustomerDto::STATUS_BLOCK,
                'potential_rp' => CustomerDto::STATUS_POTENTIAL_RP,
                'temporarily_suspended' => CustomerDto::STATUS_TEMPORARILY_SUSPENDED,
            ];
            $customerStatusName = CustomerDto::statusesName();
            $customerStatusByRole = CustomerDto::statusesByRole();
        }
        return View::component(
            $componentName,
            $props,
            [
                'menu' => Menu::getMenuItems(),
                'user' => [
                    'isGuest' => resolve(TokenStore::class)->token() == null,
                    'isSuper' => resolve(RequestInitiator::class)->hasRole(UserDto::ADMIN__SUPER),
                ],

                'userRoles' => $userRoles,

                'customerStatusByRole' => $customerStatusByRole,
                'customerStatusName' => $customerStatusName,
                'customerStatus' => $customerStatus,
            ],
            [
                'title' => $this->title,
                'assets' => $this->getAssets($componentName),
            ]
        );
    }

    private function getAssets($componentName)
    {
        if (frontend()->isInDevMode()) {
            return [
                'js' => [
                    "scripts/{$componentName}.js",
                ],
                'css' => [],
            ];
        } else {
            $webPack = json_decode(file_get_contents(public_path('/assets/webpack-assets.json')), true);
            $js = [];
            $css = [];

            if (isset($webPack[$componentName])) {
                if (isset($webPack[$componentName]['js'])) {
                    $js = array_filter((array)$webPack[$componentName]['js']);
                }
                if (isset($webPack[$componentName]['css'])) {
                    $css = array_filter((array)$webPack[$componentName]['css']);
                }
            }
            return [
                'js' => $js,
                'css' => $css,
            ];
        }
    }
    
    protected function validate(Request $request, array $rules, array $customAttributes = []): array
    {
        $data = $request->all();
        $validator = Validator::make($data, $rules, [], $customAttributes);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }
        return $data;
    }
    
    /**
     * @param  array  $merchantIds
     * @return Collection|MerchantDto[]
     */
    protected function getMerchants(array $merchantIds): Collection
    {
        $merchants = collect();
        
        if ($merchantIds) {
            /** @var MerchantService $merchantService */
            $merchantService = resolve(MerchantService::class);
            $merchantQuery = $merchantService->newQuery()
                ->setFilter('id', $merchantIds)
                ->addFields(MerchantDto::entity(), 'id', 'display_name');
            $merchants = $merchantService->merchants($merchantQuery)->keyBy('id');
        }
        
        return $merchants;
    }
}
