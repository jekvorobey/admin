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
                'title' => 'Заявки',
                'items' => [
                    ['title' => 'Проверка товаров', 'route' => route('productCheckClaims.list')],
                    ['title' => 'Производство контента', 'route' => route('contentClaims.list')],
                    ['title' => 'Изменение цен', 'route' => route('priceChangeClaims.list')],
                ]
            ],
            [
                'title' => 'Контент',
                'items' => [
                    [
                        'title' => 'Меню сайта',
                        'route' => route('menu.list'),
                    ],
                    [
                        'title' => 'Управление страницами',
                        'route' => route('landing.listPage'),
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
                        'title' => 'Подборки товаров',
                        'route' => route('productGroup.listPage'),
                    ],
                    [
                        'title' => 'Баннеры',
                        'route' => route('banner.listPage'),
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
                        'route' => route('customers.list'),
                    ],
                    [
                        'title' => 'Сегментация',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Справочники',
                        'items' => [
                            [
                                'title' => 'Виды деятельности',
                                'route' => route('customers.activities'),
                            ],
                        ]
                    ],
                ],
            ],
            [
                'title' => 'Реферальные партнеры',
                'items' => [
                    [
                        'title' => 'Список реферальных партнеров',
                        'route' => '#',
                    ],
                    [
                        'title' => 'Вознаграждения',
                        'route' => route('referral.levels'),
                    ],
                    [
                        'title' => 'Параметры отображения',
                        'route' => route('referral.options'),
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
                        'route' => route('discount.list'),
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
                'title' => 'Коммуникации',
                'items' => [
                    [
                        'title' => 'Статусы',
                        'route' => route('communications.statuses.list'),
                    ],
                    [
                        'title' => 'Темы',
                        'route' => route('communications.themes.list'),
                    ],
                    [
                        'title' => 'Типы',
                        'route' => route('communications.types.list'),
                    ],
                    [
                        'title' => 'Массовая рассылка',
                        'route' => route('communications.chats.broadcast'),
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
