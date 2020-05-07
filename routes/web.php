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

    Route::prefix('merchant')->namespace('Merchant')->group(function () {
        Route::prefix('list')->group(function () {
            Route::get('registration', 'MerchantListController@registration')->name('merchant.registrationList');
            Route::get('active', 'MerchantListController@active')->name('merchant.activeList');

            Route::get('page', 'MerchantListController@page')->name('merchant.listPage');
            Route::put('status', 'MerchantListController@status')->name('merchant.listPage.changeStatus');

            Route::post('', 'MerchantListController@createMerchant')->name('merchant.create');
            Route::get('user-exists', 'MerchantListController@checkEmailExists')->name('check.emailExists');
        });

        Route::prefix('detail/{id}')->group(function () {
            Route::get('', 'MerchantDetailController@index')->name('merchant.detail');
            Route::post('', 'MerchantDetailController@updateMerchant')->name('merchant.detail.edit');

            Route::namespace('Detail')->group(function () {
                Route::prefix('operators')->group(function () {
                    Route::get('data', 'TabOperatorController@loadData')->name('merchant.detail.operator.data');
                    Route::get('', 'TabOperatorController@load')->name('merchant.detail.operator');
                });
                Route::prefix('main')->group(function () {
                    Route::get('', 'TabMainController@load')->name('merchant.detail.main');

                    Route::post('document', 'TabMainController@createDocument')->name('merchant.detail.main.document.create');
                    Route::delete('document', 'TabMainController@deleteDocument')->name('merchant.detail.main.document.delete');
                });
                Route::prefix('commission')->group(function () {
                    Route::get('', 'TabCommissionController@load')->name('merchant.detail.commission');
                    Route::post('save', 'TabCommissionController@saveCommission')->name('merchant.detail.commission.save');
                    Route::post('remove', 'TabCommissionController@removeCommission')->name('merchant.detail.commission.remove');
                });
                Route::prefix('marketing')->group(function () {
                    Route::prefix('discounts')->group(function () {
                        Route::get('data', 'TabMarketingController@loadDiscountsData')->name('merchant.detail.marketing.discounts.data');
                        Route::get('page', 'TabMarketingController@pageDiscounts')->name('merchant.detail.marketing.discounts.pagination');
                    });
                    Route::prefix('promo-codes')->group(function () {
                        Route::get('data', 'TabMarketingController@loadPromoCodesData')->name('merchant.detail.marketing.promo-codes.data');
                        Route::get('', 'TabMarketingController@loadPromoCodes')->name('merchant.detail.marketing.promo-codes');
                    });
                });
                Route::prefix('order')->group(function () {
                    Route::get('data', 'TabOrderController@loadOrdersData')->name('merchant.detail.order.data');
                    Route::get('page', 'TabOrderController@page')->name('merchant.detail.order.pagination');
                });
                Route::prefix('product')->group(function () {
                    Route::get('data', 'TabProductController@loadProductsData')->name('merchant.detail.product.data');
                    Route::get('page', 'TabProductController@page')->name('merchant.detail.product.pagination');
                });
            });
        });

        Route::prefix('commission')->group(function () {
            Route::get('', 'MerchantCommissionController@index')->name('merchant.commission');
            Route::post('', 'MerchantCommissionController@save')->name('merchant.commission.save');
        });

        Route::prefix('operator')->group(function () {
            Route::prefix('{id}')->group(function () {
                Route::get('', 'MerchantOperatorController@indexEdit')->name('merchant.operator.indexEdit');
                Route::put('update', 'MerchantOperatorController@update')->name('merchant.operator.update');
            });
            Route::get('', 'MerchantOperatorController@indexCreate')->name('merchant.operator.indexCreate');
            Route::post('save', 'MerchantOperatorController@save')->name('merchant.operator.save');
            Route::put('change-roles', 'MerchantOperatorController@changeRoles')->name('merchant.operator.changeRoles');
            Route::delete('', 'MerchantOperatorController@delete')->name('merchant.operator.delete');
        });
    });

    Route::prefix('marketing')->namespace('Marketing')->group(function () {
        Route::prefix('discounts')->group(function () {
            Route::get('/', 'DiscountController@index')->name('discount.list');
            Route::post('/', 'DiscountController@create')->name('discount.save');
            Route::get('/create', 'DiscountController@createPage')->name('discount.create');
            Route::get('page', 'DiscountController@page')->name('discount.pagination');
            Route::put('/', 'DiscountController@status')->name('discount.status');
            Route::delete('/', 'DiscountController@delete')->name('discount.delete');

            Route::prefix('/{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('/edit', 'DiscountController@edit')->name('discount.edit');
                Route::get('', 'DiscountController@detail')->name('discount.detail');
            });

            Route::put('/{id}', 'DiscountController@update')
                ->where(['id' => '[0-9]+'])
                ->name('discount.update');
        });

        Route::prefix('promo-code')->group(function () {
            Route::get('', 'PromoCodeController@index')->name('promo-code.list');
            Route::post('', 'PromoCodeController@create')->name('promo-code.save');
            Route::get('create', 'PromoCodeController@createPage')->name('promo-code.create');
            Route::get('generate', 'PromoCodeController@generate')->name('promo-code.generate');
            Route::get('check', 'PromoCodeController@checkUnique')->name('promo-code.check');
            Route::post('status', 'PromoCodeController@status')->name('promo-code.status');
            Route::delete('delete', 'PromoCodeController@delete')->name('promo-code.delete');
        });

        Route::prefix('bonus')->group(function () {
            Route::get('', 'BonusController@index')->name('bonus.list');
            Route::post('', 'BonusController@create')->name('bonus.save');
            Route::get('create', 'BonusController@createPage')->name('bonus.create');
            Route::post('status', 'BonusController@status')->name('bonus.status');
            Route::delete('delete', 'BonusController@delete')->name('bonus.delete');
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
            Route::get('by-roles', 'UsersController@usersByRoles')->name('user.byRoles');
        });

        Route::prefix('organization-card')->group(function () {
            Route::get('', 'OrganizationCardController@index')->name('settings.organizationCard');
            Route::put('', 'OrganizationCardController@update')->name('settings.organizationCard.update');
        });

        Route::prefix('marketing')->group(function () {
            Route::get('', 'MarketingController@index')->name('settings.marketing');
            Route::put('', 'MarketingController@update')->name('settings.marketing.update');
        });
    });

    Route::prefix('notifications')->group(function () {
        Route::get('', 'NotificationsController@read')->name('notifications.get');
        Route::post('', 'NotificationsController@markAll')->name('notifications.markAll');
    });

    Route::prefix('orders')->namespace('Order')->group(function () {
        Route::get('', 'OrderListController@index')->name('orders.list');
        Route::get('page', 'OrderListController@page')->name('orders.pagination');

        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('', 'OrderDetailController@detail')->name('orders.detail');
            Route::put('changeStatus', 'OrderDetailController@changeStatus')->name('orders.changeStatus');
            Route::put('pay', 'OrderDetailController@pay')->name('orders.pay');
            Route::put('cancel', 'OrderDetailController@cancel')->name('orders.cancel');

            Route::prefix('delivery')->group(function () {
                Route::get('{deliveryId}', 'FlowDeliveryController@detail')->where(['deliveryId' => '[0-9]+'])->name('orders.delivery');
                Route::put('editDelivery', 'FlowDeliveryController@editDelivery')->name('orders.delivery.editDelivery');
                Route::put('editShipment', 'FlowDeliveryController@editShipment')->name('orders.delivery.editShipment');
            });
        });

        Route::prefix('create')->namespace('Create')->group(function () {
            Route::get('', 'OrderCreateController@create')->name('orders.create');

            Route::post('products', 'OrderCreateController@searchProducts')->name('orders.searchProducts');
            Route::post('users', 'OrderCreateController@searchCustomer')->name('orders.searchCustomer');
            Route::post('order', 'OrderCreateController@createOrder')->name('orders.createOrder');
        });

        Route::prefix('cargos')->namespace('Cargo')->group(function () {
            Route::get('/', 'CargoListController@index')->name('cargo.list');
            Route::get('/page', 'CargoListController@page')->name('cargo.pagination');

            Route::prefix('/{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'CargoDetailController@index')->name('cargo.detail');
                Route::put('changeStatus', 'CargoDetailController@changeStatus')->name('cargo.changeStatus');
                Route::put('cancel', 'CargoDetailController@cancel')->name('cargo.cancel');
                Route::get('/unshipped-shipments', 'CargoDetailController@getUnshippedShipments')->name('cargo.unshippedShipments');

                Route::prefix('/shipments')->group(function () {
                    Route::post('addShipmentPackage', 'CargoDetailController@addShipment2Cargo')
                        ->name('cargo.addShipment2Cargo');

                    Route::prefix('/{shipmentId}')->where(['shipmentId' => '[0-9]+'])->group(function () {
                        Route::delete('', 'CargoDetailController@deleteShipmentFromCargo')
                            ->name('cargo.deleteShipmentFromCargo');
                    });
                });

                Route::prefix('courier-call')->group(function () {
                    Route::post('', 'CargoDetailController@createCourierCall')->name('cargo.createCourierCall');
                    Route::put('cancel', 'CargoDetailController@cancelCourierCall')->name('cargo.cancelCourierCall');
                });
            });
        });

        Route::prefix('directories')->namespace('Directory')->group(function () {
            Route::prefix('order-statuses')->group(function () {
                Route::get('', 'OrderStatusListController@index')->name('orderStatuses.list');
                Route::get('page', 'OrderStatusListController@page')->name('orderStatuses.pagination');
            });
        });
    });

    Route::prefix('offers')->namespace('Product')->group(function () {
        Route::get('', 'OfferListController@index')->name('offers.list');
        Route::get('page', 'OfferListController@page')->name('offers.listPage');
        Route::put('change-status', 'OfferListController@changeSaleStatus')->name('offers.change.saleStatus');
        Route::delete('', 'OfferListController@deleteOffers')->name('offers.delete');
        Route::prefix('{id}')->group(function () {
            Route::post('props', 'ProductDetailController@saveOfferProps')->name('offers.saveOfferProps');
        });
    });

    Route::prefix('products')->namespace('Product')->group(function () {
        Route::get('', 'ProductListController@index')->name('products.list');
        Route::get('page', 'ProductListController@page')->name('products.listPage');

        Route::put('approval', 'ProductListController@updateApprovalStatus')->name('products.massApproval');
        Route::put('production', 'ProductListController@updateProductionStatus')->name('products.massProduction');
        Route::put('archive', 'ProductListController@updateArchiveStatus')->name('products.massArchive');

        Route::prefix('{id}')->group(function () {
            Route::get('detailData', 'ProductDetailController@detailData')->name('products.detailData');
            Route::get('', 'ProductDetailController@index')->name('products.detail');
            Route::post('', 'ProductDetailController@saveProduct')->name('products.saveProduct');
            Route::post('props', 'ProductDetailController@saveProps')->name('products.saveProps');
            Route::post('image', 'ProductDetailController@saveImage')->name('products.saveImage');
            Route::post('imageDelete', 'ProductDetailController@deleteImage')->name('products.deleteImage');
            Route::put('changeApproveStatus', 'ProductDetailController@changeApproveStatus')->name('products.changeApproveStatus');
            Route::put('reject', 'ProductDetailController@reject')->name('products.reject');

            Route::prefix('tips')->group(function () {
                Route::post('', 'ProductDetailController@addTip')->name('product.addTip');
                Route::post('{tipId}/update', 'ProductDetailController@editTip')->name('product.editTip');
                Route::post('{tipId}/delete', 'ProductDetailController@deleteTip')->name('product.deleteTip');
            });
        });
    });
    
    Route::prefix('brands')->namespace('Product')->group(function () {
        Route::get('', 'BrandController@list')->name('brand.list');
        Route::get('page', 'BrandController@page')->name('brand.listPage');
        Route::post('save', 'BrandController@save')->name('brand.save');
        Route::post('delete', 'BrandController@delete')->name('brand.delete');
    });

    Route::prefix('content')->namespace('Content')->group(function () {
        Route::prefix('product-group')->namespace('ProductGroup')->group(function () {
            Route::get('/', 'ProductGroupListController@indexPage')->name('productGroup.listPage');
            Route::get('/{id}', 'ProductGroupDetailController@updatePage')->where(['id' => '[0-9]+'])->name('productGroup.updatePage');
            Route::get('/create', 'ProductGroupDetailController@createPage')->name('productGroup.createPage');
            Route::get('/page', 'ProductGroupListController@page')->name('productGroups.page');
            Route::put('/{id}', 'ProductGroupDetailController@update')->where(['id' => '[0-9]+'])->name('productGroup.update');
            Route::post('/', 'ProductGroupDetailController@create')->name('productGroup.create');
            Route::delete('/{id}', 'ProductGroupDetailController@delete')->where(['id' => '[0-9]+'])->name('productGroup.delete');
            Route::get('/filter', 'ProductGroupDetailController@getFilters')->name('productGroup.getFilters');
            Route::get('/product', 'ProductGroupDetailController@getProducts')->name('productGroup.getProducts');
        });

        Route::prefix('menu')->namespace('Menu')->group(function () {
            Route::get('/', 'MenuListController@index')->name('menu.list');
            Route::get('/{id}', 'MenuDetailController@index')->where(['id' => '[0-9]+'])->name('menu.detail');
            Route::put('/{id}', 'MenuDetailController@update')->where(['id' => '[0-9]+'])->name('menu.update');
        });

        Route::prefix('banner')->namespace('Banner')->group(function () {
            Route::get('/', 'BannerListController@listPage')->name('banner.listPage');
            Route::get('/{id}', 'BannerDetailController@updatePage')->where(['id' => '[0-9]+'])->name('banner.updatePage');
            Route::get('/create', 'BannerDetailController@createPage')->name('banner.createPage');

            Route::get('/page', 'BannerListController@page')->name('banner.page');
            Route::get('/widgetBanners', 'BannerListController@widgetBanners')->name('banner.widgetBanners');
            Route::get('/productGroupBanners', 'BannerListController@productGroupBanners')->name('banner.productGroupBanners');
            Route::post('/', 'BannerDetailController@create')->where(['id' => '[0-9]+'])->name('banner.create');
            Route::put('/{id}', 'BannerDetailController@update')->where(['id' => '[0-9]+'])->name('banner.update');
            Route::delete('/{id}', 'BannerDetailController@delete')->where(['id' => '[0-9]+'])->name('banner.delete');

            Route::get('/initialDate', 'BannerDetailController@bannerInitialDate')->name('banner.initialData');
        });

        Route::prefix('landing')->namespace('Landing')->group(function () {
            Route::get('/', 'LandingListController@listPage')->name('landing.listPage');
            Route::get('/{id}', 'LandingDetailController@updatePage')->where(['id' => '[0-9]+'])->name('landing.updatePage');
            Route::get('/create', 'LandingDetailController@createPage')->name('landing.createPage');

            Route::get('/page', 'LandingListController@page')->name('landing.page');
            Route::post('/', 'LandingDetailController@create')->name('landing.create');
            Route::put('/{id}', 'LandingDetailController@update')->where(['id' => '[0-9]+'])->name('landing.update');
            Route::delete('/{id}', 'LandingDetailController@delete')->where(['id' => '[0-9]+'])->name('landing.delete');
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

            Route::get('/pickup-times', 'MerchantStoreController@pickupTime')
                ->name('merchantStore.pickupTime');
            Route::put('/pickup-times', 'MerchantStoreController@savePickupTime')
                ->name('merchantStore.savePickupTime');
        });
    });

    Route::prefix('logistics')->namespace('Logistics')->group(function () {
        Route::prefix('delivery-services')->namespace('DeliveryService')->group(function () {
            Route::get('/', 'DeliveryServiceListController@index')->name('deliveryService.list');
            Route::get('page', 'DeliveryServiceListController@page')->name('deliveryService.pagination');

            Route::prefix('/{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'DeliveryServiceDetailController@index')->name('deliveryService.detail');
                Route::put('', 'DeliveryServiceDetailController@save')->name('deliveryService.detail.save');

                Route::namespace('Detail')->group(function () {
                    Route::prefix('settings')->group(function () {
                        Route::put('', 'TabSettingsController@save')->name('deliveryService.detail.settings.save');
                    });
                    Route::prefix('limitations')->group(function () {
                        Route::put('', 'TabLimitationsController@save')->name('deliveryService.detail.limitations.save');
                    });
                });
            });
        });

        Route::prefix('delivery-prices')->group(function () {
            Route::get('', 'DeliveryPriceController@index')->name('deliveryPrice.index');
            Route::put('delivery-price', 'DeliveryPriceController@save')->name('deliveryPrice.save');
        });

        Route::prefix('delivery-kpi')->group(function () {
            Route::get('', 'DeliveryKpiController@index')->name('deliveryKpi.index');

            Route::prefix('main')->group(function () {
                Route::get('', 'DeliveryKpiController@getMain')->name('deliveryKpi.main.get');
                Route::put('', 'DeliveryKpiController@setMain')->name('deliveryKpi.main.set');
            });

            Route::prefix('ct')->group(function () {
                Route::get('', 'DeliveryKpiController@getCt')->name('deliveryKpi.ct.get');
                Route::put('', 'DeliveryKpiController@setCt')->name('deliveryKpi.ct.set');
            });

            Route::prefix('ppt')->group(function () {
                Route::get('', 'DeliveryKpiController@getPpt')->name('deliveryKpi.ppt.get');
                Route::put('', 'DeliveryKpiController@setPpt')->name('deliveryKpi.ppt.set');
            });

            Route::prefix('pct')->group(function () {
                Route::get('', 'DeliveryKpiController@getPct')->name('deliveryKpi.pct.get');
                Route::put('', 'DeliveryKpiController@setPct')->name('deliveryKpi.pct.set');
            });
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
            Route::post('send', 'ChatsController@send')->name('communications.chats.send');
            Route::post('create', 'ChatsController@create')->name('communications.chats.create');
            Route::post('update', 'ChatsController@update')->name('communications.chats.update');
            Route::get('broadcast', 'ChatsController@broadcast')->name('communications.chats.broadcast');
        });
    });

    Route::namespace('Customers')->group(function () {
        Route::get('professionals', 'CustomerListController@listProfessional')->name('professional.list');
        Route::get('referral-partners', 'CustomerListController@listReferralPartner')->name('referralPartner.list');

        Route::prefix('customers')->group(function () {
            Route::post('', 'CustomerListController@create')->name('customers.create');

            Route::get('filter', 'CustomerListController@filter')->name('customers.filter');

            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'CustomerDetailController@detail')->name('customers.detail');
                Route::put('', 'CustomerDetailController@save')->name('customers.detail.save');
                Route::put('referral', 'CustomerDetailController@referral')->name('customers.detail.referral');
                Route::put('professional', 'CustomerDetailController@professional')->name('customers.detail.professional');
                Route::put('portfolios', 'CustomerDetailController@putPortfolios')->name('customers.detail.portfolio.save');

                Route::namespace('Detail')->group(function () {
                    Route::prefix('main')->group(function () {
                        Route::get('', 'TabMainController@load')->name('customers.detail.main');
                        Route::delete('certificate/{certificate_id}', 'TabMainController@deleteCertificate')->name('customers.detail.main.certificate.delete');
                        Route::post('certificate/{file_id}', 'TabMainController@createCertificate')->name('customers.detail.main.certificate.create');
                    });
                    Route::prefix('preference')->group(function () {
                        Route::get('', 'TabPreferenceController@load')->name('customers.detail.preference');
                        Route::post('favorite/{product_id}', 'TabPreferenceController@addFavoriteItem')->name('customers.detail.preference.favorite.add');
                        Route::delete('favorite/{product_id}', 'TabPreferenceController@deleteFavoriteItem')->name('customers.detail.preference.favorite.delete');
                        Route::prefix('{type}')->group(function () {
                            Route::put('brands', 'TabPreferenceController@putBrands')->name('customers.detail.preference.brand.save');
                            Route::put('categories', 'TabPreferenceController@putCategories')->name('customers.detail.preference.category.save');
                        });
                    });
                    Route::prefix('promo-product')->group(function () {
                        Route::get('', 'TabPromoProductController@load')->name('customers.detail.promoProduct');
                        Route::put('', 'TabPromoProductController@save')->name('customers.detail.promoProduct.save');
                        Route::get('export', 'TabPromoProductController@export')->name('customers.detail.promoProduct.export');
                    });
                    Route::prefix('promo-page')->group(function () {
                        Route::get('', 'TabPromoPageController@load')->name('customers.detail.promoPage');
                        Route::post('', 'TabPromoPageController@add')->name('customers.detail.promoPage.add');
                        Route::delete('', 'TabPromoPageController@delete')->name('customers.detail.promoPage.delete');
                    });
                    Route::prefix('order-referrer')->group(function () {
                        Route::get('', 'TabOrderReferrerController@load')->name('customers.detail.orderReferrer');
                        Route::get('excel', 'TabOrderReferrerController@export')->name('customers.detail.orderReferrer.export');
                        Route::delete('{history_id}', 'TabOrderReferrerController@delete')->name('customers.detail.orderReferrer.delete');
                    });
                    Route::prefix('billing')->group(function () {
                        Route::get('', 'TabBillingController@load')->name('customers.detail.billing');
                        Route::post('correct', 'TabBillingController@correct')->name('customers.detail.billing.correct');
                    });
                    Route::prefix('documents')->group(function () {
                        Route::get('', 'TabDocumentController@load')->name('customers.detail.document');
                        Route::get('export', 'TabDocumentController@export')->name('customers.detail.document.export');
                        Route::prefix('{document_id}')->group(function () {
                            Route::post('send', 'TabDocumentController@sendEmail')->name('customers.detail.document.send');
                            Route::delete('delete', 'TabDocumentController@deleteDocument')->name('customers.detail.document.delete');
                        });

                        Route::post('', 'TabDocumentController@createDocument')->name('customers.detail.document.create');
                    });
                    Route::prefix('bonuses')->group(function () {
                        Route::get('', 'TabBonusController@load')->name('customers.detail.bonuses');
                        Route::post('', 'TabBonusController@add')->name('customers.detail.bonus.add');
                    });
                    Route::get('order', 'TabOrderController@load')->name('customers.detail.order');
                    Route::get('promocodes', 'TabPromocodesController@load')->name('customers.detail.promocodes');
                });

            });

            Route::prefix('activities')->group(function () {
                Route::get('', 'ActivitiesController@list')->name('customers.activities');
                Route::post('', 'ActivitiesController@save')->name('customers.activities.save');
            });

        });
    });

    Route::prefix('referral')->namespace('Referral')->group(function () {
        Route::prefix('levels')->group(function () {
            Route::get('', 'LevelsController@list')->name('referral.levels');
            Route::prefix('{level_id}')->group(function () {
                Route::post('', 'LevelsController@detail')->name('referral.levels.detail');
                Route::put('', 'LevelsController@putLevel')->name('referral.levels.save');
                Route::prefix('commission')->group(function () {
                    Route::put('', 'LevelsController@putCommission')->name('referral.levels.commission.save');
                    Route::delete('', 'LevelsController@removeCommission')->name('referral.levels.commission.remove');
                });
                Route::prefix('special-commission')->group(function () {
                    Route::put('', 'LevelsController@putSpecialCommission')->name('referral.levels.special-commission.save');
                    Route::delete('', 'LevelsController@removeSpecialCommission')->name('referral.levels.special-commission.remove');
                });
            });
        });

        Route::prefix('options')->group(function () {
            Route::get('', 'OptionsController@index')->name('referral.options');
            Route::put('', 'OptionsController@save')->name('referral.options.save');
        });
    });

    Route::prefix('public-events')->namespace('PublicEvent')->group(function () {
        Route::get('is-code-unique', 'PublicEventDetailController@isCodeUnique')->name('public-event.isCodeUnique');
        Route::post('save', 'PublicEventDetailController@save')->name('public-event.save');
        
        Route::prefix('{event_id}')->group(function () {
            Route::post('add-organizer-by-id', 'PublicEventDetailController@addOrganizerById')->name('public-event.addOrganizerById');
            Route::post('add-organizer-by-value', 'PublicEventDetailController@addOrganizerByValue')->name('public-event.addOrganizerByValue');

            Route::post('save-media', 'PublicEventDetailController@saveMedia')->name('public-event.saveMedia');
            Route::post('delete-media', 'PublicEventDetailController@deleteMedia')->name('public-event.deleteMedia');

            Route::prefix('sprints')->group(function () {
                Route::get('', 'PublicEventDetailController@getSprints')->name('public-event.getSprints');
                Route::post('', 'PublicEventDetailController@createSprint')->name('public-event.createSprint');
                Route::post('delete', 'PublicEventDetailController@deleteSprint')->name('public-event.deleteSprint');
            });

            Route::get('load', 'PublicEventDetailController@load')->name('public-event.load');
            Route::get('', 'PublicEventDetailController@index')->name('public-event.detail');
        });
        Route::get('', 'PublicEventListController@page')->name('public-event.list');
    });
    
    Route::prefix('organizers')->namespace('PublicEvent')->group(function () {
        Route::get('available', 'PublicEventDetailController@availableOrganizers')->name('public-event.availableOrganizers');
    });

});
