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

    Route::prefix('marketing')->namespace('Marketing')->group(function () {
        Route::prefix('discounts')->group(function () {
            Route::get('/', 'DiscountController@index')->name('discount.list');
            Route::get('/create', 'DiscountController@createPage')->name('discount.create');
            Route::post('/', 'DiscountController@create')->name('discount.save');
        });
    });

    Route::prefix('claims')->namespace('Claim')->group(function () {
        Route::prefix('product-check')->group(function () {
            Route::get('', 'ProductCheckClaimController@index')->name('productCheckClaims.list');
            Route::get('page', 'ProductCheckClaimController@page')->name('productCheckClaims.pagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'ProductCheckClaimController@detail')->name('productCheckClaims.detail');
                Route::put('changeStatus', 'ProductCheckClaimController@changeStatus')->name('productCheckClaims.changeStatus');
            });
        });

        Route::prefix('content')->group(function () {
            Route::post('', 'ContentClaimController@create')->name('contentClaims.create');
            Route::get('', 'ContentClaimController@index')->name('contentClaims.list');
            Route::get('page', 'ContentClaimController@page')->name('contentClaims.pagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'ContentClaimController@detail')->name('contentClaims.detail');
            });
        });

        Route::prefix('price-change')->group(function () {
            Route::get('', 'PriceChangeClaimController@index')->name('priceChangeClaims.list');
            Route::get('page', 'PriceChangeClaimController@page')->name('priceChangeClaims.pagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'PriceChangeClaimController@detail')->name('priceChangeClaims.detail');
                Route::put('changeStatus', 'PriceChangeClaimController@changeStatus')->name('priceChangeClaims.changeStatus');
                Route::put('changePrice', 'PriceChangeClaimController@changePrice')->name('priceChangeClaims.changePrice');
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

    Route::prefix('notifications')->group(function () {
        Route::get('', 'NotificationsController@read')->name('notifications.get');
        Route::post('', 'NotificationsController@markAll')->name('notifications.markAll');
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

                Route::prefix('delivery')->group(function () {
                    Route::get('{deliveryId}', 'FlowDeliveryController@detail')->where(['deliveryId' => '[0-9]+'])->name('orders.delivery');
                    Route::put('editDelivery', 'FlowDeliveryController@editDelivery')->name('orders.delivery.editDelivery');
                    Route::put('editShipment', 'FlowDeliveryController@editShipment')->name('orders.delivery.editShipment');
                });
            });

        });

        Route::prefix('create')->namespace('Create')->group(function () {
            Route::get('', 'OrderCreateController@create')->name('orders.create');

            Route::post('products', 'OrderCreateController@searchProducts')->name('orders.searchProducts');
            Route::post('users', 'OrderCreateController@searchUsers')->name('orders.searchUsers');
            Route::post('order', 'OrderCreateController@createOrder')->name('orders.createOrder');
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
            Route::put('changeApproveStatus', 'ProductDetailController@changeApproveStatus')->name('products.changeApproveStatus');
            Route::put('reject', 'ProductDetailController@reject')->name('products.reject');
        });
    });

    Route::prefix('content')->namespace('Content')->group(function () {
        Route::prefix('product-group')->group(function () {
            Route::get('/', 'ProductGroupListController@index')
                ->name('productGroup.list');
            Route::get('/{id}', 'ProductGroupDetailController@index')
                ->where(['id' => '[0-9]+'])
                ->name('productGroup.detail');
            Route::put('/{id}', 'ProductGroupDetailController@update')
                ->where(['id' => '[0-9]+'])
                ->name('productGroup.update');

            Route::get('/filter', 'ProductGroupDetailController@getFilters')
                ->name('productGroup.getFilters');

            Route::get('/product', 'ProductGroupDetailController@getProducts')
                ->name('productGroup.getProducts');
        });

        Route::prefix('menu')->namespace('Menu')->group(function () {
            Route::get('/', 'MenuListController@index')
                ->name('menu.list');
            Route::get('/{id}', 'MenuDetailController@index')
                ->where(['id' => '[0-9]+'])
                ->name('menu.detail');
            Route::put('/{id}', 'MenuDetailController@update')
                ->where(['id' => '[0-9]+'])
                ->name('menu.update');
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
            Route::get('directories', 'ChatsController@directories')->name('communications.chats.directories');
            Route::get('filter', 'ChatsController@filter')->name('communications.chats.filter');
            Route::put('read', 'ChatsController@read')->name('communications.chats.read');
            Route::post('send', 'ChatsController@send')->name('communications.chats.send');
        });
    });

    Route::prefix('customers')->namespace('Customers')->name('customers.')->group(function () {
        Route::get('', 'CustomerListController@list')->name('list');
        Route::get('filter', 'CustomerListController@filter')->name('filter');

        Route::prefix('{id}')->group(function () {
            Route::get('', 'CustomerDetailController@detail')->name('detail');
            Route::put('', 'CustomerDetailController@save')->name('detail.save');

            Route::get('main', 'CustomerDetailController@infoMain')->name('detail.main');
            Route::get('subscribe', 'CustomerDetailController@infoSubscribe')->name('detail.subscribe');
            Route::get('preference', 'CustomerDetailController@infoPreference')->name('detail.preference');
            Route::get('order', 'CustomerDetailController@infoOrder')->name('detail.order');
            Route::get('log', 'CustomerDetailController@infoLog')->name('detail.log');
        });
    });

});
