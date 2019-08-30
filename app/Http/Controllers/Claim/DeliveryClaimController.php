<?php

namespace App\Http\Controllers\Claim;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Message\Dto\Claim\ClaimDto;
use Greensight\Message\Dto\Claim\ClaimTypeDto;
use Greensight\Message\Dto\Claim\DeliveryClaimDto;
use Greensight\Message\Dto\Claim\PhotoClaimDto;
use Greensight\Message\Services\ClaimService\ClaimService;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DeliveryClaimController
 * @package App\Http\Controllers\Claim
 *
 * @deprecated
 * @todo удалить т.к. устарело
 */
class DeliveryClaimController extends Controller
{
    public function index(
        Request $request,
        ClaimService $claimService,
        MerchantService $merchantService
    )
    {
        $this->breadcrumbs = 'claims.delivery';
        $this->title = 'Заявки на доставку';

        /** @var ClaimTypeDto $claimType */
        $claimType = $claimService->newQuery()
            ->include('statusNames')
            ->claimTypes()
            ->filter(function (ClaimTypeDto $claimType) { return $claimType->id == ClaimTypeDto::TYPE_DELIVERY;})
            ->first();

        $query = $this->prepareQuery($request, $claimService);
        $claims = $this->loadClaims($query, $merchantService);
        $pager = $query->countClaims();

        return $this->render('Claim/DeliveryClaimList', [
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
        ProductService $productService,
        StoreService $storeService
    )
    {
        $query = $claimService->newQuery()->setFilter('id', $id);
        /** @var DeliveryClaimDto $claim */
        $claim = $this->loadClaims($query, $merchantService)->first();
        if (!$claim) {
            throw new NotFoundHttpException();
        }

        /** @var ClaimTypeDto $claimType */
        $claimType = $claimService->newQuery()
            ->include('statusNames')
            ->claimTypes()
            ->filter(function (ClaimTypeDto $claimType) { return $claimType->id == ClaimTypeDto::TYPE_DELIVERY;})
            ->first();

        $productIds = $claim->payload['productIds'];
        /** @var Collection|ProductDto[] $products */
        $products = $productService->newQuery()->setFilter('id', $productIds)->products();
        $merchantId = $claim->payload['merchantId'] ?? null;
        if ($merchantId) {
            $stores = $storeService->merchantStores($merchantId)->pluck('name', 'id');
        } else {
            $stores = [];
        }


        return $this->render('Claim/DeliveryClaimDetail', [
            'iClaim' => $claim,
            'statuses' => $claimType->statusNames,
            'products' => $products,
            'stores' => $stores,
        ]);
    }

    public function create(Request $request, ClaimService $claimService, RequestInitiator $user)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'productIds' => ['required', 'array'],
            'merchantId' => ['required', 'integer'],
            'photoClaimId' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            throw new BadRequestHttpException('invalid input');
        }
        $photoClaim = null;
        if ($data['photoClaim']) {
            /** @var PhotoClaimDto|null $photoClaim */
            $photoClaim = $claimService->newQuery()->setFilter('id', $data['photoClaim'])->claims()->first();
            if (!$photoClaim) {
                throw new BadRequestHttpException('invalid input');
            }
        }

        $claim = new DeliveryClaimDto();
        $claim->user_id = $user->userId();
        $claim->setProductIds($data['productIds']);
        $claim->setMerchantId($data['merchantId']);
        $claim->status = 1;
        $id = $claimService->createClaim($claim);
        if ($photoClaim) {
            $photoClaim->setDeliveryId($id);
            $claimService->updateClaim($photoClaim->id, $photoClaim);
        }

        return response()->json([
            'id' => $id
        ]);
    }

    public function update(int $id, Request $request, ClaimService $claimService)
    {
        $availableStatuses = [
            DeliveryClaimDto::STATUS_NEW,
            DeliveryClaimDto::STATUS_READY_TO_SHIP,
            DeliveryClaimDto::STATUS_DELIVERY,
            DeliveryClaimDto::STATUS_DONE,
        ];
        $data = $request->all();
        $validator = Validator::make($data, [
            'status' => ['nullable', Rule::in($availableStatuses)],
            'store' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }
        if (!count($data)) {
            throw new BadRequestHttpException('"status" or "store" required');
        }
        /** @var DeliveryClaimDto|null $claim */
        $claim = $claimService->newQuery()->setFilter('id', $id)->claims()->first();
        if (!$claim) {
            throw new NotFoundHttpException();
        }

        if ($data['status']) {
            $claim->status = $data['status'];
        }
        if ($data['store']) {
            $claim->setStoreId($data['store']);
        }

        $claimService->updateClaim($claim->id, $claim);

        return response()->json([]);
    }

    protected function prepareQuery(Request $request, ClaimService $claimService)
    {
        $page = $request->get('page', 1);
        $filter = $request->get('filter');
        $showDone = ($filter['showDone'] ?? 'false') == 'false' ? false : true;
        $restQuery = $claimService
            ->newQuery()
            ->setFilter('type', ClaimTypeDto::TYPE_DELIVERY)
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
        return $claims->map(function (DeliveryClaimDto $claim) use ($merchants) {
            $merchantId = $claim->getMerchantId();
            $claim['merchant'] = $merchants->has($merchantId) ? $merchants->get($merchantId)->legal_name : 'N/A';
            return $claim;
        });
    }
}