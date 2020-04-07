<?php

namespace App\Core;

use Greensight\CommonMsa\Dto\UserDto;
use Illuminate\Support\Collection;

class Helpers
{
    /**
     * Формирует коллекцию вида [[value, text], ...] для создания <select>
     *
     * @param iterable $collections
     * @return Collection
     */
    public static function getSelectOptions(iterable $collections)
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
    public static function getOptionRoles($showDefault = true)
    {
        $roles = $showDefault ? [['value' => null, 'text' => 'Все']] : [];
        $roles[] = ['value' => UserDto::SHOWCASE__PROFESSIONAL, 'text' => 'Профессионал'];
        $roles[] = ['value' => UserDto::SHOWCASE__REFERRAL_PARTNER, 'text' => 'Реферальный партнер'];
        return $roles;
    }
}
