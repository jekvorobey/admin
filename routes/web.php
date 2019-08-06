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
            });
        });
    });
});
