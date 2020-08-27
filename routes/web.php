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

    Route::prefix('search')->group(function() {
        Route::get('products', 'SearchController@products')->name('search.products');
    });

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
                    Route::get('page', 'TabOperatorController@loadOperators')->name('merchant.detail.operator.pagination');
                });
                Route::prefix('digest')->group(function () {
                    Route::get('', 'TabDigestController@load')->name('merchant.detail.digest');
                    Route::put('comment', 'TabDigestController@comment')->name('merchant.detail.digest.comment');
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
                Route::prefix('store')->group(function () {
                    Route::get('page', 'TabStoreController@page')->name('merchant.detail.store.pagination');
                });
                Route::prefix('billing')->group(function () {
                    Route::get('', 'TabBillingController@load')->name('merchant.detail.billing');
                    Route::put('billing_cycle', 'TabBillingController@billingCycle')->name('merchant.detail.billing.billing_cycle');
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
                Route::get('/orders', 'DiscountController@discountOrdersDetail')->name('discount.orders');
                Route::get('', 'DiscountController@detail')->name('discount.detail');
            });

            Route::put('/{id}', 'DiscountController@update')
                ->where(['id' => '[0-9]+'])
                ->name('discount.update');
        });

        Route::prefix('bundles')->group(function () {
            Route::get('/', 'BundleController@index')->name('bundle.list');
            Route::get('page', 'BundleController@page')->name('bundle.pagination');
            Route::get('/create', 'BundleController@createPage')->name('bundle.create');
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

            Route::put('productLimit', 'BonusController@changeProductLimit')->name('bonus.changeMPP');
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
            Route::get('', 'ContentClaimController@index')->name('contentClaims.list');
            Route::get('create', 'ContentClaimController@create')->name('contentClaims.create');
            Route::get('page', 'ContentClaimController@page')->name('contentClaims.pagination');
            Route::post('createClaim', 'ContentClaimController@saveClaim')->name('contentClaims.createClaim');
            Route::put('changeStatus', 'ContentClaimController@changeStatuses')->name('contentClaims.changeStatuses');
            Route::delete('', 'ContentClaimController@deleteClaims')->name('contentClaims.deleteClaims');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'ContentClaimController@detail')->name('contentClaims.detail');
                Route::put('', 'ContentClaimController@update')->name('contentClaims.update');
                Route::prefix('documents')->group(function () {
                    Route::get('acceptance-act', 'ContentClaimController@acceptanceAct')->name('contentClaims.documents.acceptanceAct');
                });
            });
            Route::get('products-by-merchant', 'ContentClaimController@loadProductsByMerchantId')->name('contentClaims.productsByMerchant');
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
        Route::prefix('payment-methods')->group(function () {
            Route::get('', 'PaymentMethodsController@list')->name('settings.paymentMethods');
            Route::put('{id}/edit', 'PaymentMethodsController@edit')->name('settings.paymentMethods.edit');
        });
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

        Route::prefix('packages')->group(function () {
            Route::get('', 'PackagesController@list')->name('settings.packages.list');
        });
    });

    Route::prefix('notifications')->group(function () {
        Route::get('', 'NotificationsController@read')->name('notifications.get');
        Route::post('', 'NotificationsController@markAll')->name('notifications.markAll');
    });

    Route::prefix('document-templates')->group(function () {
        Route::get('claim-act', 'DocumentTemplatesController@claimAct')->name('documentTemplates.claimAct');
        Route::get('acceptance-act', 'DocumentTemplatesController@acceptanceAct')->name('documentTemplates.acceptanceAct');
        Route::get('inventory', 'DocumentTemplatesController@inventory')->name('documentTemplates.inventory');
        Route::get('assembling-card', 'DocumentTemplatesController@assemblingCard')->name('documentTemplates.assemblingCard');
    });

    Route::prefix('orders')->namespace('Order')->group(function () {
        Route::get('', 'OrderListController@index')->name('orders.list');
        Route::get('page', 'OrderListController@page')->name('orders.pagination');
        Route::post('byOffers', 'OrderListController@byOffers')->name('orders.byOffers');

        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('', 'OrderDetailController@detail')->name('orders.detail');
            Route::put('changeStatus', 'OrderDetailController@changeStatus')->name('orders.changeStatus');
            Route::put('pay', 'OrderDetailController@pay')->name('orders.pay');
            Route::put('cancel', 'OrderDetailController@cancel')->name('orders.cancel');

            Route::namespace('Detail')->group(function () {
                Route::prefix('main')->group(function () {
                    Route::get('', 'TabMainController@load')->name('orders.detail.main');
                    Route::put('', 'TabMainController@save')->name('orders.detail.main.save');
                });
                Route::prefix('deliveries')->group(function () {
                    Route::prefix('{deliveryId}')->where(['deliveryId' => '[0-9]+'])->group(function () {
                        Route::get('', 'TabDeliveriesController@load')->name('orders.detail.deliveries');
                        Route::put('', 'TabDeliveriesController@save')->name('orders.detail.deliveries.save');
                        Route::put('save-delivery-order', 'TabDeliveriesController@saveDeliveryOrder')->name('orders.detail.deliveries.saveDeliveryOrder');
                        Route::put('cancel-delivery-order', 'TabDeliveriesController@cancelDeliveryOrder')->name('orders.detail.deliveries.cancelDeliveryOrder');
                        Route::put('cancel', 'TabDeliveriesController@cancelDelivery')->name('orders.detail.deliveries.cancel');
                    });
                });
                Route::prefix('shipments')->group(function () {
                    Route::prefix('{shipmentId}')->where(['shipmentId' => '[0-9]+'])->group(function () {
                        Route::put('', 'TabShipmentsController@save')->name('orders.detail.shipments.save');
                        Route::put('change-status', 'TabShipmentsController@changeShipmentStatus')->name('orders.detail.shipments.changeShipmentStatus');
                        Route::put('mark-as-non-problem', 'TabShipmentsController@markAsNonProblemShipment')->name('orders.detail.shipments.markAsNonProblem');
                        Route::get('barcodes', 'TabShipmentsController@barcodes')->name('orders.detail.shipments.barcodes');
                        Route::get('cdek-receipt', 'TabShipmentsController@cdekReceipt')->name('orders.detail.shipments.cdekReceipt');
                        Route::put('cancel', 'TabShipmentsController@cancelShipment')->name('orders.detail.shipments.cancel');

                        Route::prefix('documents')->group(function () {
                            Route::get('acceptance-act', 'TabShipmentsController@acceptanceAct')->name('orders.detail.shipments.documents.acceptanceAct');
                            Route::get('inventory', 'TabShipmentsController@inventory')->name('orders.detail.shipments.documents.inventory');
                            Route::get('assembling-card', 'TabShipmentsController@assemblingCard')->name('orders.detail.shipments.documents.assemblingCard');
                        });

                        Route::prefix('/shipment-packages')->group(function () {
                            Route::post('addShipmentPackage', 'TabShipmentsController@addShipmentPackage')
                                ->name('orders.detail.shipments.addShipmentPackage');

                            Route::prefix('/{shipmentPackageId}')->where(['shipmentPackageId' => '[0-9]+'])->group(function () {
                                Route::delete('', 'TabShipmentsController@deleteShipmentPackage')
                                    ->name('orders.detail.shipments.deleteShipmentPackage');

                                Route::prefix('/items')->group(function () {
                                    Route::post('', 'TabShipmentsController@addShipmentPackageItems')
                                        ->name('orders.detail.shipments.addShipmentPackageItems');

                                    Route::prefix('/{basketItemId}')->where(['basketItemId' => '[0-9]+'])->group(function () {
                                        Route::put('', 'TabShipmentsController@editShipmentPackageItem')
                                            ->name('orders.detail.shipments.editShipmentPackageItem');
                                        Route::delete('', 'TabShipmentsController@deleteShipmentPackageItem')
                                            ->name('orders.detail.shipments.deleteShipmentPackageItem');
                                    });
                                });
                            });
                        });
                    });
                });
            });

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
                    Route::get('status', 'CargoDetailController@checkCourierCallStatus')->name('cargo.checkCourierCallStatus');
                    Route::post('', 'CargoDetailController@createCourierCall')->name('cargo.createCourierCall');
                    Route::put('cancel', 'CargoDetailController@cancelCourierCall')->name('cargo.cancelCourierCall');
                });
            });
        });

        Route::prefix('shipments')->namespace('Shipment')->group(function () {
            Route::get('', 'ShipmentListController@index')->name('shipment.list');
            Route::get('page', 'ShipmentListController@page')->name('shipment.pagination');
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
        Route::post('', 'OfferListController@createOffer')->name('offers.create');
        Route::put('', 'OfferListController@editOffer')->name('offers.edit');
        Route::put('change-status', 'OfferListController@changeSaleStatus')->name('offers.change.saleStatus');
        Route::delete('', 'OfferListController@deleteOffers')->name('offers.delete');
        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('', 'OfferDetailController@index')->name('offers.detail');
            Route::post('props', 'ProductDetailController@saveOfferProps')->name('offers.saveOfferProps');
        });
        Route::get('store-qty-info', 'OfferListController@loadStoreAndQty')->name('offers.storeAndQty');
        Route::get('validate-offer', 'OfferListController@validateOffer')->name('offers.validate');
    });

    Route::prefix('products')->namespace('Product')->group(function () {
        Route::get('', 'ProductListController@index')->name('products.list');
        Route::get('page', 'ProductListController@page')->name('products.listPage');

        Route::put('approval', 'ProductListController@updateApprovalStatus')->name('products.massApproval');
        Route::put('production', 'ProductListController@updateProductionStatus')->name('products.massProduction');
        Route::put('archive', 'ProductListController@updateArchiveStatus')->name('products.massArchive');
        Route::put('badges', 'ProductListController@attachBadges')->name('products.attachBadges');

        Route::prefix('properties')->group(function () {
            Route::get('', 'PropertiesController@list')->name('products.properties.list');
            Route::get('create', 'PropertiesController@create')->name('products.properties.create');
            Route::put('update', 'PropertiesController@update')->name('products.properties.update');
            Route::prefix('{id}')->group(function () {
                Route::get('', 'PropertiesController@detail')->name('products.properties.detail');
                Route::delete('', 'PropertiesController@delete')->name('products.properties.delete');
            });
        });

        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('detailData', 'ProductDetailController@detailData')->name('products.detailData');
            Route::get('', 'ProductDetailController@index')->name('products.detail');
            Route::post('', 'ProductDetailController@saveProduct')->name('products.saveProduct');
            Route::post('props', 'ProductDetailController@saveProps')->name('products.saveProps');
            Route::post('image', 'ProductDetailController@saveImage')->name('products.saveImage');
            Route::post('imageDelete', 'ProductDetailController@deleteImage')->name('products.deleteImage');
            Route::put('ingredients', 'ProductDetailController@saveIngredients')->name('products.saveIngredients');
            Route::put('changeApproveStatus', 'ProductDetailController@changeApproveStatus')->name('products.changeApproveStatus');
            Route::put('reject', 'ProductDetailController@reject')->name('products.reject');

            Route::prefix('tips')->group(function () {
                Route::post('', 'ProductDetailController@addTip')->name('product.addTip');
                Route::post('{tipId}/update', 'ProductDetailController@editTip')->name('product.editTip');
                Route::post('{tipId}/delete', 'ProductDetailController@deleteTip')->name('product.deleteTip');
            });
        });

        Route::prefix('variant-groups')->namespace('VariantGroup')->group(function () {
            Route::get('', 'VariantGroupListController@index')->name('variantGroups.list');
            Route::post('', 'VariantGroupListController@create')->name('variantGroups.create');
            Route::delete('', 'VariantGroupListController@delete')->name('variantGroups.delete');
            Route::get('page', 'VariantGroupListController@page')->name('variantGroups.pagination');
            Route::post('byOffers', 'VariantGroupListController@byOffers')->name('variantGroups.byOffers');

            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', 'VariantGroupDetailController@detail')->name('variantGroups.detail');
                Route::put('', 'VariantGroupDetailController@save')->name('variantGroups.detail.save');

                Route::namespace('Detail')->group(function () {
                    Route::prefix('products')->group(function () {
                        Route::get('', 'TabProductsController@load')->name('variantGroups.detail.products.load');
                        Route::post('', 'TabProductsController@add')->name('variantGroups.detail.products.add');
                        Route::delete('', 'TabProductsController@delete')->name('variantGroups.detail.products.delete');
                        Route::prefix('{productId}')->where(['id' => '[0-9]+'])->group(function () {
                            Route::put('set-main', 'TabProductsController@setMain')->name('variantGroups.detail.products.setMain');
                        });
                    });

                    Route::prefix('properties')->group(function () {
                        Route::get('', 'TabPropertiesController@load')->name('variantGroups.detail.properties.load');
                        Route::delete('', 'TabPropertiesController@delete')->name('variantGroups.detail.properties.delete');
                        Route::prefix('{propertyId}')->where(['id' => '[0-9]+'])->group(function () {
                            Route::post('', 'TabPropertiesController@add')->name('variantGroups.detail.properties.add');
                        });
                    });
                });
            });
        });

        Route::prefix('import-export')->namespace('ImportExport')->group(function () {
            Route::get('export-by-product-ids', 'ProductsExportController@exportByProductIds')->name('products.exportByProductIds');
            Route::get('export-by-filters', 'ProductsExportController@exportByFilters')->name('products.exportByFilters');
        });
    });
    
    Route::prefix('brands')->namespace('Product')->group(function () {
        Route::get('', 'BrandController@list')->name('brand.list');
        Route::get('page', 'BrandController@page')->name('brand.listPage');
        Route::post('save', 'BrandController@save')->name('brand.save');
        Route::post('delete', 'BrandController@delete')->name('brand.delete');
    });

    Route::prefix('categories')->namespace('Product')->group(function () {
        Route::get('', 'CategoryController@index')->name('categories.list');
        Route::post('', 'CategoryController@create')->name('categories.create');
        Route::put('', 'CategoryController@update')->name('categories.update');
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

        Route::prefix('contacts')->namespace('Contacts')->group(function () {
            Route::get('', 'ContactsController@list')->name('contacts.list');
            Route::post('add', 'ContactsController@add')->name('contacts.add');
            Route::put('edit', 'ContactsController@edit')->name('contacts.edit');
            Route::delete('remove', 'ContactsController@remove')->name('contacts.remove');
        });

        Route::prefix('product-badges')->namespace('ProductBadges')->group(function () {
            Route::get('', 'ProductBadgesController@list')->name('productBadges.list');
            Route::post('add', 'ProductBadgesController@add')->name('productBadges.add');
            Route::put('edit', 'ProductBadgesController@edit')->name('productBadges.edit');
            Route::put('reorder', 'ProductBadgesController@reorder')->name('productBadges.reorder');
            Route::delete('remove', 'ProductBadgesController@remove')->name('productBadges.remove');
        });

        Route::prefix('search-requests')->namespace('SearchRequest')->group(function () {
            Route::get('', 'SearchRequestController@list')->name('searchRequests.list');
            Route::post('create', 'SearchRequestController@create')->name('searchRequests.create');
            Route::put('update', 'SearchRequestController@update')->name('searchRequests.update');
            Route::put('reorder', 'SearchRequestController@reorder')->name('searchRequests.reorder');
            Route::delete('delete', 'SearchRequestController@delete')->name('searchRequests.delete');
        });

        Route::prefix('search-synonyms')->namespace('SearchSynonym')->group(function () {
            Route::get('', 'SearchSynonymController@list')->name('searchSynonyms.list');
            Route::get('page', 'SearchSynonymController@page')->name('searchSynonyms.page');
            Route::post('create', 'SearchSynonymController@create')->name('searchSynonyms.create');
            Route::put('update', 'SearchSynonymController@update')->name('searchSynonyms.update');
            Route::delete('delete', 'SearchSynonymController@delete')->name('searchSynonyms.delete');
        });

        Route::prefix('popular-brands')->namespace('PopularBrand')->group(function () {
            Route::get('', 'PopularBrandController@list')->name('popularBrands.list');
            Route::post('create', 'PopularBrandController@create')->name('popularBrands.create');
            Route::put('update', 'PopularBrandController@update')->name('popularBrands.update');
            Route::put('reorder', 'PopularBrandController@reorder')->name('popularBrands.reorder');
            Route::delete('delete', 'PopularBrandController@delete')->name('popularBrands.delete');
        });

        Route::prefix('popular-products')->namespace('PopularProduct')->group(function () {
            Route::get('', 'PopularProductController@list')->name('popularProducts.list');
            Route::post('create', 'PopularProductController@create')->name('popularProducts.create');
            Route::put('reorder', 'PopularProductController@reorder')->name('popularProducts.reorder');
            Route::delete('delete', 'PopularProductController@delete')->name('popularProducts.delete');
        });

        Route::prefix('categories')->namespace('Category')->group(function () {
            Route::get('', 'FrequentCategoryController@index')->name('frequentCategories.list');
            Route::put('', 'FrequentCategoryController@editCategories')->name('frequentCategories.edit');
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
            Route::delete('', 'MerchantStoreController@deleteArray')
                ->where(['id' => '[0-9]+'])
                ->name('merchantStore.deleteArray');

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
            Route::put('update-chat-user', 'ChatsController@updateChatUser')->name('communications.chats.updateChatUser');
            Route::get('broadcast', 'ChatsController@broadcast')->name('communications.chats.broadcast');
            Route::get('unlink-messenger-chats', 'ChatsController@unlinkMessengerChats')->name('communications.chats.unlinkMessengerChats');
        });

        Route::get('channels', 'ChannelController@channels')->name('communications.channels.list');
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

                    Route::prefix('newsletter')->group(function () {
                        Route::get('', 'TabNewsletterController@load')->name('customers.detail.newsletter');
                        Route::put('edit', 'TabNewsletterController@edit')->name('customers.detail.newsletter.edit');
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
                    Route::prefix('reviews')->group(function () {
                        Route::get('data', 'TabReviewsController@load')->name('customers.detail.reviews.data');
                        Route::get('page', 'TabReviewsController@page')->name('customers.detail.reviews.pagination');
                    });
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

        Route::prefix('promo-products')->group(function () {
            Route::get('', 'MassPromoProductsController@list')->name('referral.promo-products.list');
            Route::put('edit', 'MassPromoProductsController@editProduct')->name('referral.promo-products.edit');
            Route::put('attach', 'MassPromoProductsController@attachProduct')->name('referral.promo-products.attach');
            Route::delete('', 'MassPromoProductsController@removeProduct')->name('referral.promo-products.delete');
        });

        Route::prefix('options')->group(function () {
            Route::get('', 'OptionsController@index')->name('referral.options');
            Route::put('', 'OptionsController@save')->name('referral.options.save');
        });
    });

    Route::prefix('public-events')->namespace('PublicEvent')->group(function () {
        Route::get('is-code-unique', 'PublicEventDetailController@isCodeUnique')->name('public-event.isCodeUnique');
        Route::post('save', 'PublicEventDetailController@save')->name('public-event.save');
        
        Route::prefix('organizers')->group(function () {
            Route::get('', 'OrganizerController@list')->name('public-event.organizers.list');
            Route::get('page', 'OrganizerController@page')->name('public-event.organizers.page');
            Route::post('save', 'OrganizerController@save')->name('public-event.organizers.save');
            Route::post('delete', 'OrganizerController@delete')->name('public-event.organizers.delete');
        });

        Route::prefix('types')->group(function () {
            Route::get('', 'EventTypeController@list')->name('public-event.types.list');
            Route::get('page', 'EventTypeController@page')->name('public-event.types.page');
            Route::post('save', 'EventTypeController@save')->name('public-event.types.save');
            Route::post('delete', 'EventTypeController@delete')->name('public-event.types.delete');
        });

        Route::prefix('speakers')->group(function () {
            Route::get('', 'SpeakerController@list')->name('public-event.speakers.list');
            Route::get('page', 'SpeakerController@page')->name('public-event.speakers.page');
            Route::get('fullPage', 'SpeakerController@fullPage')->name('public-event.speakers.fullPage');
            Route::post('save', 'SpeakerController@save')->name('public-event.speakers.save');
            Route::post('delete', 'SpeakerController@delete')->name('public-event.speakers.delete');
            Route::get('{stage_id}', 'SpeakerController@getByStage')->name('public-event.sprint-stage.getSpeakers');
            Route::post('{stage_id}', 'SpeakerController@attachStage')->name('public-event.sprint-stage.attachSpeaker');
            Route::delete('{stage_id}', 'SpeakerController@detachStage')->name('public-event.sprint-stage.detachSpeaker');
        });

        Route::prefix('ticket-types')->group(function () {
            Route::get('', 'PublicEventTicketTypeController@list')->name('public-event.ticket-types.list');
            Route::get('page', 'PublicEventTicketTypeController@page')->name('public-event.ticket-types.page');
            //Route::get('{sprint_id}', 'PublicEventTicketTypeController@getBySprint')->name('public-event.sprint.getTicketTypes');
            Route::post('save', 'PublicEventTicketTypeController@save')->name('public-event.ticket-types.save');
            Route::post('delete', 'PublicEventTicketTypeController@delete')->name('public-event.ticket-types.delete');
            //Route::post('{sprint_id}', 'PublicEventTicketTypeController@createBySprint')->name('public-event.sprint.createTicketType');
            Route::post('{stage_id}', 'PublicEventTicketTypeController@attachStage')->name('public-event.ticket-types.attachStage');
            Route::delete('{stage_id}', 'PublicEventTicketTypeController@detachStage')->name('public-event.ticket-types.detachStage');
        });

        Route::prefix('sprint-stages')->group(function () {
            Route::get('', 'PublicEventSprintStageController@list')->name('public-event.sprint-stages.list');
            Route::get('page', 'PublicEventSprintStageController@page')->name('public-event.sprint-stages.page');
            // Route::get('{sprint_id}', 'PublicEventSprintStageController@getBySprint')->name('public-event.sprint.getSprintStages');
            Route::post('save', 'PublicEventSprintStageController@save')->name('public-event.sprint-stages.save');
            Route::post('delete', 'PublicEventSprintStageController@delete')->name('public-event.sprint-stages.delete');
            // Route::post('{sprint_id}', 'PublicEventSprintStageController@createBySprint')->name('public-event.sprint.createSprintStage');
            Route::post('{type_id}', 'PublicEventTicketTypeController@attachType')->name('public-event.sprint-stages.attachType');
            Route::delete('{type_id}', 'PublicEventTicketTypeController@detachType')->name('public-event.sprint-stages.detachType');
        });

        Route::prefix('sprint-documents')->group(function () {
            Route::get('', 'PublicEventSprintDocumentController@list')->name('public-event.sprint-documents.list');
            Route::get('page', 'PublicEventSprintDocumentController@page')->name('public-event.sprint-documents.page');
            Route::post('save', 'PublicEventSprintDocumentController@save')->name('public-event.sprint-documents.save');
            Route::post('delete', 'PublicEventSprintDocumentController@delete')->name('public-event.sprint-documents.delete');
            Route::get('{sprint_id}', 'PublicEventSprintDocumentController@getBySprint')->name('public-event.sprint-documents.getBySprint');
        });

        Route::prefix('professions')->group(function () {
            Route::get('', 'PublicEventProfessionController@list')->name('public-event.professions.list');
            Route::get('names', 'PublicEventProfessionController@names')->name('public-event.professions.names');
            Route::get('page', 'PublicEventProfessionController@page')->name('public-event.professions.page');
            Route::get('{event_id}', 'PublicEventProfessionController@getByEvent')->name('public-event.event.getProfessions');
            Route::post('save', 'PublicEventProfessionController@save')->name('public-event.professions.save');
            Route::post('delete', 'PublicEventProfessionController@delete')->name('public-event.professions.delete');
            Route::post('{event_id}', 'PublicEventProfessionController@createByEvent')->name('public-event.event.createProfession');
        });

        Route::prefix('places')->group(function () {
            Route::get('', 'PlaceController@list')->name('public-event.places.list');
            Route::get('page', 'PlaceController@page')->name('public-event.places.page');
            Route::get('fullList', 'PlaceController@fullList')->name('public-event.places.fullList');
            Route::post('save', 'PlaceController@save')->name('public-event.places.save');
            Route::post('delete', 'PlaceController@delete')->name('public-event.places.delete');
            Route::post('media', 'PlaceController@media')->name('public-event.places.media');
        });

        Route::prefix('media')->group(function () {
            Route::get('fullList', 'MediaController@fullList')->name('public-event.media.fullList');
            Route::post('save', 'MediaController@save')->name('public-event.media.save');
            Route::post('delete', 'MediaController@delete')->name('public-event.media.delete');
        });

        Route::prefix('sprint-sell-status')->group(function () {
            Route::post('save', 'PublicEventSprintSellStatusController@save')->name('public-event.sprint-sell-status.save');
            Route::post('delete', 'PublicEventSprintSellStatusController@delete')->name('public-event.sprint-sell-status.delete');
        });

        Route::prefix('sprints')->group(function () {
            Route::get('', 'PublicEventSprintController@list')->name('public-event.sprints.list');
            Route::get('page', 'PublicEventSprintController@page')->name('public-event.sprints.page');
            Route::post('save', 'PublicEventSprintController@save')->name('public-event.sprints.save');
            Route::post('delete', 'PublicEventSprintController@delete')->name('public-event.sprints.delete');
        });

        Route::get('statuses', 'PublicEventStatusController@index')->name('public-event.statuses.list');
        Route::get('event-statuses', 'PublicEventStatusController@event')->name('public-event.event-statuses.list');
        Route::get('list', 'PublicEventListController@load')->name('public-event.fullList');

        Route::prefix('{event_id}')->group(function () {
            Route::post('add-organizer-by-id', 'PublicEventDetailController@addOrganizerById')->name('public-event.addOrganizerById');
            Route::post('add-organizer-by-value', 'PublicEventDetailController@addOrganizerByValue')->name('public-event.addOrganizerByValue');

            Route::post('save-media', 'PublicEventDetailController@saveMedia')->name('public-event.saveMedia');
            Route::post('delete-media', 'PublicEventDetailController@deleteMedia')->name('public-event.deleteMedia');

            Route::get('recommendations', 'PublicEventDetailController@recommendations')->name('public-event.recommendations');
            Route::post('recommendation/{recommendation_id}', 'PublicEventDetailController@attachRecommendation')->name('public-event.attachRecommendation');
            Route::delete('recommendation/{recommendation_id}', 'PublicEventDetailController@detachRecommendation')->name('public-event.detachRecommendation');

            Route::prefix('sprints')->group(function () {
                Route::get('', 'PublicEventDetailController@getSprints')->name('public-event.getSprints');
                Route::post('', 'PublicEventDetailController@createSprint')->name('public-event.createSprint');
                Route::post('delete', 'PublicEventDetailController@deleteSprint')->name('public-event.deleteSprint');
            });

            
            Route::get('load', 'PublicEventDetailController@load')->name('public-event.load');
            Route::get('', 'PublicEventDetailController@index')->name('public-event.detail');
        });
        
        
        Route::get('', 'PublicEventListController@list')->name('public-event.list');
        Route::get('list/page', 'PublicEventListController@page')->name('public-event.list.page');
    });

    Route::prefix('communications/service-notifications')->namespace('ServiceNotification')->group(function () {
        Route::prefix('')->group(function () {
            Route::get('', 'ServiceNotificationController@page')->name('communications.service-notification.list');
            Route::get('list', 'ServiceNotificationController@list')->name('communications.service-notification.page');
            Route::get('{id}/templates', 'TemplateController@listNotification')->name('communications.service-notification.template.listNotification');
            Route::get('{id}/templatesReload', 'TemplateController@pageNotification')->name('communications.service-notification.template.reload');
            Route::post('save', 'ServiceNotificationController@save')->name('communications.service-notification.save');
            Route::post('delete', 'ServiceNotificationController@delete')->name('communications.service-notification.delete');
            Route::post('send', 'ServiceNotificationController@send')->name('communications.service-notification.send');
        });

        Route::prefix('templates')->group(function () {
            Route::get('list', 'TemplateController@list')->name('communications.service-notification.template.fullList');
            Route::post('save', 'TemplateController@save')->name('communications.service-notification.template.save');
            Route::post('delete', 'TemplateController@delete')->name('communications.service-notification.template.delete');
        });

        Route::prefix('system-alerts')->group(function () {
            Route::post('save', 'SystemAlertController@save')->name('communications.service-notification.system-alert.save');
            Route::post('delete', 'SystemAlertController@delete')->name('communications.service-notification.system-alert.delete');
            Route::get('{service_notification_id}', 'SystemAlertController@pageNotification')->name('communications.service-notification.system-alert.page');
        });
    });
    
    Route::prefix('organizers')->namespace('PublicEvent')->group(function () {
        Route::get('available', 'PublicEventDetailController@availableOrganizers')->name('public-event.availableOrganizers');
    });


});
