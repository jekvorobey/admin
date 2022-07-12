<?php

namespace App\Core;

use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Services\RoleService\RoleService;
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

    public static function getRoles(int|array $fronts = []): array
    {
        /** @var RoleService $roleService */
        $roleService = resolve(RoleService::class);
        $roleQuery = $roleService->newQuery();
        if ($fronts) {
            $roleQuery->setFilter('front', $fronts);
        }

        return $roleService->roles($roleQuery)->pluck('name', 'id')->all();
    }

    public static function getOptionRoles(bool $showDefault = true, $front = Front::FRONT_SHOWCASE): array
    {
        $roles = collect(self::getRoles($front));

        $options = $roles->map(function (string $name, int $id) {
            return [
                'value' => $id,
                'text' => $name,
            ];
        });

        if ($showDefault) {
            $options->prepend([
                'value' => null,
                'text' => 'Все',
            ]);
        }

        return $options->values()->all();
    }

    public static function getPriceFormat(
        $price,
        int $decimals = 2,
        string $decPoint = '.',
        string $thousandsSep = ' '
    ): string {
        return number_format($price, $decimals, $decPoint, $thousandsSep);
    }
}
