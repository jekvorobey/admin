<?php

use App\Core\Frontend;
use Carbon\Carbon;

if (!function_exists('frontend')) {
    /**
     * Входная точка в класс работы со сборщиком.
     * frontend()->img()...
     */
    function frontend(): ?Frontend
    {
        static $frontend = null;

        if ($frontend === null) {
            $frontend = new Frontend();
        }

        return $frontend;
    }
}

if (!function_exists('plural_form')) {
    /**
     *  Выбор нужной формы слова.
     *   $forms = [
     *     "банан",
     *     "банана",
     *     "бананов"
     *   ];
     *   plural_form(1, $forms); //банан
     *   plural_form(2, $forms); //банана
     *   plural_form(5, $forms); //бананов
     */
    function plural_form($n, $forms): string
    {
        return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
    }
}

if (!function_exists('to_boolean')) {
    function to_boolean($str): bool
    {
        return !($str === 'false' || $str === '0' || !$str);
    }
}

if (!function_exists('url_showcase')) {
    function url_showcase($path): string
    {
        return join('/', [
            rtrim(config('app.showcase_host'), '/'),
            ltrim($path, '/'),
        ]);
    }
}

if (!function_exists('date2str')) {
    function date2str(?DateTime $date): string
    {
        return $date ? $date->format('d.m.Y') : '';
    }
}

if (!function_exists('date_time2str')) {
    function date_time2str(?DateTime $date): string
    {
        return $date ? $date->format('d.m.Y H:i:s') : '';
    }
}

if (!function_exists('end_of_day_filter')) {
    function end_of_day_filter(string $date): string
    {
        return Carbon::parse($date)->endOfDay()->toDateTimeString();
    }
}
