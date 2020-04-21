<?php

namespace App\Http\Controllers\Claim;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Message\Dto\Claim\ClaimDto;
use Greensight\Message\Dto\Claim\ClaimTypeDto;
use Greensight\Message\Dto\Claim\Content\AllClaimDto;
use Greensight\Message\Services\ClaimService\ClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MerchantManagement\Services\OperatorService\OperatorService;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ContentClaimController
 * @package App\Http\Controllers\Claim
 */
class ContentClaimController extends Controller
{
    /** @var array */
    protected $contentTypes = [
        ClaimTypeDto::TYPE_CONTENT_TEXT,
        ClaimTypeDto::TYPE_CONTENT_PHOTO,
        ClaimTypeDto::TYPE_CONTENT_ALL
    ];

    /**
     * @param  Request  $request
     * @param  ClaimService  $claimService
     * @param  OperatorService  $operatorService
     * @return mixed
     */
    public function index(
        Request $request,
        ClaimService $claimService,
        OperatorService $operatorService
    )
    {
        $this->title = 'Заявки на производство контента';

        /** @var Collection|ClaimTypeDto[] $claimTypes */
        $claimTypes = $claimService->newQuery()
            ->include('statusNames')
            ->setFilter('type', $this->contentTypes)
            ->claimTypes();

        $query = $this->prepareQuery($request, $claimService, $operatorService);
        $claims = $this->loadClaims($query, $operatorService);
        $pager = $query->countClaims();

        $types = $claimTypes->pluck('name', 'id')->toArray();

        return $this->render('Claim/Content/List', [
            'iClaims' => $claims,
            'statuses' => $claimTypes->firstWhere('id', ClaimTypeDto::TYPE_CONTENT_ALL)->statusNames,
            'types' => $types,
            'iPager' => $pager,
            'iCurrentPage' => (int) $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
        ]);
    }

    /**
     * @param  Request  $request
     * @param  ClaimService  $claimService
     * @param  OperatorService  $operatorService
     * @return \Illuminate\Http\JsonResponse
     */
    public function page(
        Request $request,
        ClaimService $claimService,
        OperatorService $operatorService
    ) {
        $query = $this->prepareQuery($request, $claimService, $operatorService);
        $result = [
            'items' => $this->loadClaims($query, $operatorService),
        ];
        if ($request->get('page') == 1) {
            $result['pager'] = $query->countClaims();
        }
        return response()->json($result);
    }

    /**
     * @param  int  $id
     * @param  ClaimService  $claimService
     * @param  OperatorService  $operatorService
     * @param  ProductService  $productService
     * @return mixed
     */
    public function detail(
        int $id,
        ClaimService $claimService,
        OperatorService $operatorService,
        ProductService $productService
    ) {
        $this->title = 'Заявка на производство контента';

        $query = $claimService->newQuery()->setFilter('id', $id);
        $claim = $this->loadClaims($query, $operatorService)->first();

        if (!$claim) {
            throw new NotFoundHttpException();
        }

        /** @var Collection|ClaimTypeDto[] $claimTypes */
        $claimTypes = $claimService->newQuery()
            ->include('statusNames')
            ->setFilter('type', $this->contentTypes)
            ->claimTypes();

        $productIds = $claim->payload['productId'];
        /** @var Collection|ProductDto[] $products */
        $products = $productService->newQuery()->setFilter('id', $productIds)->products();

        return $this->render('Claim/Content/Detail', [
            'claim' => $claim,
            'statuses' => $claimTypes->firstWhere('id', ClaimTypeDto::TYPE_CONTENT_ALL)->statusNames,
            'products' => $products
        ]);
    }

    //    public function create(
    //        ClaimService $claimService,
    //        OperatorService $operatorService,
    //        RequestInitiator $user,
    //        ProductBuffer $buffer
    //    )
    //    {
    //        $productIds = $buffer->all();
    //        if (empty($productIds)) {
    //            throw new BadRequestHttpException('po products selected');
    //        }
    //        $operator = $operatorService->current();
    //        if (!$operator) {
    //            throw new BadRequestHttpException('user is not operator');
    //        }
    //
    //        $claim = new PhotoClaimDto();
    //        $claim->type = ClaimTypeDto::TYPE_PHOTO;
    //        $claim->user_id = $user->userId();
    //        $claim->setProductId(array_values($productIds));
    //        $claim->setMerchantId($operator->merchant_id);
    //
    //        $claimService->createClaim($claim);
    //        return response()->json();
    //    }

    /**
     * @param  Request  $request
     * @param  ClaimService  $claimService
     * @param  OperatorService  $operatorService
     * @return \Greensight\CommonMsa\Dto\DataQuery
     */
    protected function prepareQuery(Request $request, ClaimService $claimService, OperatorService $operatorService)
    {
        $page = $request->get('page', 1);
        $filters = array_filter($request->get('filter', []));

        $restQuery = $claimService->newQuery()->addSort('created_at', 'desc');
        $restQuery->setFilter('type', $this->contentTypes);

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'showDone':
                    if($value == "true") {
                        $restQuery->setFilter('status', AllClaimDto::STATUS_DONE);
                    } else {
                        $restQuery->setFilter('status', '!=', AllClaimDto::STATUS_DONE);
                    }
                    break;

                case 'created_at':
                    $restQuery->setFilter('created_at', '>=', Carbon::parse($value));
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
     * @param  OperatorService  $operatorService
     * @return Collection
     */
    protected function loadClaims(RestQuery $query, OperatorService $operatorService)
    {
        /** @var Collection|ClaimDto[] $claims */
        $claims = $query->claims();

        $userIds = $claims->pluck('user_id')->all();
        $operatorsQuery = (new RestQuery())->setFilter('user_id', $userIds);
        $users = $operatorService->operators($operatorsQuery)->keyBy('user_id');
        return $claims->map(function (ClaimDto $claim) use ($users) {
            $claim['userName'] = $users->has($claim->user_id) ? $users->get($claim->user_id)->name : 'N/A';
            return $claim;
        });
    }
}
