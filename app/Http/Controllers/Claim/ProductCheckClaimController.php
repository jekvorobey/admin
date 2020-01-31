<?php

namespace App\Http\Controllers\Claim;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Message\Dto\Claim\ClaimTypeDto;
use Greensight\Message\Dto\Claim\ProductCheckClaimDto;
use Greensight\Message\Services\ClaimService\ClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
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

        $statuses = $claimTypes->firstWhere('id', ClaimTypeDto::TYPE_PRODUCT_CHECK)->statusNames;
        $types = $claimTypes->pluck('name', 'id')->toArray();

        return $this->render('Claim/ProductCheck/List', [
            'iClaims' => $claims,
            'statuses' => $statuses,
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'display_name')->merchants(),
            'types' => $types,
            'iPager' => $pager,
            'iCurrentPage' => (int) $request->get('page', 1),
            'iFilter' => $this->getFilter(),
        ]);
    }

    /**
     * @param  Request  $request
     * @param  ClaimService  $claimService
     * @param  UserService  $userService
     * @return \Illuminate\Http\JsonResponse
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
                'status' => Rule::in([
                    ProductCheckClaimDto::STATUS_NEW,
                    ProductCheckClaimDto::STATUS_WORK,
                    ProductCheckClaimDto::STATUS_DONE,
                ]),
                'created_at' => 'array|someone',
            ]
        )->attributes();
    }

    /**
     * @param  int  $id
     * @param  ClaimService  $claimService
     * @param  UserService  $userService
     * @param  ProductService  $productService
     * @return mixed
     */
    public function detail(
        int $id,
        ClaimService $claimService,
        UserService $userService,
        ProductService $productService
    ) {
        $this->title = 'Заявка на проверку товаров';

        $query = $claimService->newQuery()->setFilter('id', $id);
        /** @var ProductCheckClaimDto $claim */
        $claim = $this->loadClaims($query, $userService)->first();

        if (!$claim) {
            throw new NotFoundHttpException();
        }

        /** @var ClaimTypeDto $claimType */
        $claimType = $claimService->newQuery()
            ->include('statusNames')
            ->setFilter('type', ClaimTypeDto::TYPE_PRODUCT_CHECK)
            ->claimTypes()
            ->first();

        $productIds = $claim['payload']['productIds'];
        /** @var Collection|ProductDto[] $products */
        $products = $productService->newQuery()->setFilter('id', $productIds)->products();

        return $this->render('Claim/ProductCheck/Detail', [
            'claim' => $claim,
            'statuses' => $claimType->statusNames,
            'products' => $products
        ]);
    }

    /**
     * @param  Request  $request
     * @param  ClaimService  $claimService
     * @return \Greensight\CommonMsa\Dto\DataQuery
     */
    protected function prepareQuery(Request $request, ClaimService $claimService)
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
     * @return Collection
     */
    protected function loadClaims(RestQuery $query, UserService $userService)
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

        return $claims->map(function (ProductCheckClaimDto $claim) use ($users, $merchants) {
            $data = $claim->toArray();

            $data['payload']['merchant'] = $merchants->has($claim->getMerchantId()) ? $merchants[$claim->getMerchantId()] : [];
            $data['userName'] = $users->has($claim->user_id) ? $users[$claim->user_id]->full_name : 'N/A';

            return $data;
        });
    }
}
