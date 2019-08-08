<?php

use Illuminate\Support\Facades\Route;

Route::prefix('login')->group(function () {
    Route::get('', 'MainController@login')->name('page.login');
    Route::post('', 'MainController@loginAjax')->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/', 'MainController@home')->name('home');
    Route::post('upload', 'MainController@uploadFile')->name('uploadFile');
    Route::post('logout', 'MainController@logoutAjax')->name('logout');

    Route::prefix('claims')->namespace('Claim')->group(function () {
        Route::prefix('photo')->group(function () {
            Route::get('', 'PhotoClaimController@index')->name('photoClaims.list');
            Route::get('page', 'PhotoClaimController@page')->name('photoClaims.pagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'PhotoClaimController@detail')->name('photoClaims.detail');
                Route::post('', 'PhotoClaimController@update')->name('photoClaims.update');
            });
        });

        Route::prefix('delivery')->group(function () {
            Route::get('', 'DeliveryClaimController@index')->name('deliveryClaims.list');
            Route::post('', 'DeliveryClaimController@create')->name('deliveryClaims.create');
            Route::get('page', 'DeliveryClaimController@page')->name('deliveryClaims.pagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'DeliveryClaimController@detail')->name('deliveryClaims.detail');
                Route::post('', 'DeliveryClaimController@update')->name('deliveryClaims.update');
            });
        });
    });
});
