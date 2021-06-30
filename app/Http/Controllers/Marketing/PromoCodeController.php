<?php

namespace App\Http\Controllers\Marketing;

use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Marketing\Builder\PromoCode\PromoCodeBuilder;
use Greensight\Marketing\Dto\Bonus\BonusInDto;
use Greensight\Marketing\Dto\Discount\DiscountDto;
use Greensight\Marketing\Dto\Discount\DiscountInDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeInDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeOutDto;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Greensight\Marketing\Services\PromoCodeService\PromoCodeService;
use Greensight\Marketing\Services\BonusService\BonusService;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;

/**
 * Class PromoCodeController
 * @package App\Http\Controllers\Merchant
 */
class PromoCodeController extends Controller
{
    /**
     * Список промокодов
     * @param MerchantService $merchantService
     * @return mixed
     */
    public function index(
        PromoCodeService $promoCodeService,
        UserService $userService,
        CustomerService $customerService,
        CommunicationService $communicationService
    ) {
        $this->title = 'Промокоды';
        $promoCodeInDto = new PromoCodeInDto();
        $promoCodes = $promoCodeService->promoCodes($promoCodeInDto);

        $promoRequestsCount = $communicationService->unreadCount([], false, 'Запрос промокода');

        $merchantsIds = $promoCodes->pluck('merchant_id')->unique()->all();
        $merchants = $this->getMerchants($merchantsIds)->values();

        $users = collect();
        $referrals = collect();
        $creatorIds = $promoCodes->pluck('creator_id')->unique();
        $userIds = collect($creatorIds);

        $referralIds = $promoCodes->pluck('owner_id')->filter()->unique();
        if ($referralIds->isNotEmpty()) {
            $referrals = $customerService->customers((new RestQuery())->setFilter('id', $referralIds->all()))->keyBy('id');
            $userIds = $userIds->merge($referrals->pluck('user_id'));
        }

        if ($userIds->isNotEmpty()) {
            $users = $userService->users((new RestQuery())->setFilter('id', $userIds->all()))->keyBy('id');
        }

        $promoCodes = $promoCodes
            ->sortByDesc('created_at')
            ->map(function (PromoCodeOutDto $promoCode) use ($users, $referrals) {
                $promoCode['validityPeriod'] = $promoCode->validityPeriod();

                /** @var UserDto $creatorUser */
                $creatorUser = $users->get($promoCode->creator_id);
                $promoCode['creator'] = $creatorUser ? [
                    'id' => $creatorUser->id,
                    'title' => $creatorUser->getTitle(),
                ] : null;

                $promoCode['owner'] = null;
                if ($promoCode->owner_id) {
                    /** @var CustomerDto $referral */
                    $referral = $referrals->get($promoCode->owner_id);
                    if ($referral) {
                        /** @var UserDto $ownerUser */
                        $ownerUser = $users->get($referral->user_id);
                        $promoCode['owner'] = $ownerUser ? [
                            'id' => $promoCode->owner_id,
                            'title' => $ownerUser->getTitle(),
                        ] : null;
                    }
                }

                return $promoCode;
            })->values();

        return $this->render('Marketing/PromoCode/List', [
            'iPromoCodes' => $promoCodes,
            'promoRequestsCount' => $promoRequestsCount,
            'Merchants' => $merchants,
            'statuses' => PromoCodeOutDto::allStatuses(),
            'types' => PromoCodeOutDto::allTypes(),
            'creators' => $creatorIds->map(function ($creatorId) use ($users) {
                /** @var UserDto $user */
                $user = $users->get($creatorId);
                return [
                    'id' => $creatorId,
                    'title' => $user ? $user->getTitle() : '-',
                ];
            })->sortBy('title')->values(),
            'owners' => $referralIds->map(function ($owner_id) use ($users, $referrals) {
                /** @var CustomerDto $referral */
                $referral = $referrals->get($owner_id);

                /** @var UserDto $user */
                $user = null;
                if ($referral) {
                    $user = $users->get($referral->user_id);
                }

                if (!$user) {
                    return false;
                }

                return [
                    'id' => $owner_id,
                    'title' => $user ? $user->getTitle() : '-',
                ];
            })->filter()->sortBy('title')->values(),
        ]);
    }

    public function status(PromoCodeService $promoCodeService)
    {
        $ids = request('ids');
        $status = request('status');

        foreach ($ids as $id) {
            $promoCodeService->update($id, (new PromoCodeBuilder())->status($status));
        }

        return response('', 204);
    }

    public function delete(PromoCodeService $promoCodeService)
    {
        $ids = request('ids');

        foreach ($ids as $id) {
            $promoCodeService->delete($id);
        }

        return response('', 204);
    }

    /**
     * Страница для создания промокода
     *
     * @return mixed
     */
    public function createPage(Request $request)
    {
        $this->title = 'Создание промокода';
        $promoCodeTypes = PromoCodeOutDto::allTypes();
        $promoCodeStatuses = PromoCodeOutDto::allStatuses();

        $discountService = resolve(DiscountService::class);
        $merchantService = resolve(MerchantService::class);
        $bonusesService = resolve(BonusService::class);
        $bonuses = $bonusesService->bonuses(new BonusInDto());

        $params = (new DiscountInDto())->toQuery();
        $discounts = $discountService->discounts($params)
            ->sortByDesc('created_at')
            ->map(function (DiscountDto $item) {
                return [
                    'merchant_id' => $item->merchant_id,
                    'value' => $item->id,
                    'text' => "{$item->name} ({$item->validityPeriod()})",
                ];
            })
            ->values();

        $merchants = $merchantService->merchants()->map(function (MerchantDto $merchant) {
            return ['id' => $merchant->id, 'name' => $merchant->legal_name];
        });

        return $this->render('Marketing/PromoCode/Create', [
            'iTypesForMerchant' => PromoCodeOutDto::availableTypesForMerchant(),
            'iTypes' => Helpers::getSelectOptions($promoCodeTypes),
            'iStatuses' => Helpers::getSelectOptions($promoCodeStatuses),
            'iDiscounts' => $discounts,
            'gifts' => [],
            'bonuses' => Helpers::getSelectOptions($bonuses),
            'merchants' => Helpers::getSelectOptions($merchants),
            'iRoles' => Helpers::getOptionRoles(false),
            'iSegments' => [['text' => 'A', 'value' => 1], ['text' => 'B', 'value' => 2]], // todo
            'returnUrl' => $request->get('returnUrl', route('promo-code.list')),
            'referral' => $request->get('referral'),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, PromoCodeService $promoCodeService, RequestInitiator $requestInitiator)
    {
        $data = $request->validate([
            'owner_id' => 'numeric|nullable',
            'merchant_id' => 'numeric|nullable',
            'name' => 'string|required',
            'code' => 'string|required',
            'counter' => 'numeric|nullable',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'status' => 'numeric|required',
            'type' => 'numeric|required',
            'discount_id' => 'numeric|nullable',
            'gift_id' => 'numeric|nullable',
            'bonus_id' => 'numeric|nullable',
            'conditions' => 'array|nullable',
            'conditions.segments' => 'array|nullable',
            'conditions.segments.*' => 'numeric|nullable',
            'conditions.roles' => 'array|nullable',
            'conditions.roles.*' => 'numeric|nullable',
            'conditions.customers' => 'array|nullable',
            'conditions.customers.*' => 'numeric|nullable',
        ]);

        $data['creator_id'] = $requestInitiator->userId();
        try {
            $data['start_date'] = $data['start_date']
                ? Carbon::createFromFormat('Y-m-d', $data['start_date'])
                : null;

            $data['end_date'] = $data['end_date']
                ? Carbon::createFromFormat('Y-m-d', $data['end_date'])
                : null;
        } catch (\Throwable $ex) {
            //
        }

        $builder = new PromoCodeBuilder($data);
        $result = $promoCodeService->create($builder);
        return response()->json(['status' => $result ? 'ok' : 'fail']);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(PromoCodeService $promoCodeService)
    {
        $r = $promoCodeService->generate();
        return response()->json($r);
    }

    /**
     * Проверка промокода на уникальность
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUnique(PromoCodeService $promoCodeService)
    {
        $data = $this->validate(request(), [
            'code' => 'required|string|max:32',
        ]);

        $status = $promoCodeService->checkUnique($data['code']);

        return response()->json([
            'status' => $status,
        ], 200);
    }
}
