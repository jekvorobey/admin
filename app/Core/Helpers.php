<?php

namespace App\Core;

use Greensight\CommonMsa\Dto\RoleDto;
use Illuminate\Support\Collection;

class Helpers
{
    /**
     * Формирует коллекцию вида [[value, text], ...] для создания <select>
     */
    public static function getSelectOptions(iterable $collections): Collection
    {
        $collections = is_array($collections) ? collect($collections) : $collections;

        return $collections->map(function ($item) {
            return ['value' => $item['id'], 'text' => $item['name']];
        });
    }

    /**
     * @param bool $showDefault
     * @return array
     */
    public static function getOptionRoles($showDefault = true): array
    {
        $roles = $showDefault ? [['value' => null, 'text' => 'Все']] : [];
        $roles[] = ['value' => RoleDto::ROLE_SHOWCASE_PROFESSIONAL, 'text' => 'Профессионал'];
        $roles[] = ['value' => RoleDto::ROLE_SHOWCASE_REFERRAL_PARTNER, 'text' => 'Реферальный партнер'];

        return $roles;
    }

    /**
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     */
    public static function getPriceFormat($price, $decimals = 2, $decPoint = '.', $thousandsSep = ' '): string
    {
        return number_format($price, $decimals, $decPoint, $thousandsSep);
    }
}
