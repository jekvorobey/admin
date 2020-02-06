<?php

namespace App\Core;

use Illuminate\Support\Collection;

class Helpers
{
    /**
     * Формирует коллекцию вида [[value, text], ...] для создания <select>
     *
     * @param iterable $collections
     * @return Collection
     */
    static public function getSelectOptions(iterable $collections)
    {
        $collections = is_array($collections) ? collect($collections) : $collections;
        return $collections->map(function ($item) {
            return ['value' => $item['id'], 'text' => $item['name']];
        });
    }
}
