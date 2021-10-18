<?php

namespace App\Http\Controllers\Claim;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Marketing\Dto\Price\OfferPriceDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Message\Dto\Claim\ClaimTypeDto;
use Greensight\Message\Dto\Claim\PriceChangeClaimDto;
use Greensight\Message\Services\ClaimService\ClaimService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PriceChangeClaimController
 * @package App\Http\Controllers\Claim
 */
class PriceChangeClaimController extends Controller
{
    private RequestInitiator $requestInitiator;

    public function __construct(RequestInitiator $requestInitiator)
    {
        $this->requestInitiator = $requestInitiator;
    }

    /**
     * @return mixed
     */
    public function index(
        Request $request,
        ClaimService $claimService,
        MerchantService $merchantService,
        UserService $userService
    ) {
        /** Проверка разрешения */
        $this->requestInitiator->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        $this->title = 'Заявки на изменение цен';

        /** @var Collection|ClaimTypeDto[] $claimTypes */
        $claimTypes = $claimService->newQuery()
            ->include('statusNames')
            ->setFilter('type', ClaimTypeDto::TYPE_PRICE_CHANGE)
            ->claimTypes();

        $query = $this->prepareQuery($request, $claimService);
        $claims = $this->loadClaims($query, $userService);
        $pager = $query->countClaims();

        return $this->render('Claim/ClaimList', [
            'routePrefix' => 'priceChangeClaims',
            'iClaims' => $claims,
            'claimStatuses' => $claimTypes->firstWhere('id', ClaimTypeDto::TYPE_PRICE_CHANGE)->statusNames,
            'merchants' => $merchantService->newQuery()->addFields(MerchantDto::entity(), 'id', 'legal_name')->merchants(),
            'iPager' => $pager,
            'iCurrentPage' => (int) $request->get('page', 1),
            'iFilter' => $this->getFilter(),
        ]);
    }

    public function page(Request $request, ClaimService $claimService, UserService $userService): JsonResponse
    {
        $this->requestInitiator->canView(BlockDto::ADMIN_BLOCK_SETTINGS);
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
     * @return mixed
     */
    public function detail(int $id, ClaimService $claimService, UserService $userService)
    {
        /** Проверка разрешения */
        $this->requestInitiator->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        $query = $claimService->newQuery()->setFilter('id', $id);
        /** @var PriceChangeClaimDto $claim */
        $claim = $this->loadClaims($query, $userService, true)->first();

        if (!$claim) {
            throw new NotFoundHttpException();
        }

        /** @var Collection|ClaimTypeDto[] $claimTypes */
        $claimTypes = $claimService->newQuery()
            ->include('statusNames')
            ->setFilter('type', ClaimTypeDto::TYPE_PRICE_CHANGE)
            ->claimTypes();

        return $this->render('Claim/PriceChange/Detail', [
            'iClaim' => $claim,
            'claimStatuses' => $claimTypes->firstWhere('id', ClaimTypeDto::TYPE_PRICE_CHANGE)->statusNames,
        ]);
    }

    /**
     * @return array
     */
    protected function getFilter(): array
    {
        return Validator::make(
            request('filter') ??
            [
                'status' => [
                    PriceChangeClaimDto::STATUS_NEW,
                    PriceChangeClaimDto::STATUS_WORK,
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

    protected function validateStatus(): In
    {
        return Rule::in([
            PriceChangeClaimDto::STATUS_NEW,
            PriceChangeClaimDto::STATUS_WORK,
            PriceChangeClaimDto::STATUS_DONE,
        ]);
    }

    /**
     * Изменить статус заявки
     */
    public function changeStatus(
        int $id,
        Request $request,
        ClaimService $claimService,
        UserService $userService
    ): JsonResponse {
        /** Проверка разрешения */
        $this->requestInitiator->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $result = 'ok';
        $claim = [];
        $error = '';
        $systemError = '';
        try {
            $data = $this->validate($request, [
                'status' => $this->validateStatus(),
            ]);
            $claim = new PriceChangeClaimDto($data);
            $claimService->updateClaim($id, $claim);

            $query = $claimService->newQuery()->setFilter('id', $id);
            /** @var PriceChangeClaimDto $claim */
            $claim = $this->loadClaims($query, $userService)->first();
        } catch (\Throwable $e) {
            $result = 'fail';
            if ($request->get('status') == PriceChangeClaimDto::STATUS_DONE) {
                $error = 'Не все запросы на изменение цены в заявке обработаны';
            }
            $systemError = $e->getMessage();
        }

        return response()->json(['result' => $result, 'claimStatus' => $claim->status, 'error' => $error, 'systemErrors' => $systemError]);
    }

    /**
     * Изменить цену
     */
    public function changePrice(
        int $id,
        Request $request,
        ClaimService $claimService,
        UserService $userService
    ): JsonResponse {
        /** Проверка разрешения */
        $this->requestInitiator->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $result = 'ok';
        $claim = [];
        $error = '';
        $systemError = '';
        try {
            $data = $this->validate($request, [
                'action' => Rule::in(['accept', 'reject']),
                'offerId' => 'sometimes|required|integer',
                'comment' => 'sometimes|required|string',
            ]);

            $query = $claimService->newQuery()->setFilter('id', $id);
            /** @var PriceChangeClaimDto $claim */
            $claim = $this->loadClaims($query, $userService)->first();
            $offers = $claim->getOffers();
            //Формируем массив с измененными ценами
            $newPrices = collect();
            foreach ($offers as &$offer) {
                if (
                    ((isset($data['offerId']) && $data['offerId'] == $offer['offerId']) ||
                        !isset($data['offerId'])) &&
                    !isset($offer['status'])
                ) {
                    $offer['status'] = $data['action'];
                    $offer['updated_at'] = now()->format('H:i:s Y-m-d');
                    $offer['comment'] = $data['comment'] ?? '';

                    if ($data['action'] == 'accept') {
                        $offerPriceDto = new OfferPriceDto();
                        $offerPriceDto->offer_id = $offer['offerId'];
                        $offerPriceDto->price = $offer['newPrice'];

                        $newPrices->push($offerPriceDto);
                    }
                }
            }
            //Сохраняем новые цены
            if ($newPrices->isNotEmpty()) {
                /** @var PriceService $priceService */
                $priceService = resolve(PriceService::class);
                $priceService->setPrices($newPrices);
            }
            //Сохраняем информацию об изменении цен в заявке
            $claim->setOffers($offers);
            $claimService->updateClaim($id, $claim);
            //Получаем всю информацию по заявке
            $query = $claimService->newQuery()->setFilter('id', $id);
            /** @var PriceChangeClaimDto $claim */
            $claim = $this->loadClaims($query, $userService)->first();
        } catch (\Throwable $e) {
            $result = 'fail';
            $systemError = $e->getMessage();
        }

        return response()->json(['result' => $result, 'claimPayload' => $claim->payload, 'error' => $error, 'systemErrors' => $systemError]);
    }

    protected function prepareQuery(Request $request, ClaimService $claimService): DataQuery
    {
        $page = $request->get('page', 1);
        $filters = $this->getFilter();

        $restQuery = $claimService->newQuery()->addSort('created_at', 'desc');
        $restQuery->setFilter('type', ClaimTypeDto::TYPE_PRICE_CHANGE);

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'created_between':
                    $value = array_filter($value);
                    if ($value) {
                        $restQuery->setFilter('created_at', '>=', $value[0]);
                        $restQuery->setFilter('created_at', '<=', $value[1]);
                    }
                    break;
                case 'created_at':
                    if ($value) {
                        $restQuery->setFilter($key, 'like', "{$value}%");
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
     * @return Collection|PriceChangeClaimDto[]
     */
    protected function loadClaims(RestQuery $query, UserService $userService, bool $withProducts = false): Collection
    {
        /** Проверка разрешения */
        $this->requestInitiator->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

        /** @var Collection|PriceChangeClaimDto[] $claims */
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

        /** @var Collection $productsByOffers */
        $productsByOffers = collect();
        if ($withProducts) {
            /** @var ProductService $productService */
            $productService = resolve(ProductService::class);

            $offerIds = [];
            foreach ($claims as $claim) {
                $offerIds = array_column($claim->getOffers(), 'offerId');
            }
            if ($offerIds) {
                $productQuery = $productService
                    ->newQuery()
                    ->addFields(
                        ProductDto::entity(),
                        'id',
                        'name',
                        'vendor_code'
                    );
                $productsByOffers = $productService->productsByOffers($productQuery, $offerIds);
            }
        }

        return $claims->map(function (PriceChangeClaimDto $claim) use ($users, $merchants, $withProducts, $productsByOffers) {
            $claim['merchant'] = $merchants->has($claim->getMerchantId()) ? $merchants[$claim->getMerchantId()] : [];
            $claim['userName'] = $users->has($claim->user_id) ? $users[$claim->user_id]->full_name : '';
            $claim['productsQty'] = count($claim->getOffers());

            if ($withProducts) {
                $isProcessed = true;
                $claimProducts = [];
                foreach ($claim->getOffers() as $offer) {
                    if ($productsByOffers->has($offer['offerId'])) {
                        $claimProducts[$offer['offerId']] = $productsByOffers[$offer['offerId']];
                    }

                    if (!isset($offer['status'])) {
                        $isProcessed = false;
                    }
                }
                $claim['productsByOffers'] = $claimProducts;
                $claim['isProcessed'] = $isProcessed;
            }

            return $claim;
        });
    }
}
