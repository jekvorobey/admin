<?php

namespace App\Http\Controllers\Claim;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Message\Dto\Claim\ClaimDto;
use Greensight\Message\Dto\Claim\ClaimTypeDto;
use Greensight\Message\Dto\Claim\PhotoClaimDto;
use Greensight\Message\Services\ClaimService\ClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PhotoClaimController extends Controller
{
    public function index(
        Request $request,
        ClaimService $claimService,
        MerchantService $merchantService
    )
    {
        $this->breadcrumbs = 'claims.photo';
        $this->title = 'Заявки на съёмку';

        /** @var ClaimTypeDto $claimType */
        $claimType = $claimService->newQuery()
            ->include('statusNames')
            ->setFilter('type', ClaimTypeDto::TYPE_PHOTO)
            ->claimTypes()
            ->first();

        $query = $this->prepareQuery($request, $claimService);
        $claims = $this->loadClaims($query, $merchantService);
        $pager = $query->countClaims();

        return $this->render('Claim/PhotoClaimList', [
            'iClaims' => $claims,
            'statuses' => $claimType->statusNames,
            'iPager' => $pager,
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
        ]);
    }

    public function page(
        Request $request,
        ClaimService $claimService,
        MerchantService $merchantService
    )
    {
        $query = $this->prepareQuery($request, $claimService);
        $result = [
            'items' => $this->loadClaims($query, $merchantService),
        ];
        if ($request->get('page') == 1) {
            $result['pager'] = $query->countClaims();
        }
        return response()->json($result);
    }

    public function detail(
        int $id,
        ClaimService $claimService,
        MerchantService $merchantService,
        ProductService $productService
    )
    {
        $this->breadcrumbs = 'claims.photo.detail';
        $this->title = 'Заявка на съёмку';

        $query = $claimService->newQuery()->setFilter('id', $id);
        $claim = $this->loadClaims($query, $merchantService)->first();

        if (!$claim) {
            throw new NotFoundHttpException();
        }

        /** @var ClaimTypeDto $claimType */
        $claimType = $claimService->newQuery()
            ->include('statusNames')
            ->setFilter('type', ClaimTypeDto::TYPE_PHOTO)
            ->claimTypes()
            ->first();

        $productIds = $claim->payload['productId'];
        /** @var Collection|ProductDto[] $products */
        $products = $productService->newQuery()->setFilter('id', $productIds)->products();

        return $this->render('Claim/PhotoClaimDetail', [
            'claim' => $claim,
            'statuses' => $claimType->statusNames,
            'products' => $products
        ]);
    }

    protected function prepareQuery(Request $request, ClaimService $claimService)
    {
        $page = $request->get('page', 1);
        $filter = $request->get('filter');
        $showDone = ($filter['showDone'] ?? 'false') == 'false' ? false : true;
        $restQuery = $claimService
            ->newQuery()
            ->setFilter('type', ClaimTypeDto::TYPE_PHOTO)
            ->pageNumber($page, 20);
        if (!$showDone) {
            $restQuery->setFilter('status', '!=', PhotoClaimDto::STATUS_DONE);
        }
        return $restQuery;
    }

    protected function loadClaims(RestQuery $query, MerchantService $merchantService)
    {
        /** @var Collection|ClaimDto[] $claims */
        $claims = $query->claims();

        $merchantIds = $claims->pluck('payload.merchantId')->all();
        /** @var Collection|MerchantDto[] $merchants */
        $merchants = $merchantService->newQuery()->setFilter('id', $merchantIds)->merchants()->keyBy('id');
        return $claims->map(function (PhotoClaimDto $claim) use ($merchants) {
            $merchantId = $claim->getMerchantId();
            $claim['merchant'] = $merchants->has($merchantId) ? $merchants->get($merchantId)->legal_name : 'N/A';
            return $claim;
        });
    }
}