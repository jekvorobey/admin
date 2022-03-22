<?php

return [
    /*
     * Код текущего микросервиса.
     * Используется для идентификации собственных прав из общего пула прав назначеннызх на роль.
     * Пример: 'auth', 'pim', 'oms'
     */
    'currentServiceCode' => 'admin',

    /*
     * Способ получения токена.
     * Варианты:
     *      request - получить из запроса (для rest-cервисов)
     *      store - использовать сохранённый токен (для фронтэнд rest-клиентов)
     * Если указать другое значние, то необходимо самостоятельно зарегистрировать TokenHolder.
     */
    'tokenHolderDriver' => 'store',

    'showcaseHost' => env('SHOWCASE_HOST', 'https://ibt.ru'),

    /**
     * Адрес микросервиса интеграции. Пока нужен только для показа в интеграции мерчанта.
     */
    'integration1CHost' => env('I_1C_SERVICE_HOST', 'https://i-1c.ibt.ru'),
    'integrationMoyskladHost' => env('MOYSKLAD_SERVICE_HOST', 'https://online.moysklad.ru/api/remap/1.2'),
];
