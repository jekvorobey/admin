<?php

namespace App\Core;

use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Message\Services\CommunicationService\CommunicationService;

class Menu
{
    private static string $activeUrl;

    private static function menu(): array
    {
        return [
            [
                'id' => BlockDto::ADMIN_BLOCK_PRODUCTS,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_PRODUCTS)->name,
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
                        'title' => 'Товарные группы',
                        'route' => route('variantGroups.list'),
                    ],
                    [
                        'title' => 'Справочники',
                        'items' => [
                            [
                                'title' => 'Бренды',
                                'route' => route('brand.list'),
                            ],
                            [
                                'title' => 'Категории',
                                'route' => route('categories.list'),
                            ],
                            [
                                'title' => 'Товарные атрибуты',
                                'route' => route('products.properties.list'),
                            ],
                        ],
                    ],
//                    [
//                        'title' => 'Подарки',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Сертификаты',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Справочники',
//                        'items' => [
//                            [
//                                'title' => 'Бренды',
//                                'route' => '#',
//                            ],
//                            [
//                                'title' => 'Товарные категории',
//                                'route' => '#',
//                            ],
//                            [
//                                'title' => 'Спец.признаки',
//                                'route' => '#',
//                            ],
//                        ]
//                    ],
                ],
            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_ORDERS,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_ORDERS)->name,
                'items' => [
                    [
                        'title' => 'Список заказов',
                        'route' => route('orders.list'),
                    ],
//                    [
//                        'title' => 'Проблемные заказы',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Возвраты и отмены',
//                        'route' => '#',
//                    ],
                    [
                        'title' => 'Грузы',
                        'route' => route('cargo.list'),
                    ],
                    /* @link https://redmine.greensight.ru/issues/57841 [
                     * 'title' => 'Отправления',
                     * 'route' => route('shipment.list')
                     * ],*/
                    [
                        'title' => 'Справочники',
                        'items' => [
                            [
                                'title' => 'Статусы заказов',
                                'route' => route('orderStatuses.list'),
                            ],
                            [
                                'title' => 'Причины отмены',
                                'route' => route('orderReturnReasons.list'),
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_CLAIMS,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_CLAIMS)->name,
                'items' => [
                    ['title' => 'Проверка товаров', 'route' => route('productCheckClaims.list')],
                    ['title' => 'Производство контента', 'route' => route('contentClaims.list')],
                    ['title' => 'Изменение цен', 'route' => route('priceChangeClaims.list')],
                ],
            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_CONTENT,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_CONTENT)->name,
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
                        'title' => 'Управление контактами и соц. сетями',
                        'route' => route('contacts.list'),
                    ],
                    [
                        'title' => 'Управление категориями',
                        'route' => route('frequentCategories.list'),
                    ],
//                    [
//                        'title' => 'Шаблоны страниц',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Контент-разделы',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'SEO',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Отзывы',
//                        'route' => '#',
//                    ],
                    [
                        'title' => 'Редиректы',
                        'route' => route('redirect.list'),
                    ],
                    [
                        'title' => 'Подборки товаров',
                        'route' => route('productGroup.listPage'),
                    ],
                    [
                        'title' => 'Баннеры',
                        'route' => route('banner.listPage'),
                    ],
                    [
                        'title' => 'Товарные шильдики',
                        'route' => route('productBadges.list'),
                    ],
                    [
                        'title' => 'Поисковые запросы',
                        'route' => route('searchRequests.list'),
                    ],
                    [
                        'title' => 'Поисковые синонимы',
                        'route' => route('searchSynonyms.list'),
                    ],
                    [
                        'title' => 'Популярные бренды',
                        'route' => route('popularBrands.list'),
                    ],
                    [
                        'title' => 'Популярные товары',
                        'route' => route('popularProducts.list'),
                    ],
                ],
            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_LOGISTICS,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_LOGISTICS)->name,
                'items' => [
                    [
                        'title' => 'Логистические операторы',
                        'route' => route('deliveryService.list'),
                    ],
//                    [
//                        'title' => 'Статусы доставки',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Таблица соотвествий статусов',
//                        'route' => '#',
//                    ],
                    [
                        'title' => 'Стоимость доставки по регионам',
                        'route' => route('deliveryPrice.index'),
                    ],
                    [
                        'title' => 'Планировщик времени статусов',
                        'route' => route('deliveryKpi.index'),
                    ],
                ],
            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_STORES,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_STORES)->name,
                'items' => [
//                    [
//                        'title' => 'Склад консолидации',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Склад упаковки',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Склад подарков',
//                        'route' => '#',
//                    ],
                    [
                        'title' => 'Склады мерчантов',
                        'route' => route('merchantStore.list'),
                    ],
                ],
            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_CLIENTS,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_CLIENTS)->name,
                'items' => [
                    [
                        'title' => 'Клиентская база',
                        'route' => route('professional.list'),
                    ],
//                    [
//                        'title' => 'Сегментация',
//                        'route' => '#',
//                    ],
                    [
                        'title' => 'Справочники',
                        'items' => [
                            [
                                'title' => 'Виды деятельности',
                                'route' => route('customers.activities'),
                            ],
                            [
                                'title' => 'Вайтлист',
                                'route' => route('customers.whitelist'),
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_REFERRALS,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_REFERRALS)->name,
                'items' => [
                    [
                        'title' => 'Список реферальных партнеров',
                        'route' => route('referralPartner.list'),
                    ],
                    [
                        'title' => 'Товары для продвижения',
                        'route' => route('referral.promo-products.list'),
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
                'id' => BlockDto::ADMIN_BLOCK_MERCHANTS,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_MERCHANTS)->name,
                'items' => [
                    [
                        'title' => 'Заявки на регистрацию',
                        'route' => route('merchant.registrationList'),
                    ],
                    [
                        'title' => 'Список мерчантов',
                        'route' => route('merchant.activeList'),
                    ],
                    [
                        'title' => 'Комиссия',
                        'route' => route('merchant.commission'),
                    ],
                    [
                        'title' => 'Взаиморасчёты',
                        'route' => route('merchant.settlements'),
                    ],
                ],
            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_MARKETING,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_MARKETING)->name,
                'items' => [
//                    [
//                        'title' => 'Баннеры',
//                        'route' => '#',
//                    ],
                    [
                        'title' => 'Промокоды',
                        'route' => route('promo-code.list'),
                    ],
                    [
                        'title' => 'Скидки',
                        'route' => route('discount.list'),
                    ],
                    [
                        'title' => 'Бандлы',
                        'route' => route('bundle.list'),
                    ],
                    [
                        'title' => 'Бонусы',
                        'route' => route('bonus.list'),
                    ],
                    [
                        'title' => 'Подарочные сертификаты',
                        'route' => route('certificate.index'),
                    ],
                ],
            ],
//            [
//                'title' => 'Отчёты',
//                'items' => [
//                    [
//                        'title' => 'Отчёты по заказам',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Отчёты по маркетингу',
//                        'items' => [
//                            [
//                                'title' => 'Отчёты по промокодам',
//                                'route' => '#',
//                            ],
//                            [
//                                'title' => 'Отчёты по скидкам',
//                                'route' => '#',
//                            ],
//                        ]
//                    ],
//                    [
//                        'title' => 'Отчёты по мерчантам',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Отчёты по подарочным сертификатам',
//                        'route' => '#',
//                    ],
//                    [
//                        'title' => 'Отчёты по клиентам',
//                        'route' => '#',
//                    ],
//                ],
//            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_COMMUNICATIONS,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_COMMUNICATIONS)->name,
                'items' => [
                    [
                        'title' => 'Непрочитанные сообщения' . static::unreadCount(),
                        'route' => route('communications.chats.unread'),
                    ],
                    [
                        'title' => 'Сервисные уведомления',
                        'route' => route('communications.service-notification.list'),
                    ],
                    [
                        'title' => 'Массовая рассылка',
                        'route' => route('communications.chats.broadcast'),
                    ],
                    [
                        'title' => 'Неперсонифицированные чаты',
                        'route' => route('communications.chats.unlinkMessengerChats'),
                    ],
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
                ],
            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS)->name,
                'items' => [
                    [
                        'title' => 'Каталог мастер-классов',
                        'route' => route('public-event.list'),
                    ],
                    [
                        'title' => 'Справочники',
                        'items' => [
                            [
                                'title' => 'Организаторы',
                                'route' => route('public-event.organizers.list'),
                            ],
                            [
                                'title' => 'Площадки',
                                'route' => route('public-event.places.list'),
                            ],
                            [
                                'title' => 'Спикеры',
                                'route' => route('public-event.speakers.list'),
                            ],
                            [
                                'title' => 'Типы событий',
                                'route' => route('public-event.types.list'),
                            ],
                            [
                                'title' => 'Направления',
                                'route' => route('public-event.specialties.list'),
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => BlockDto::ADMIN_BLOCK_SETTINGS,
                'title' => BlockDto::blockById(BlockDto::ADMIN_BLOCK_SETTINGS)->name,
                'active' => false,
                'items' => [
                    [
                        'title' => 'Способы оплаты',
                        'route' => route('settings.paymentMethods'),
                    ],
                    [
                        'title' => 'Пользователи и права',
                        'route' => route('settings.userList'),
                    ],
                    [
                        'title' => 'Роли',
                        'route' => route('settings.rolesList'),
                    ],
                    [
                        'title' => 'Карточка организации',
                        'route' => route('settings.organizationCard'),
                    ],
                    [
                        'title' => 'Маркетинговые инструменты',
                        'route' => route('settings.marketing'),
                    ],
                ],
            ],
        ];
    }

    public static function setActiveUrl($url)
    {
        self::$activeUrl = $url;
    }

    private static function detectActive(&$items, $activeUrl): bool
    {
        foreach ($items as &$item) {
            if (isset($item['route']) && $activeUrl == $item['route']) {
                $item['active'] = true;
                return true;
            }
            if (isset($item['items']) && self::detectActive($item['items'], $activeUrl)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Выдать только доступные пользователю блоки.
     * @param $items
     * @return array
     */
    private static function filterAllowMenuItems($items): array
    {
        $allowMenu = [];
        $allowBlockIds = resolve(RequestInitiator::class)->blockPermissions()->pluck('block_id')->toArray();
        if (empty($allowBlockIds)) {
            return [];
        }
        foreach ($items as $item) {
            if (!isset($item['id']) || in_array($item['id'], $allowBlockIds)) {
                $allowMenu[] = $item;
            }
        }

        return $allowMenu;
    }

    public static function getMenuItems(): array
    {
        $menuItems = static::menu();

        self::detectActive($menuItems, self::$activeUrl ?? url()->current());

//        foreach ($menuItems as &$group) {
//            //todo Добавить проверку прав для группы
//            foreach ($group['items'] as &$item) {
//
//                //todo Добавить проверку прав для пункта меню
//                if (isset($item['route']) && url()->current() == $item['route']) {
//                    $item['active'] = true;
//                }
//            }
//        }

        return self::filterAllowMenuItems($menuItems);
    }

    /**
     * Получить кол-во непрочитанных сообщений для отображения у пункта меню
     */
    private static function unreadCount(): string
    {
        $communicationService = resolve(CommunicationService::class);

        return ' (' . $communicationService->unreadCount() . ')';
    }
}
