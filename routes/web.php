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

        Route::prefix('flow')->namespace('Flow')->group(function () {
            Route::get('', 'FlowListController@index')->name('orders.flowList');
            Route::get('page', 'FlowListController@page')->name('orders.FlowPagination');

            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'FlowDetailController@detail')->name('orders.flowDetail');
                Route::get('delivery/{deliveryId}', 'FlowDeliveryController@detail')->where(['deliveryId' => '[0-9]+'])->name('orders.flowDelivery');
            });

        });

        Route::prefix('create')->namespace('Create')->group(function () {
            Route::get('', 'OrderCreateController@create')->name('orders.create');

            Route::post('products', 'OrderCreateController@searchProducts')->name('orders.searchProducts');
            Route::post('users', 'OrderCreateController@searchUsers')->name('orders.searchUsers');
        });

        Route::prefix('cargo')->namespace('Cargo')->group(function () {
            Route::get('/', 'CargoListController@index')->name('cargo.list');
            Route::get('/page', 'CargoListController@page')->name('cargo.pagination');

            Route::prefix('/{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'CargoDetailController@index')->name('cargo.detail');
                Route::put('changeStatus', 'CargoDetailController@changeStatus')->name('cargo.changeStatus');
                Route::get('/unshipped-shipments', 'CargoDetailController@getUnshippedShipments')->name('cargo.unshippedShipments');

                Route::prefix('/shipments')->group(function () {
                    Route::post('addShipmentPackage', 'CargoDetailController@addShipment2Cargo')
                        ->name('cargo.addShipment2Cargo');

                    Route::prefix('/{shipmentId}')->where(['shipmentId' => '[0-9]+'])->group(function () {
                        Route::delete('', 'CargoDetailController@deleteShipmentFromCargo')
                            ->name('cargo.deleteShipmentFromCargo');
                    });
                });
            });
        });
    });

    Route::prefix('offers')->namespace('Product')->group(function () {
        Route::get('', 'OfferListController@index')->name('offers.list');
        Route::get('page', 'OfferListController@page')->name('offers.listPage');
    });

    Route::prefix('products')->namespace('Product')->group(function () {
        Route::get('', 'ProductListController@index')->name('products.list');
        Route::get('page', 'ProductListController@page')->name('products.listPage');
        Route::prefix('{id}')->group(function () {
            Route::get('detailData', 'ProductDetailController@detailData')->name('products.detailData');
            Route::get('', 'ProductDetailController@index')->name('products.detail');
            Route::post('', 'ProductDetailController@saveProduct')->name('products.saveProduct');
            Route::post('props', 'ProductDetailController@saveProps')->name('products.saveProps');
            Route::post('image', 'ProductDetailController@saveImage')->name('products.saveImage');
            Route::post('imageDelete', 'ProductDetailController@deleteImage')->name('products.deleteImage');
        });
    });

    Route::prefix('content')->namespace('Content')->group(function () {
        Route::get('product-group-pages', 'ProductGroupPageListController@index')->name('productGroupPages.list');
        Route::prefix('{id}')->group(function () {
            Route::get('', 'ProductGroupPageDetailController@index')->name('productGroupPage.detail');
        });
    });

    Route::prefix('stores')->namespace('Store')->group(function () {
        Route::prefix('merchant-stores')->group(function () {
            Route::get('/', 'MerchantStoreController@index')->name('merchantStore.list');
            Route::get('/create', 'MerchantStoreController@createPage')->name('merchantStore.add');

            Route::get('/{id}', 'MerchantStoreController@detailPage')
                ->where(['id' => '[0-9]+'])
                ->name('merchantStore.edit');

            Route::get('page', 'MerchantStoreController@page')->name('merchantStore.pagination');

            Route::post('', 'MerchantStoreController@create')
                ->where(['id' => '[0-9]+'])
                ->name('merchantStore.create');
            Route::put('/{id}', 'MerchantStoreController@update')
                ->where(['id' => '[0-9]+'])
                ->name('merchantStore.update');
            Route::delete('/{id}', 'MerchantStoreController@delete')
                ->where(['id' => '[0-9]+'])
                ->name('merchantStore.delete');

            Route::prefix('working')->group(function () {
                Route::put('/{id}', 'MerchantStoreController@updateWorking')
                    ->where(['id' => '[0-9]+'])
                    ->name('merchantStore.updateWorking');
            });

            Route::prefix('contacts')->group(function () {
                Route::post('/{id}', 'MerchantStoreController@createContact')
                    ->name('merchantStore.createContact');
                Route::put('/{id}', 'MerchantStoreController@updateContact')
                    ->where(['id' => '[0-9]+'])
                    ->name('merchantStore.updateContact');
                Route::delete('/{id}', 'MerchantStoreController@deleteContact')
                    ->where(['id' => '[0-9]+'])
                    ->name('merchantStore.deleteContact');
            });

            Route::put('/pickup-times', 'MerchantStoreController@savePickupTime')
                ->name('merchantStore.savePickupTime');
        });
    });

    Route::prefix('logistics')->namespace('Logistics')->group(function () {
        Route::prefix('delivery-prices')->group(function () {
            Route::get('/', 'DeliveryPriceController@index')->name('deliveryPrice.index');
            Route::put('/delivery-price', 'DeliveryPriceController@save')->name('deliveryPrice.save');
        });
    });

    Route::prefix('communications')->namespace('Communications')->group(function () {
        Route::prefix('statuses')->group(function () {
            Route::get('', 'StatusController@index')->name('communications.statuses.list');
            Route::post('', 'StatusController@save')->name('communications.statuses.save');
            Route::delete('{id}', 'StatusController@delete')->name('communications.statuses.delete');
        });
        Route::prefix('themes')->group(function () {
            Route::get('', 'ThemeController@index')->name('communications.themes.list');
            Route::post('', 'ThemeController@save')->name('communications.themes.save');
            Route::delete('{id}', 'ThemeController@delete')->name('communications.themes.delete');
        });
        Route::prefix('types')->group(function () {
            Route::get('', 'TypeController@index')->name('communications.types.list');
            Route::post('', 'TypeController@save')->name('communications.types.save');
            Route::delete('{id}', 'TypeController@delete')->name('communications.types.delete');
        });

        Route::prefix('chats')->group(function () {
            Route::get('unread', 'ChatsController@unread')->name('communications.chats.unread');
            Route::get('unread/count', 'ChatsController@unreadCount')->name('communications.chats.unread.count');
            Route::get('filter', 'ChatsController@filter')->name('communications.chats.filter');
            Route::put('read', 'ChatsController@read')->name('communications.chats.read');
        });
    });

});
