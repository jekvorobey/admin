<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\MerchantStatus;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Dto\RatingDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
use Pim\Core\PimException;
use Pim\Dto\CategoryDto;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\OfferService\OfferService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MerchantDetailController extends Controller
{
    /**
     * @return mixed
     * @throws PimException
     */
    public function index(
        int $id,
        MerchantService $merchantService,
        OperatorService $operatorService,
        UserService $userService,
        CommunicationService $communicationService,
        BrandService $brandService,
        CategoryService $categoryService,
        OfferService $offerService
    ) {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $this->loadMerchantStatuses = true;
        $this->loadMerchantCommissionTypes = true;
        $this->loadMerchantVatTypes = true;
        $this->loadMerchantDocumentTypes = true;
        $this->loadUserRoles = true;
        $this->loadCustomerStatus = true;
        $this->loadCommunicationChannelTypes = true;
        $this->loadCommunicationChannels = true;
        $this->loadCommunicationThemes = true;
        $this->loadCommunicationStatuses = true;
        $this->loadCommunicationTypes = true;
        $this->loadBillingReportTypes = true;
        $this->loadBillingReportStatuses = true;

        /** @var MerchantDto $merchant */
        $merchant = $merchantService->merchants((new RestQuery())->setFilter('id', $id))->first();
        if (!$merchant) {
            throw new NotFoundHttpException();
        }

        $this->title = $merchant->name;

        $isRequest = in_array($merchant->status, array_keys(MerchantStatus::statusesByMode(true)));

        /** @var OperatorDto $operatorMain */
        $operatorMain = $operatorService->operators(
            (new RestQuery())->setFilter('merchant_id', $merchant->id)->setFilter('is_main', true)
        )->first();
        if (is_null($operatorMain)) {
            /**
             * Если не найден оператор с флагом is_main, то берем первого попавшегося оператора
             */
            $operatorMain = $operatorService->operators(
                (new RestQuery())->setFilter('merchant_id', $merchant->id)
            )->first();
        }
        $userMain = null;
        if ($operatorMain) {
            /** @var UserDto $userMain */
            $userMain = $userService
                ->users((new RestQuery())->setFilter('id', $operatorMain->user_id))
                ->first();
        }

        $userOperatorIds = $operatorService->operators(
            (new RestQuery())->addFields(OperatorDto::class, 'user_id')
                ->setFilter('merchant_id', $merchant->id)
        )->pluck('user_id')->all();

        $operatorUsers = $userService->users(
            (new RestQuery())->addFields(
                UserDto::class,
                'id',
                'first_name',
                'last_name',
                'middle_name',
                'phone',
                'email'
            )->include('roleLinks')
                ->setFilter('id', $userOperatorIds)
        )->keyBy('id');

        // Счетчик непрочитанных сообщений от пользователя //
        $unreadMsgCount = $operatorMain ? $communicationService->unreadCount(
            [$operatorMain->user_id],
            true
        ) : 0;

        $restQuery = (new RestQuery())
            ->include('product')
            ->setFilter('merchant_id', $id);

        $brandIds = $offerService->offers($restQuery)
            ->pluck('product.brand_id', 'product.brand_id')->toArray();
        $categoryIds = $offerService->offers($restQuery)
            ->pluck('product.category_id', 'product.category_id')->toArray();

        $ratings = $merchantService->ratings()->sortByDesc('name');
        $managers = $userService->users((new RestQuery())
            ->setFilter('role', UserDto::ADMIN__MANAGER_MERCHANT));

        $brandList = $brandIds ? $brandService->brands(
            (new RestQuery())
                ->setFilter('id', $brandIds)
                ->addSort('name')
        )->toArray() : [];

        $categories = $categoryIds ? $categoryService->categories((new RestQuery())
            ->include('ancestors')
            ->addFields(CategoryDto::entity(), 'id', 'name', 'code', 'parent_id', 'active'))->toArray() : [];

        $allCategoryList = [];
        foreach ($categories as $category) {
            $categoryNameArr = [];
            if (count($category['ancestors']) > 0) {
                foreach ($category['ancestors'] as $ancestor) {
                    $categoryNameArr[] = $ancestor['name'];
                }
                $categoryName = implode('→', $categoryNameArr) . '→' . $category['name'];
            } else {
                $categoryName = $category['name'];
            }
            $allCategoryList[$category['id']] = [
                'id' => $category['id'],
                'name' => $categoryName,
            ];
        }
        $categoryList = array_intersect_key($allCategoryList, $categoryIds);

        return $this->render('Merchant/Detail', [
            'iMerchant' => [
                'id' => $merchant->id,
                'name' => $merchant->name,
                'legal_name' => $merchant->legal_name,
                'status' => $merchant->status,
                'status_at' => $merchant->status_at,
                'city' => $merchant->city,
                'rating_id' => $merchant->rating_id,
                'manager_id' => $merchant->manager_id,
                'created_at' => $merchant->created_at,
                'legal_address' => $merchant->legal_address,
                'inn' => $merchant->inn,
                'kpp' => $merchant->kpp,
                'fact_address' => $merchant->fact_address,
                'ceo_last_name' => $merchant->ceo_last_name,
                'ceo_first_name' => $merchant->ceo_first_name,
                'ceo_middle_name' => $merchant->ceo_middle_name,
                'payment_account' => $merchant->payment_account,
                'correspondent_account' => $merchant->correspondent_account,
                'bank' => $merchant->bank,
                'bank_address' => $merchant->bank_address,
                'bank_bik' => $merchant->bank_bik,
                'storage_address' => $merchant->storage_address,
                'sale_info_brands' => json_decode($merchant->sale_info, true)['brands'] ?? [],
                'sale_info_categories' => json_decode($merchant->sale_info, true)['categories'] ?? [],
                'vat_info' => $merchant->vat_info,
                'commercial_info' => $merchant->commercial_info,
                'commissionaire_contract_number' => $merchant->commissionaire_contract_number,
                'commissionaire_contract_at' => $merchant->commissionaire_contract_at
                    ? Carbon::createFromFormat('Y-m-d', $merchant->commissionaire_contract_at)->format('Y-m-d') : null,
                'agent_contract_number' => $merchant->agent_contract_number,
                'agent_contract_at' => $merchant->agent_contract_at
                    ? Carbon::createFromFormat('Y-m-d', $merchant->agent_contract_at)->format('Y-m-d') : null,
                'main_operator' => [
                    'first_name' => $userMain ? $userMain->first_name : '',
                    'last_name' => $userMain ? $userMain->last_name : 'N/A',
                    'middle_name' => $userMain ? $userMain->middle_name : '',
                    'phone' => $userMain ? $userMain->phone : 'N/A',
                    'email' => $userMain ? $userMain->email : 'N/A',
                ],
                'operators' => $operatorUsers->map(function (UserDto $operatorUser) {
                        return [
                            'id' => $operatorUser->id,
                            'title' => $operatorUser->getTitle() . (array_key_exists(
                                UserDto::MAS__MERCHANT_ADMIN,
                                $operatorUser->roles
                            ) ? ' (Администратор)' : ''),
                            'email' => $operatorUser->email,
                        ];
                })->values()
                        ->all(),
            ],
            'statuses' => MerchantStatus::statusesByMode($isRequest),
            'ratings' => $ratings->map(function (RatingDto $ratingDto) {
                return [
                    'id' => $ratingDto->id,
                    'name' => $ratingDto->name,
                ];
            }),
            'managers' => $managers->sortByDesc('full_name')->map(function (UserDto $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                ];
            }),
            'isRequest' => $isRequest,
            'unreadMsgCount' => $unreadMsgCount,
            'brandList' => $brandList,
            'categoryList' => $categoryList,
        ]);
    }

    public function updateMerchant(int $id, MerchantService $merchantService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $this->validate(request(), [
            'merchant.name' => 'nullable',
            'merchant.legal_name' => 'nullable',
            'merchant.status' => ['nullable', Rule::in(array_keys(MerchantStatus::allStatuses()))],
            'merchant.city' => 'nullable',
            'merchant.rating_id' => 'nullable|integer',
            'merchant.manager_id' => 'nullable|integer',

            'merchant.inn' => ['nullable', 'regex:/^\d{10}(\d{2})?$/'],
            'merchant.kpp' => 'nullable|string|size:9',
            'merchant.fact_address' => 'nullable|string',
            'merchant.legal_address' => 'nullable|string',

            'merchant.ceo_last_name' => 'nullable|string',
            'merchant.ceo_first_name' => 'nullable|string',
            'merchant.ceo_middle_name' => 'nullable|string',

            'merchant.payment_account' => 'nullable|string|size:20',
            'merchant.correspondent_account' => 'nullable|string|size:20',
            'merchant.bank' => 'nullable|string',
            'merchant.bank_address' => 'nullable|string',
            'merchant.bank_bik' => 'nullable|string|size:9',

            'merchant.storage_address' => 'nullable|string',
            'merchant.sale_info_brands' => 'array|nullable',
            'merchant.sale_info_brands.*' => 'integer',
            'merchant.sale_info_categories' => 'array|nullable',
            'merchant.sale_info_categories.*' => 'integer',
            'merchant.vat_info' => 'nullable|string',
            'merchant.commercial_info' => 'nullable|string',

            'merchant.commissionaire_contract_number' => 'nullable|string',
            'merchant.commissionaire_contract_at' => 'nullable|date_format:Y-m-d',
        ]);

        if (isset($data['merchant']['sale_info_brands']) && isset($data['merchant']['sale_info_categories'])) {
            $sale_info = [
                'brands' => $data['merchant']['sale_info_brands'],
                'categories' => $data['merchant']['sale_info_categories'],
            ];
            $data['merchant']['sale_info'] = json_encode($sale_info);
            unset($data['merchant']['sale_info_brands']);
            unset($data['merchant']['sale_info_categories']);
        }

        $editedMerchant = new MerchantDto($data['merchant']);
        $editedMerchant->id = $id;
        $merchantService->update($editedMerchant);

        return response('', 204);
    }
}
