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
                        'title' => 'Карточка мерчанта',
                        'route' => route('merchant.card'),
                        'permission' => '#merchant-card/view'
                    ],
                    [
                        'title' => 'Менеджеры',
                        'route' => route('merchant.operator_list'),
                        'permission' => '#operator/view'
                    ],
                    [
                        'title' => 'Магазины и склады',
                        'route' => route('merchant.store_list'),
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
        
        foreach ($menuItems as $group) {
            //todo Добавить проверку прав для группы
            foreach ($group as $item) {
                //todo Добавить проверку прав для пункта меню
            }
        }
        
        return $menuItems;
    }
}