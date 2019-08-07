<?php

return [
    /*
     * Параметры создания и валидации токена авторизации.
     *
     * privateKey и publicKey принимают как путь до файла ключа, так и строку с содержимым ключа.
     * Для того чтобы отличить одно от другого используется проверка на валидность URL, соотвественно путь до файла
     * должен начинаться с протокола file://
     * Пример для linux: file:///var/www/jwt_key/id_rsa
     * Пример для windows: file://C:/projects/ibt/lib-common/jwt_rsa_keys/id_rsa
     *
     * Параметры TTL указываются в минутах.
     */
    'token' => [
        'privateKey' => env('TOKEN_PRIVATE_KEY', 'file:///var/www/jwt_key/id_rsa'),
        'publicKey' => env('TOKEN_PUBLIC_KEY','file:///var/www/jwt_key/id_rsa.pem'),
        'tokenTTL' => 120,
        'refreshTTL' => 60 * 24 * 7 * 2,
        'deadline' => 10
    ],

    /*
     * Код текущего микросервиса.
     * Используется для идентификации собственных прав из общего пула прав назначеннызх на роль.
     * Пример: 'auth', 'pim', 'oms'
     */
    'currentServiceCode' => 'admin',

    /*
     * Способ получения ролей для проверки прав.
     * Варианты:
     *      rest - для получения прав от микросервиса авторизации
     * Если указать другое значние, то необходимо самостоятельно зарегистрировать RbacRoleProvider.
     */
    'roleProviderDriver' => 'rest',

    /*
     * Параметры клиента к микросервису авторизации.
     */
    'serviceAuth' => [
        'host' => env('AUTH_SERVICE_HOST', 'http://localhost'),
    ],

    /*
     * Способ получения токена.
     * Варианты:
     *      request - получить из запроса (для rest-cервисов)
     *      store - использовать сохранённый токен (для фронтэнд rest-клиентов)
     * Если указать другое значние, то необходимо самостоятельно зарегистрировать TokenHolder.
     */
    'tokenHolderDriver' => 'store',

    /*
     * Способ сохранения токена для варианта tokenHolderDriver = store
     * Варианты:
     *      session - хранить токен в сессии
     * Если указать другое значние, то необходимо самостоятельно зарегистрировать TokenStore.
     */
    'tokenStoreDriver' => 'session',
];