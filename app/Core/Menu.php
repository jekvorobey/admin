<?php

namespace App\Core;


class Menu
{
    private static function menu()
    {
        $fake_route = 0;
        return [
            [
                'title' => 'Поток закзов',
                'items' => [
                    [
                        'title' => 'Новые заказы',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Список заказов',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Заказы на консолидацию',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Проблемные заказы',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Возвраты и отмены',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Каталог (товары)',
                'items' => [
                    [
                        'title' => 'Товары',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Справочники свойств',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Товарные свойства',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Категории товаров',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Логистика',
                'items' => [
                    [
                        'title' => 'Статусы доставки',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Таблица соотвествий статусов',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Склады',
                'items' => [
                    [
                        'title' => 'Склад консолидации',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Склад ответсвенного хранения',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Управление контентом',
                'items' => [
                    [
                        'title' => 'Страницы маркетплейса',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'SEO',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Маркетинг',
                'items' => [
                    [
                        'title' => 'Брошенные корзины',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Скидки и промокоды',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Подарочные сертификаты',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Бандлы',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Мастер-классы',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Магазин подарков',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Мерчанты',
                'items' => [
                    [
                        'title' => 'Заявки на регистрацию',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Список мерчантов',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Обратная связь с мерчантами',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Взаиморасчёты с мерчантами',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Амбассадоры',
                'items' => [
                    [
                        'title' => 'Заявки на регистрацию',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Список амбассадоров',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Обратная связь с амбассадорами',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Взаиморасчёты с амбассадорами',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Покупатели и профессионалы',
                'items' => [
                    [
                        'title' => 'Список покупателей',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Сегменты покупателей',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Анализ поведения покупателей',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Рассылки',
                'items' => [
                    [
                        'title' => 'Создание рассылки',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Анализ рассылок',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Настройки',
                'items' => [
                    [
                        'title' => 'Профиль',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Пользователи и права',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Настройки интеграций',
                        'route' => $fake_route++,//route('home'),
                    ],
                ],
            ],
            [
                'title' => 'Отчёты',
                'items' => [
                    [
                        'title' => 'Стандартные отчёты',
                        'route' => $fake_route++,//route('home'),
                    ],
                    [
                        'title' => 'Свободный отчёт',
                        'route' => $fake_route++,//route('home'),
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
                if (url()->full() == $item['route']) {
                    $item['active'] = true;
                }
            }
        }
        
        return $menuItems;
    }
}