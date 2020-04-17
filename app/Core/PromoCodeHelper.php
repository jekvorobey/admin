<?php

namespace App\Core;

use \Illuminate\Support\Collection;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Marketing\Services\DiscountService\DiscountService;
use Greensight\Marketing\Services\PromoCodeService\PromoCodeService;
use Greensight\Marketing\Dto\PromoCode\PromoCodeInDto;
use Greensight\Marketing\Dto\PromoCode\PromoCodeOutDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Dto\CustomerDto;


class PromoCodeHelper
{
    /**
     * Получение списка промокодов c типом скидка
     *
     * @param DiscountService $discountService
     * @param PromoCodeService $promoCodeService
     * @param int $merchantId
     * @return Collection
     */
    public static function getDiscountPromoCodes(
        DiscountService $discountService,
        PromoCodeService $promoCodeService,
        int $merchantId
    ) {
        $discountPromoCodes = collect();

        $discounts = $discountService->discounts([
            'filter' => [
                'relateToMerchant' => $merchantId,
            ]
        ]);

        if ($discounts->isNotEmpty()) {
            $promoCodeInDto = (new PromoCodeInDto())
                ->discount($discounts->pluck('id')->all())
                ->merchant([$merchantId, 0]);
            $discountPromoCodes = $promoCodeService->promoCodes($promoCodeInDto);
        }

        return $discountPromoCodes;
    }

    /**
     * Получение списка промокодов
     *
     * @param PromoCodeService $promoCodeService
     * @param int $merchantId
     * @return Collection
     */
    public static function getPromoCodes(
        PromoCodeService $promoCodeService,
        int $merchantId = null
    ) {
        $promoCodeInDto = (new PromoCodeInDto());
        if ($merchantId) {
            $promoCodeInDto = $promoCodeInDto->merchant($merchantId);
        }
        return $promoCodeService->promoCodes($promoCodeInDto);
    }

    /**
     * Получение списка реферальных партнеров в списке промокодов
     *
     * @param Collection $promoCodes
     * @param CustomerService $customerService
     * @return Collection
     */
    public static function getReferrals(
        Collection $referralIds,
        CustomerService $customerService
    ) {
        $referrals = collect();
        if ($referralIds->isNotEmpty()) {
            $referrals = $customerService
                ->customers(
                    (new RestQuery())
                        ->setFilter('id', $referralIds->all())
                )
                ->keyBy('id');
        }
        return $referrals;
    }

    /**
     * Получение списка пользователей в списке промокодов
     *
     * @param Collection $promoCodes
     * @param UserService $userService
     * @param Collection $referrals
     * @return Collection
     */
    public static function getUsers(
        Collection $userIds,
        UserService $userService,
        Collection $referrals = null
    ) {
        if ($referrals) {
            $userIds = $userIds->merge($referrals->pluck('user_id'));
        }

        $users = collect();
        if ($userIds->isNotEmpty()) {
            $users = $userService
                ->users(
                    (new RestQuery())
                        ->setFilter('id', $userIds->all())
                )
                ->keyBy('id');
        }

        return $users;
    }

    /**
     * Форматирование списка промокодов для вывода в таблицу
     *
     * @param Collection $promoCodes
     * @param Collection $referrals
     * @param Collection $users
     * @return Collection
     */
    public static function formatPromoCodes(
        Collection $promoCodes,
        Collection $referrals,
        Collection $users
    ) {
        return $promoCodes
            ->sortByDesc('created_at')
            ->map(function (PromoCodeOutDto $promoCode) use ($users, $referrals) {
                $promoCode['validityPeriod'] = $promoCode->validityPeriod();

                /** @var UserDto $creatorUser */
                $creatorUser = $users->get($promoCode->creator_id);
                $promoCode['creator'] = $creatorUser ? [
                    'id' => $creatorUser->id,
                    'title' => $creatorUser->getTitle(),
                ]: null;

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
                        ]: null;
                    }
                }

                return $promoCode;
            })->values();
    }

    /**
     * Получение списка пользователей-создателей промокодов
     *
     * @param Collection $creatorIds
     * @param Collection $users
     * @return Collection
     */
    public static function getCreators(Collection $creatorIds, Collection $users)
    {
        return $creatorIds->map(function ($creatorId) use ($users) {
            /** @var UserDto $user */
            $user = $users->get($creatorId);
            return [
                'id' => $creatorId,
                'title' => $user ? $user->getTitle() : '-',
            ];
        })->sortBy('title')
            ->values();
    }

    /**
     * Получение списка реферальных партнеров, которые есть в списке промокодов
     *
     * @param Collection $creatorIds
     * @param Collection $users
     * @param Collection $referrals
     * @return Collection
     */
    public static function getOwners(Collection $referralIds, Collection $users, Collection $referrals)
    {
        return $referralIds->map(function ($owner_id) use ($users, $referrals) {
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
        })->filter()
            ->sortBy('title')
            ->values();
    }
}
