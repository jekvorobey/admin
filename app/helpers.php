<?php


/**
 * Входная точка в класс работы со сборщиком.
 * frontend()->img()...
 *
 * @return \App\Core\Frontend
 */
function frontend()
{
    static $frontend = null;
    
    if ($frontend === null) {
        $frontend = new \App\Core\Frontend();
    }
    
    return $frontend;
}

/**
 *  Выбор нужной формы слова.
 *
 *   $forms = [
 *     "банан",
 *     "банана",
 *     "бананов"
 *   ];
 *
 *   plural_form(1, $forms); //банан
 *   plural_form(2, $forms); //банана
 *   plural_form(5, $forms); //бананов
 *
 * @param $n
 * @param $forms
 * @return string
 */
function plural_form($n, $forms)
{
    return $n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
}

function is_null_or_empty($str){
    return (!isset($str) || trim($str) === '');
}

function to_boolean($str) {
    return !($str === 'false' || $str === '0' || !$str);
}