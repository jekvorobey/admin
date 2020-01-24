<?php

namespace App\Core;


class Menu
{
    private static function menu()
    {
        return [
            [
                'title' => 'Товары',
                'items' => [
                    [
                        'title' => 'Каталог товаров',
                        'route' => route('products.list'),
                    ],
                    [
                        'title' => 'Предложения мерчантов',
                        'route' => route('offers.list'),
                    ],
                    [
                        'title' => 'Подарки',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Сертификаты',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Справочники',
                        'items' => [
                            [
                                'title' => 'Бренды',
                                'route' => '#',
                            ],
                            [
                                'title' => 'Товарные категории',
                                'route' => '#',
                            ],
                            [
                                'title' => 'Спец.признаки',
                                'route' => '#',
                            ],
                        ]
                    ],
                ],
            ],
            [
                'title' => 'Заказы',
                'items' => [
                    [
                        'title' => 'Создать заказ',
                        'route' => route('orders.create'),
                    ],
                    [
                        'title' => 'Поток сборки',
                        'route' => route('orders.flowList'),
                    ],
                    [
                        'title' => 'Проблемные заказы',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Возвраты и отмены',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Грузы',
                        'route' => route('cargo.list')
                    ],
                ],
            ],
            [
                'title' => 'Контент',
                'items' => [
                    [
                        'title' => 'Меню сайта',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Управление страницами',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Шаблоны страниц',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Контент-разделы',
                        'route' => '#',
                    ],
                    [
                        'title' => 'SEO',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Отзывы',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Страницы с подборками товаров',
                        'route' => route('productGroupPages.list'),
                    ],
                ]
            ],
            [
                'title' => 'Логистика',
                'items' => [
                    [
                        'title' => 'Статусы доставки',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Таблица соотвествий статусов',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Стоимость доставки по регионам',
                        'route' => route('deliveryPrice.index'),
                    ],
                ],
            ],
            [
                'title' => 'Склады',
                'items' => [
                    [
                        'title' => 'Склад консолидации',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Склад упаковки',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Склад подарков',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Склады мерчантов',
                        'route' => route('merchantStore.list'),
                    ],
                ],
            ],
            [
                'title' => 'Клиенты',
                'items' => [
                    [
                        'title' => 'Клиентская база',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Сегментация',
                        'route' => '#',
                    ],
                ],
            ],
            [
                'title' => 'Амбассадоры',
                'items' => [
                    [
                        'title' => 'Заявки на регистрацию',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Список амбассадоров',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Вознаграждения',
                        'route' => '#',
                    ],
                ],
            ],
            [
                'title' => 'Мерчанты',
                'items' => [
                    [
                        'title' => 'Заявки на регистрацию',
                        'route' => route('merchant.registrationList'),
                    ],
                    [
                        'title' => 'Список мерчантов',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Взаиморасчёты',
                        'route' => '#',
                    ],
                ],
            ],
            [
                'title' => 'Мастер-классы',
                'route' => '#',
                'items' => []
            ],
            [
                'title' => 'Маркетинг',
                'items' => [
                    [
                        'title' => 'Баннеры',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Промокоды',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Скидки',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Бандлы',
                        'route' => '#',
                    ],
                ],
            ],
            [
                'title' => 'Отчёты',
                'items' => [
                    [
                        'title' => 'Отчёты по заказам',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Отчёты по маркетингу',
                        'items' => [
                            [
                                'title' => 'Отчёты по промокодам',
                                'route' => '#',
                            ],
                            [
                                'title' => 'Отчёты по скидкам',
                                'route' => '#',
                            ],
                        ]
                    ],
                    [
                        'title' => 'Отчёты по мерчантам',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Отчёты по подарочным сертификатам',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Отчёты по клиентам',
                        'route' => '#',
                    ],
                ],
            ],
            [
                'title' => 'Настройки',
                'items' => [
                    [
                        'title' => 'Пользователи и права',
                        'route' => route('settings.userList'),
                    ],
                ],
            ],
        ];
    }

    public static function getMenuItems()
    {
        $menuItems = static::menu();

        foreach ($menuItems as &$group) {
            //todo Добавить проверку прав для группы
            foreach ($group['items'] as &$item) {
                //todo Добавить проверку прав для пункта меню
                if (isset($item['route']) && url()->full() == $item['route']) {
                    $item['active'] = true;
                }
            }
        }

        return $menuItems;
    }
}
