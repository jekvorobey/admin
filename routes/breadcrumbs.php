<?php

use DaveJamesMiller\Breadcrumbs\BreadcrumbsGenerator;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

// Главная
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Главная', route('home'));
});

// Главная > Авторизация
Breadcrumbs::for('login', function ($trail) {
    $trail->parent('home');
    $trail->push('Авторизация', route('login'));
});

//================= Заявки =============================================================================================
// Главная > Заявки
Breadcrumbs::for('claims', function (BreadcrumbsGenerator $trail) {
    $trail->parent('home');
    $trail->push('Заявки');
});

// Главная > Заявки > Съёмка
Breadcrumbs::for('claims.photo', function (BreadcrumbsGenerator $trail) {
    $trail->parent('claims');
    $trail->push('Съёмка', route('photoClaims.list'));
});
// Главная > Заявки > Съёмка > Заявка
Breadcrumbs::for('claims.photo.detail', function (BreadcrumbsGenerator $trail) {
    $trail->parent('claims.photo');
    $trail->push('Заявка');
});

// Главная > Заявки > Доставка
Breadcrumbs::for('claims.delivery', function (BreadcrumbsGenerator $trail) {
    $trail->parent('claims');
    $trail->push('Доставка', route('photoClaims.list'));
});
// Главная > Заявки > Доставка > Заявка
Breadcrumbs::for('claims.delivery.detail', function (BreadcrumbsGenerator $trail) {
    $trail->parent('claims.delivery');
    $trail->push('Заявка');
});