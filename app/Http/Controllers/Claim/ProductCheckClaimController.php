<?php

namespace App\Http\Controllers\Claim;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Message\Dto\Claim\ClaimTypeDto;
use Greensight\Message\Dto\Claim\ProductCheckClaimDto;
use Greensight\Message\Services\ClaimService\ClaimService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Dto\Product\ProductApprovalStatus;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductCheckClaimController
 * @package App\Http\Controllers\Claim
 */
class ProductCheckClaimController extends Controller
{
    /**
     * @param  Request  $request
     * @param  ClaimService  $claimService
     * @param  MerchantService  $merchantService
     * @param  UserService  $userService
     * @return mixed
     */
    public function index(
        Request $request,
        ClaimService $claimService,
        MerchantService $merchantService,
        UserService $userService
    )
    {
        $this->title = 'Заявки на проверку товаров';

        /** @var Collection|ClaimTypeDto[] $claimTypes */
        $claimTypes = $claimService->newQuery()
            ->include('statusNames')
            ->setFilter('type', ClaimTypeDto::TYPE_PRODUCT_CHECK)
            ->claimTypes();

        $query = $this->prepareQuery($request, $claimService);
        $claims = $this->loadClaims($query, $userService);
        $pager = $query->countClaims();

        return $this->render('Claim/ProductCheck/List', [
            'iClaims' => $claims,
            'claimStatuses' => $claimTypes->firstWhere('id', ClaimTypeDto::TYPE_PRODUCT_CHECK)->statusNames,
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'display_name')->merchants(),
            'iPager' => $pager,
            'iCurrentPage' => (int) $request->get('page', 1),
            'iFilter' => $this->getFilter(),
        ]);
    }

    /**
     * @param  Request  $request
     * @param  ClaimService  $claimService
     * @param  UserService  $userService
     * @return JsonResponse
     */
    public function page(
        Request $request,
        ClaimService $claimService,
        UserService $userService
    ) {
        $query = $this->prepareQuery($request, $claimService);
        $result = [
            'items' => $this->loadClaims($query, $userService),
        ];
        if ($request->get('page') == 1) {
            $result['pager'] = $query->countClaims();
        }
        return response()->json($result);
    }

    /**
     * @param  int  $id
     * @param  ClaimService  $claimService
     * @param  UserService  $userService
     * @return mixed
     */
    public function detail(
        int $id,
        ClaimService $claimService,
        UserService $userService
    ) {
        $query = $claimService->newQuery()->setFilter('id', $id);
        /** @var ProductCheckClaimDto $claim */
        $claim = $this->loadClaims($query, $userService, true)->first();

        if (!$claim) {
            throw new NotFoundHttpException();
        }

        /** @var Collection|ClaimTypeDto[] $claimTypes */
        $claimTypes = $claimService->newQuery()
            ->include('statusNames')
            ->setFilter('type', ClaimTypeDto::TYPE_PRODUCT_CHECK)
            ->claimTypes();

        return $this->render('Claim/ProductCheck/Detail', [
            'iClaim' => $claim,
            'claimStatuses' => $claimTypes->firstWhere('id', ClaimTypeDto::TYPE_PRODUCT_CHECK)->statusNames,
        ]);
    }

    /**
     * @return array
     */
    protected function getFilter(): array
    {
        return Validator::make(request('filter') ??
            [
                'status' => [
                    ProductCheckClaimDto::STATUS_NEW,
                    ProductCheckClaimDto::STATUS_WORK,
                ],
            ],
            [
                'id' => 'integer|someone',
                'merchant_id' => 'integer|someone',
                'status' => $this->validateStatus(),
                'created_at' => 'array|someone',
            ]
        )->attributes();
    }

    /**
     * @return \Illuminate\Validation\Rules\In
     */
    protected function validateStatus(): In
    {
        return Rule::in([
            ProductCheckClaimDto::STATUS_NEW,
            ProductCheckClaimDto::STATUS_WORK,
            ProductCheckClaimDto::STATUS_DONE,
        ]);
    }

    /**
     * Изменить статус заявки
     * @param  int  $id
     * @param  Request  $request
     * @param  ClaimService  $claimService
     * @param  UserService $userService
     * @param  ProductService $productService
     * @return JsonResponse
     */
    public function changeStatus(
        int $id,
        Request $request,
        ClaimService $claimService,
        UserService $userService,
        ProductService $productService
    ): JsonResponse
    {
        $result = 'ok';
        $claim = [];
        $error = '';
        $systemError = '';
        try {
            $data = $this->validate($request, [
                'status' => $this->validateStatus(),
            ]);
            $claim = new ProductCheckClaimDto($data);
            $claimService->updateClaim($id, $claim);

            //Для заявки в статусе "В работе" переводим все товары из заявки в статус согласования "На рассмотрении"
            if ($claim->status == ProductCheckClaimDto::STATUS_WORK) {
                $query = $claimService->newQuery()->setFilter('id', $id);
                /** @var ProductCheckClaimDto $claim */
                $claim = $this->loadClaims($query, $userService)->first();

                if ($claim->getProductIds()) {
                    $productService->changeApprovalStatus(
                        $claim->getProductIds(),
                        ProductApprovalStatus::STATUS_APPROVING
                    );
                }
            }

            $query = $claimService->newQuery()->setFilter('id', $id);
            /** @var ProductCheckClaimDto $claim */
            $claim = $this->loadClaims($query, $userService, true)->first();
        } catch (\Exception $e) {
            $result = 'fail';
            if ($request->get('status') == ProductCheckClaimDto::STATUS_DONE) {
                $error = 'Не все товары в заявке проверены (согласованы или отменены)';
            }
            $systemError = $e->getMessage();
        }

        return response()->json(['result' => $result, 'claim' => $claim, 'error' => $error,'systemErrors' => $systemError]);
    }

    /**
     * @param  Request  $request
     * @param  ClaimService  $claimService
     * @return DataQuery
     */
    protected function prepareQuery(Request $request, ClaimService $claimService): DataQuery
    {
        $page = $request->get('page', 1);
        $filters = $this->getFilter();

        $restQuery = $claimService->newQuery();
        $restQuery->setFilter('type', ClaimTypeDto::TYPE_PRODUCT_CHECK);

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'created_at':
                    $value = array_filter($value);
                    if ($value) {
                        $restQuery->setFilter($key, '>=', $value[0]);
                        $restQuery->setFilter($key, '<=', $value[1]);
                    }
                    break;

                default:
                    $restQuery->setFilter($key, $value);
            }
        }

        $restQuery->pageNumber($page, 20);

        return $restQuery;
    }

    /**
     * @param  RestQuery  $query
     * @param  UserService  $userService
     * @param  bool  $withProducts
     * @return Collection|ProductCheckClaimDto[]
     */
    protected function loadClaims(RestQuery $query, UserService $userService, bool $withProducts = false): Collection
    {
        /** @var Collection|ProductCheckClaimDto[] $claims */
        $claims = $query->claims();

        $merchantIds = [];
        foreach ($claims as $claim) {
            $merchantIds[] = $claim->getMerchantId();
        }
        $merchantIds = array_unique($merchantIds);
        $merchants = $this->getMerchants($merchantIds);

        $userIds = $claims->pluck('user_id')->all();
        $users = collect();
        if ($userIds) {
            $userQuery = $userService
                ->newQuery()
                ->setFilter('id', $userIds)
                ->include('profile');
            $users = $userService->users($userQuery)->keyBy('id');
        }

        /** @var Collection|ProductDto[] $products */
        $products = collect();
        if ($withProducts) {
            /** @var ProductService $productService */
            $productService = resolve(ProductService::class);

            $productIds = [];
            foreach ($claims as $claim) {
                $productIds = $claim->getProductIds();
            }
            if ($productIds) {
                $products = $productService
                    ->newQuery()
                    ->setFilter('id', $productIds)
                    ->addFields(
                        ProductDto::class,
                        'id',
                        'name',
                        'vendor_code',
                        'approval_status'
                    )
                    ->products();
                $products = $products->map(function (ProductDto $product) {
                    $product['approval_status'] = $product->approvalStatus()->toArray();

                    return $product;
                })->keyBy('id');
            }
        }

        return $claims->map(function (ProductCheckClaimDto $claim) use ($users, $merchants, $withProducts, $products) {
            $claim['merchant'] = $merchants->has($claim->getMerchantId()) ? $merchants[$claim->getMerchantId()] : [];
            $claim['userName'] = $users->has($claim->user_id) ? $users[$claim->user_id]->full_name : '';

            if ($withProducts) {
                $claimProducts = [];
                foreach ($claim->getProductIds() as $productId) {
                    if ($products->has($productId)) {
                        $claimProducts[$productId] = $products[$productId];
                    }
                }
                $claim['products'] = $claimProducts;
            }

            return $claim;
        });
    }
}
