<?php

namespace App\Http\Controllers\Claim;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Message\Dto\Claim\ContentClaimDto;
use Greensight\Message\Dto\Claim\History\ClaimHistoryDto;
use Greensight\Message\Dto\Claim\History\ClaimHistoryTypeDto;
use Greensight\Message\Services\ClaimService\ContentClaimService;
use Greensight\Message\Dto\Document\DocumentDto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Greensight\CommonMsa\Services\AuthService\UserService;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Dto\MerchantStatus;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Offer\OfferDto;
use Pim\Dto\Product\ProductArchiveStatus;
use Pim\Dto\Product\ProductDto;
use Pim\Services\OfferService\OfferService;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ContentClaimController
 * @package App\Http\Controllers\Claim
 */
class ContentClaimController extends Controller
{
    /**
     * @return mixed
     */
    public function index(
        Request $request,
        ContentClaimService $claimService,
        UserService $userService,
        MerchantService $merchantService
    ) {
        $this->title = 'Заявки на производство контента';

        /** @var Collection|ContentClaimMetaDto[] $claimMeta */
        $claimMeta = $claimService->newQuery()
            ->include('typeNames')
            ->include('unpackNames')
            ->include('statusNames')
            ->include('noUnpack')
            ->include('adjustStatuses')
            ->include('deliveryConfirm')
            ->claimMeta();
        $meta = $claimMeta->pluck('value', 'propName');

        $query = $this->prepareQuery($request, $claimService, $userService);
        $claims = $query ? $this->loadClaims($query, $userService, $merchantService) : [];
        $pager = $query ? $query->countClaims() : [];

        $merchantsQuery = (new RestQuery())->addFields(MerchantDto::entity(), 'id', 'legal_name');
        $merchants = $merchantService->merchants($merchantsQuery)->pluck('legal_name', 'id');

        return $this->render('Claim/Content/List', [
            'iClaims' => $claims,
            'options' => [
                'merchants' => $merchants,
                'statuses' => $meta['statusNames'],
                'types' => $meta['typeNames'],
                'unpack' => $meta['unpackNames'],
                'adjustStatuses' => $meta['adjustStatuses'],
                'noUnpack' => $meta['noUnpack'],
                'deliveryConfirm' => $meta['deliveryConfirm'],
            ],
            'iPager' => $pager,
            'iCurrentPage' => (int) $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
        ]);
    }

    /**
     * @return mixed
     */
    public function create(ContentClaimService $claimService, MerchantService $merchantService)
    {
        $this->title = 'Создание заявки на производство контента';

        $claimMeta = $claimService->newQuery()
            ->include('typeNames')
            ->include('unpackNames')
            ->include('noUnpack')
            ->include('statusNames')
            ->claimMeta();

        $meta = $claimMeta->pluck('value', 'propName');

        $merchantsQuery = (new RestQuery())
            ->addFields(MerchantDto::entity(), 'id', 'legal_name')
            ->setFilter('status', MerchantStatus::STATUS_WORK);
        $merchants = $merchantService
            ->merchants($merchantsQuery)
            ->pluck('legal_name', 'id');


        return $this->render('Claim/Content/Create', [
            'options' => [
                'merchantOptions' => $merchants,
                'typeOptions' => $meta['typeNames'],
                'unpackOptions' => $meta['unpackNames'],
                'noUnpack' => $meta['noUnpack'],
            ],
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveClaim(Request $request, RequestInitiator $user, ContentClaimService $contentClaimService)
    {
        $data = $this->validate($request, [
            'merchant_id' => 'required|integer',
            'type' => 'required|integer',
            'unpacking' => 'nullable|boolean',
            'product_ids' => 'required|array',
        ]);
        $data['user_id'] = $user->userId();

        $contentClaim = new ContentClaimDto($data);

        $id = $contentClaimService->createClaim($contentClaim);
        return response()->json($id);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function page(
        Request $request,
        ContentClaimService $claimService,
        UserService $userService,
        MerchantService $merchantService
    ) {
        $query = $this->prepareQuery($request, $claimService, $userService);
        $result = $query ? [
            'items' => $this->loadClaims($query, $userService, $merchantService),
        ] : [];
        if ($query && $request->get('page') == 1) {
            $result['pager'] = $query->countClaims();
        }
        return response()->json($result);
    }

    /**
     * @return mixed
     */
    public function detail(
        int $id,
        ContentClaimService $claimService,
        UserService $userService,
        MerchantService $merchantService,
        ProductService $productService
    ) {
//        $this->title = 'Заявка на производство контента';

        $query = $claimService->newQuery()->setFilter('id', $id);
        /** @var ContentClaimDto $claim */
        $claim = $this->loadClaims($query, $userService, $merchantService)->first();

        if (!$claim) {
            throw new NotFoundHttpException();
        }

        /** @var Collection|ContentClaimMetaDto[] $claimMeta */
        $claimMeta = $claimService->newQuery()
            ->include('typeNames')
            ->include('unpackNames')
            ->include('statusNames')
            ->claimMeta();

        $meta = $claimMeta->pluck('value', 'propName');

        $productIds = $claim->product_ids;
        /** @var Collection|ProductDto[] $products */
        $products = $productService->newQuery()
            ->include(BrandDto::entity(), CategoryDto::entity())
            ->addFields(ProductDto::entity(), 'id', 'name', 'vendor_code')
            ->addFields(BrandDto::entity(), 'id', 'name')
            ->addFields(CategoryDto::entity(), 'id', 'name')
            ->setFilter('id', $productIds)
            ->products();

        $historyQuery = $claimService->newQuery()
            ->addFields(ClaimHistoryDto::entity(), 'user_id', 'type', 'data', 'created_at')
            ->setFilter('entity_id', $id)
            ->addSort('created_at', 'desc');
        $history = $this->loadClaimHistory($historyQuery, $userService);

        return $this->render('Claim/Content/Detail', [
            'claim' => $claim,
            'options' => [
                'statuses' => $meta['statusNames'],
                'types' => $meta['typeNames'],
                'unpack' => $meta['unpackNames'],
            ],
            'products' => $products,
            'history' => $history,
            'historyMeta' => ClaimHistoryTypeDto::claimHistoryMeta(),
            'roleNames' => UserDto::roles(),
        ]);
    }

    public function update(int $id, Request $request, ContentClaimService $contentClaimService)
    {
        $data = $this->validate($request, [
            'service_message' => 'sometimes|required|string',
            'status' => 'sometimes|required|integer',
        ]);

        $contentClaim = new ContentClaimDto($data);
        $contentClaimService->updateClaim($id, $contentClaim);

        return response()->json(['status' => 'ok']);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatuses(Request $request, ContentClaimService $contentClaimService)
    {
        $data = $this->validate($request, [
            'claim_ids' => 'required|array',
            'status' => 'required|integer',
        ]);

        $contentClaimService->updateClaims($data['claim_ids'], $data['status']);
        return response()->json(['status' => 'ok']);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteClaims(Request $request, ContentClaimService $contentClaimService)
    {
        $data = $this->validate($request, [
            'claim_ids' => 'required|array',
        ]);

        $contentClaimService->deleteClaims($data['claim_ids']);
        return response()->json(['status' => 'ok']);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Pim\Core\PimException
     */
    public function loadProductsByMerchantId(Request $request, OfferService $offerService)
    {
        $data = $this->validate($request, [
            'id' => 'required|integer',
        ]);
        $query = $offerService->newQuery()
            ->addFields(OfferDto::entity(), 'product_id')
            ->setFilter('merchant_id', $data['id'])
            ->setFilter('archive', ProductArchiveStatus::NO_ARCHIVE);
        $products = $offerService->offers($query);
        $availableIds = $products->pluck('product_id')->all();

        return response()->json([
            'ids' => $availableIds,
        ]);
    }

    public function acceptanceAct(int $id, ContentClaimService $contentClaimService): StreamedResponse
    {

        return $this->getDocumentResponse($contentClaimService->claimAcceptanceAct($id));
    }

    protected function getDocumentResponse(DocumentDto $documentDto): StreamedResponse
    {
        return response()->streamDownload(function () use ($documentDto) {
            echo file_get_contents($documentDto->absolute_url);
        }, $documentDto->original_name);
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
     * @return \Greensight\CommonMsa\Dto\DataQuery|null
     */
    protected function prepareQuery(Request $request, ContentClaimService $claimService, UserService $userService)
    {
        $page = $request->get('page', 1);
        $filters = array_filter($request->get('filter', []), function ($v, $k) {
            return $v || ($k == 'unpack');
        }, ARRAY_FILTER_USE_BOTH);

        $restQuery = $claimService
            ->newQuery()
            ->addSort('created_at', 'desc');

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'merchant':
                    $restQuery->setFilter('merchant_id', $value);
                    break;

                case 'unpack':
                    $restQuery->setFilter('unpacking', $value);
                    break;

                case 'created_at':
                    $value = array_filter($value);
                    if ($value) {
                        $restQuery->setFilter('created_at', '>=', $value[0]);
                        $restQuery->setFilter('created_at', '<', Carbon::parse($value[1])
                            ->addDay()
                            ->toDateTimeString());
                    }
                    break;

                case 'user':
                    $userQuery = $userService
                        ->newQuery()
                        ->prepare($userService)
                        ->addFields('id')
                        ->setFilter('login', $value);
                    $user = $userQuery->users()->first();
                    if (!$user) {
                        return null;
                    }
                    $restQuery->setFilter('user_id', $user->id);
                    break;

                default:
                    $restQuery->setFilter($key, $value);
            }
        }

        $restQuery->pageNumber($page, 20);

        return $restQuery;
    }

    /**
     * @return Collection
     */
    protected function loadClaims(RestQuery $query, UserService $userService, MerchantService $merchantService)
    {
        /** @var Collection|ContentClaimDto[] $claims */
        $claims = $query->claims();

        $userIds = $claims->pluck('user_id')->all();
        $usersQuery = (new RestQuery())->setFilter('id', $userIds);
        /** @var Collection|UserDto[] $users */
        $users = $userService->users($usersQuery)->keyBy('id');

        $merchantIds = $claims->pluck('merchant_id')->all();
        $merchantQuery = (new RestQuery())
            ->addFields(MerchantDto::entity(), 'id', 'legal_name')
            ->setFilter('id', $merchantIds);
        $merchants = $merchantService->merchants($merchantQuery)->keyBy('id');

        return $claims->map(function (ContentClaimDto $claim) use ($users, $merchants) {
            $claim['userName'] = $users->has($claim->user_id) ? $users->get($claim->user_id)->short_name : 'N/A';
            $claim['userLogin'] = $users->has($claim->user_id) ? $users->get($claim->user_id)->login : 'N/A';
            $claim['merchantName'] = $merchants->has($claim->merchant_id) ? $merchants->get($claim->merchant_id)->legal_name : 'N/A';
            $claim['merchantId'] = $merchants->has($claim->merchant_id) ? $merchants->get($claim->merchant_id)->id : 'N/A';
            return $claim;
        });
    }

    protected function loadClaimHistory(RestQuery $query, UserService $userService)
    {

        /** @var Collection|ClaimHistoryDto[] $history */
        $history = $query->claimHistory();
        $userIds = $history->pluck('user_id')->unique()->all();

        $usersRoles = [];
        foreach ($userIds as $id) {
            $usersRoles[$id] = $userService->userRoles($id)->first();
        }
        $usersQuery = (new RestQuery())->setFilter('id', $userIds);
        $userNames = $userService->users($usersQuery)->keyBy('id');

        return $history->map(function (ClaimHistoryDto $event) use ($userNames, $usersRoles) {
            $event['userRoleId'] = $usersRoles[$event->user_id]['id'];
            $event['userName'] = $userNames->has($event->user_id) ? $userNames->get($event->user_id)->short_name : 'N/A';
            return $event;
        });
    }
}
