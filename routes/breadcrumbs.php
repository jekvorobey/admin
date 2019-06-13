<?php

use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

// Главная
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Главная', route('home'));
});

// Главная > Мерчант
Breadcrumbs::for('merchant', function ($trail) {
    $trail->parent('home');
    $trail->push('Мерчант');
});

// Главная > Мерчант > Карточка мерчанта
Breadcrumbs::for('merchant.card', function ($trail) {
    $trail->parent('merchant');
    $trail->push('Карточка мерчанта', route('merchant.card'));
});

// Главная > Мерчант > Менеджеры
Breadcrumbs::for('merchant.operator_list', function ($trail) {
    $trail->parent('merchant');
    $trail->push('Менеджеры', route('merchant.operator_list'));
});

// Главная > Мерчант > Магазины и склады
Breadcrumbs::for('merchant.store_list', function ($trail) {
    $trail->parent('merchant');
    $trail->push('Магазины и склады', route('merchant.store_list'));
});

// Главная > Логин
Breadcrumbs::for('login', function ($trail) {
    $trail->parent('home');
    $trail->push('Login', route('login'));
});