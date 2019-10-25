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
    Route::get('available-managers', 'Merchant\\MerchantDetailController@availableManagers')->name('managers.all');

    Route::prefix('merchant')->namespace('Merchant')->group(function () {
        Route::prefix('registration')->group(function () {
            Route::get('page', 'RegistrationRequestController@page')->name('merchant.registrationListPage');
            Route::get('', 'RegistrationRequestController@index')->name('merchant.registrationList');
            Route::prefix('{id}')->group(function () {
                Route::get('', 'MerchantDetailController@index')->name('merchant.registrationDetail');
                Route::post('', 'MerchantDetailController@updateMerchant')->name('merchant.edit');
            });
        });
    });

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

    Route::prefix('settings')->namespace('Settings')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('page', 'UsersController@page')->name('settings.userListPagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'UsersController@detail')->name('settings.userDetail');
                Route::post('addRole', 'UsersController@addRole')->name('user.addRole');
                Route::post('deleteRole', 'UsersController@deleteRole')->name('user.deleteRole');
            });
            Route::get('', 'UsersController@index')->name('settings.userList');
            Route::post('', 'UsersController@saveUser')->name('settings.createUser');
        });
    });

    Route::prefix('orders')->namespace('Orders')->group(function () {

        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::post('userComment', 'FlowController@userComment')->name('orders.userComment');
        });

        Route::prefix('flow')->group(function () {
            Route::get('page', 'FlowController@page')->name('orders.FlowPagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'FlowController@detail')->name('orders.flowDetail');

            });
            Route::get('', 'FlowController@index')->name('orders.flowList');
        });
    });


    Route::prefix('offers')->namespace('Product')->group(function () {
        Route::get('', 'OfferListController@index')->name('offers.list');
        Route::get('page', 'OfferListController@page')->name('offers.listPage');
    });

});
