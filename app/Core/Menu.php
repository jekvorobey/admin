<?php

namespace App\Core;


class Menu
{
    private static function menu()
    {
        return [
            [
                'title' => 'Мерчант',
                'permission' => 'menu/merchant',
                'items' => [
                    [
                        'title' => 'Пункт меню',
                        'route' => route('home'),
                        'permission' => '#merchant-card/view',
                        'isActive'
                    ],
                    [
                        'title' => 'Ещё один',
                        'route' => route('home'),
                        'permission' => '#operator/view'
                    ],
                    [
                        'title' => 'И ещё',
                        'route' => route('home'),
                        'permission' => '#store/view'
                    ],
                ],
            ],
            /*[
                'title' => 'Товары',
                'permission' => 'menu/products',
                'items' => [
                    [
                        'title' => 'Товары',
                        'route' => ['name' => 'model.list', 'params' => ['crud' => 'product']],
                        'permission' =>
                            '#product/view'
                    ],
                ],
            ],
            [
                'title' => 'Заказы',
                'permission' => 'menu/orders',
                'items' => [
                    ['title' => 'Заказы', 'route' => ['name' => 'order.list'], 'permission' => '#order/view']
                ],
            ],
            [
                'title' => 'Маркетинг',
                'permission' => 'menu/marketing',
                'items' => [
                    [
                        'title' => 'Акции',
                        'route' => ['name' => 'model.list', 'params' => ['crud' => 'promotion']],
                        'permission' =>
                            '#promotion/view'
                    ],
                    [
                        'title' => 'Промокоды',
                        'route' => ['name' => 'model.list', 'params' => ['crud' => 'promotional_code']],
                        'permission' =>
                            '#promotional_code/view'
                    ],
                ],
            ],
            [
                'title' => 'Отчёты',
                'permission' => 'menu/reports',
                'items' => [
                    [
                        'title' => 'Сводный отчет по продажам',
                        'route' => ['name' => 'report.sales_summary_report'],
                        'permission' =>
                            '#sales_summary_report/view'
                    ],
                    [
                        'title' => 'Отчет по позициям',
                        'route' => ['name' => 'report.position_report'],
                        'permission' =>
                            '#position_report/view'
                    ],
                    [
                        'title' => 'Отчет по продажам',
                        'route' => ['name' => 'report.sales_report'],
                        'permission' =>
                            '#sales_report/view'
                    ],
                    [
                        'title' => 'Отчет по остаткам',
                        'route' => ['name' => 'report.rests_report'],
                        'permission' =>
                            '#rests_report/view'
                    ],
                    [
                        'title' => 'Отчет по подтвержденным остаткам',
                        'route' => ['name' => 'report.confirmed_rests_report'],
                        'permission' =>
                            '#confirmed_rests_report/view'
                    ],
                    [
                        'title' => 'Отчет по неотгруженным резервам',
                        'route' => ['name' => 'report.unloaded_rests_report'],
                        'permission' =>
                            '#unloaded_rests_report/view'
                    ],
                    [
                        'title' => 'Отчет комиссионера',
                        'route' => ['name' => 'report.commissioner_report'],
                        'permission' =>
                            '#commissioner_report/view'
                    ],
                ],
            ],*/
        ];
    }
    
    public static function getMenuItems()
    {
        $menuItems = static::menu();
        
        foreach ($menuItems as &$group) {
            //todo Добавить проверку прав для группы
            foreach ($group['items'] as &$item) {
                //todo Добавить проверку прав для пункта меню
                if (url()->full() == $item['route']) {
                    $item['active'] = true;
                }
            }
        }
        
        return $menuItems;
    }
}