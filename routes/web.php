<?php

use App\Http\Controllers\Basket\BasketDetailController;
use App\Http\Controllers\Basket\BasketListController;
use App\Http\Controllers\BillingReport\BillingReportController;
use App\Http\Controllers\Claim\ContentClaimController;
use App\Http\Controllers\Claim\PriceChangeClaimController;
use App\Http\Controllers\Claim\ProductCheckClaimController;
use App\Http\Controllers\Communications\ChannelController;
use App\Http\Controllers\Communications\ChatsController;
use App\Http\Controllers\Communications\StatusController;
use App\Http\Controllers\Communications\ThemeController;
use App\Http\Controllers\Communications\TypeController;
use App\Http\Controllers\Content\Banner\BannerDetailController;
use App\Http\Controllers\Content\Banner\BannerListController;
use App\Http\Controllers\Content\Category\FrequentCategoryController;
use App\Http\Controllers\Content\Contacts\ContactsController;
use App\Http\Controllers\Content\Landing\LandingDetailController;
use App\Http\Controllers\Content\Landing\LandingListController;
use App\Http\Controllers\Content\Menu\MenuDetailController;
use App\Http\Controllers\Content\Menu\MenuListController;
use App\Http\Controllers\Content\PopularBrand\PopularBrandController;
use App\Http\Controllers\Content\PopularProduct\PopularProductController;
use App\Http\Controllers\Content\ProductBadges\ProductBadgesController;
use App\Http\Controllers\Content\ProductGroup\ProductGroupDetailController;
use App\Http\Controllers\Content\ProductGroup\ProductGroupListController;
use App\Http\Controllers\Content\Redirect\RedirectDetailController;
use App\Http\Controllers\Content\Redirect\RedirectListController;
use App\Http\Controllers\Content\SearchRequest\SearchRequestController;
use App\Http\Controllers\Content\SearchSynonym\SearchSynonymController;
use App\Http\Controllers\Content\Seo\SeoController;
use App\Http\Controllers\Customers\ActivitiesController;
use App\Http\Controllers\Customers\CustomerDetailController;
use App\Http\Controllers\Customers\CustomerListController;
use App\Http\Controllers\Customers\CustomerWhitelistController;
use App\Http\Controllers\Customers\Detail\TabBonusController;
use App\Http\Controllers\Customers\Detail\TabDocumentController;
use App\Http\Controllers\Customers\Detail\TabNewsletterController;
use App\Http\Controllers\Customers\Detail\TabOrderReferrerController;
use App\Http\Controllers\Customers\Detail\TabPreferenceController;
use App\Http\Controllers\Customers\Detail\TabPromocodesController;
use App\Http\Controllers\Customers\Detail\TabPromoPageController;
use App\Http\Controllers\Customers\Detail\TabPromoProductController;
use App\Http\Controllers\Customers\Detail\TabReviewsController;
use App\Http\Controllers\Customers\Detail\TabMainController as CustomerTabMainController;
use App\Http\Controllers\Customers\Detail\TabOrderController as CustomerTabOrderController;
use App\Http\Controllers\Customers\Detail\TabBillingController as CustomerTabBillingController;
use App\Http\Controllers\DocumentTemplatesController;
use App\Http\Controllers\Logistics\DeliveryKpiController;
use App\Http\Controllers\Logistics\DeliveryPriceController;
use App\Http\Controllers\Logistics\DeliveryService\DeliveryServiceDetailController;
use App\Http\Controllers\Logistics\DeliveryService\DeliveryServiceListController;
use App\Http\Controllers\Logistics\DeliveryService\Detail\TabLimitationsController;
use App\Http\Controllers\Logistics\DeliveryService\Detail\TabSettingsController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Marketing\BonusController;
use App\Http\Controllers\Marketing\BundleController;
use App\Http\Controllers\Marketing\CertificateCardController;
use App\Http\Controllers\Marketing\CertificateController;
use App\Http\Controllers\Marketing\CertificateDesignController;
use App\Http\Controllers\Marketing\CertificateNominalController;
use App\Http\Controllers\Marketing\CertificateReportController;
use App\Http\Controllers\Marketing\DiscountController;
use App\Http\Controllers\Marketing\PromoCodeController;
use App\Http\Controllers\Merchant\Detail\TabBillingController;
use App\Http\Controllers\Merchant\Detail\TabCommissionController;
use App\Http\Controllers\Merchant\Detail\TabDigestController;
use App\Http\Controllers\Merchant\Detail\TabExtSystemsController;
use App\Http\Controllers\Merchant\Detail\TabMainController;
use App\Http\Controllers\Merchant\Detail\TabMarketingController;
use App\Http\Controllers\Merchant\Detail\TabOperatorController;
use App\Http\Controllers\Merchant\Detail\TabOrderController;
use App\Http\Controllers\Merchant\Detail\TabProductController;
use App\Http\Controllers\Merchant\Detail\TabPublicEventController;
use App\Http\Controllers\Merchant\Detail\TabStoreController;
use App\Http\Controllers\Merchant\Detail\TabVatController;
use App\Http\Controllers\Merchant\MerchantCommissionController;
use App\Http\Controllers\Merchant\MerchantDetailController;
use App\Http\Controllers\Merchant\MerchantListController;
use App\Http\Controllers\Merchant\MerchantOperatorController;
use App\Http\Controllers\Merchant\MerchantSettlementsController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\Order\Cargo\CargoDetailController;
use App\Http\Controllers\Order\Cargo\CargoListController;
use App\Http\Controllers\Order\Create\OrderCreateController;
use App\Http\Controllers\Order\Detail\TabDeliveriesController;
use App\Http\Controllers\Order\Detail\TabShipmentsController;
use App\Http\Controllers\Order\Directory\OrderReturnReasonListController;
use App\Http\Controllers\Order\Directory\OrderStatusListController;
use App\Http\Controllers\Order\FlowDeliveryController;
use App\Http\Controllers\Order\OrderDetailController;
use App\Http\Controllers\Order\OrderListController;
use App\Http\Controllers\Order\Detail\TabMainController as OrderTabMainController;
use App\Http\Controllers\Order\Shipment\ShipmentListController;
use App\Http\Controllers\Product\BrandController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\ImportExport\ProductsExportController;
use App\Http\Controllers\Product\OfferDetailController;
use App\Http\Controllers\Product\OfferListController;
use App\Http\Controllers\Product\ProductDetailController;
use App\Http\Controllers\Product\ProductListController;
use App\Http\Controllers\Product\PropertiesController;
use App\Http\Controllers\Product\VariantGroup\Detail\TabProductsController;
use App\Http\Controllers\Product\VariantGroup\Detail\TabPropertiesController;
use App\Http\Controllers\Product\VariantGroup\VariantGroupDetailController;
use App\Http\Controllers\Product\VariantGroup\VariantGroupListController;
use App\Http\Controllers\PublicEvent\EventTypeController;
use App\Http\Controllers\PublicEvent\MediaController;
use App\Http\Controllers\PublicEvent\OrganizerController;
use App\Http\Controllers\PublicEvent\PlaceController;
use App\Http\Controllers\PublicEvent\PublicEventDetailController;
use App\Http\Controllers\PublicEvent\PublicEventListController;
use App\Http\Controllers\PublicEvent\PublicEventOrdersController;
use App\Http\Controllers\PublicEvent\PublicEventProfessionController;
use App\Http\Controllers\PublicEvent\PublicEventSprintController;
use App\Http\Controllers\PublicEvent\PublicEventSprintDocumentController;
use App\Http\Controllers\PublicEvent\PublicEventSprintSellStatusController;
use App\Http\Controllers\PublicEvent\PublicEventSprintStageController;
use App\Http\Controllers\PublicEvent\PublicEventStatusController;
use App\Http\Controllers\PublicEvent\PublicEventTicketsController;
use App\Http\Controllers\PublicEvent\PublicEventTicketTypeController;
use App\Http\Controllers\PublicEvent\SpeakerController;
use App\Http\Controllers\PublicEvent\SpecialtyController;
use App\Http\Controllers\Referral\LevelsController;
use App\Http\Controllers\Referral\MassPromoProductsController;
use App\Http\Controllers\Referral\OptionsController;
use App\Http\Controllers\ServiceNotification\ServiceNotificationController;
use App\Http\Controllers\ServiceNotification\SystemAlertController;
use App\Http\Controllers\ServiceNotification\TemplateController;
use App\Http\Controllers\ServiceNotification\VariableController;
use App\Http\Controllers\Settings\MarketingController;
use App\Http\Controllers\Settings\OrganizationCardController;
use App\Http\Controllers\Settings\PackagesController;
use App\Http\Controllers\Settings\PaymentMethodsController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\UsersController;
use App\Http\Controllers\Store\MerchantStoreController;
use Illuminate\Support\Facades\Route;

Route::get('user/{id}/change-password/{signature}', [UsersController::class, 'changePassword'])->name('page.changePassword');

Route::prefix('login')->group(function () {
    Route::get('', [MainController::class, 'login'])->name('page.login');
    Route::post('', [MainController::class, 'loginAjax'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [MainController::class, 'home'])->name('home');
    Route::post('upload', [MainController::class, 'uploadFile'])->name('uploadFile');
    Route::post('logout', [MainController::class, 'logoutAjax'])->name('logout');

    Route::prefix('search')->group(function () {
        Route::get('products', [UsersController::class, 'changePassword'])->name('search.products');
    });

    Route::prefix('merchant')->namespace('Merchant')->group(function () {
        Route::prefix('list')->group(function () {
            Route::get('registration', [MerchantListController::class, 'registration'])->name('merchant.registrationList');
            Route::get('active', [MerchantListController::class, 'active'])->name('merchant.activeList');

            Route::get('page', [MerchantListController::class, 'page'])->name('merchant.listPage');
            Route::put('status', [MerchantListController::class, 'status'])->name('merchant.listPage.changeStatus');

            Route::post('', [MerchantListController::class, 'createMerchant'])->name('merchant.create');
            Route::get('user-exists', [MerchantListController::class, 'checkEmailExists'])->name('check.emailExists');
        });

        Route::prefix('detail/{id}')->group(function () {
            Route::get('', [MerchantDetailController::class, 'index'])->name('merchant.detail');
            Route::post('', [MerchantDetailController::class, 'updateMerchant'])->name('merchant.detail.edit');

            Route::namespace('Detail')->group(function () {
                Route::prefix('operators')->group(function () {
                    Route::get('data', [TabOperatorController::class, 'loadData'])->name('merchant.detail.operator.data');
                    Route::get('page', [TabOperatorController::class, 'loadOperators'])->name('merchant.detail.operator.pagination');
                });
                Route::prefix('digest')->group(function () {
                    Route::get('', 'TabDigestController@load')->name('merchant.detail.digest');
                    Route::put('comment', [TabDigestController::class, 'comment'])->name('merchant.detail.digest.comment');
                    Route::post('auth', [TabDigestController::class, 'auth'])->name('merchant.detail.digest.auth');
                });
                Route::prefix('main')->group(function () {
                    Route::get('', [TabMainController::class, 'load'])->name('merchant.detail.main');

                    Route::post('document', [TabMainController::class, 'createDocument'])->name('merchant.detail.main.document.create');
                    Route::delete('document', [TabMainController::class, 'deleteDocument'])->name('merchant.detail.main.document.delete');
                });
                Route::prefix('commission')->group(function () {
                    Route::get('', [TabCommissionController::class, 'load'])->name('merchant.detail.commission');
                    Route::post('save', [TabCommissionController::class, 'saveCommission'])->name('merchant.detail.commission.save');
                    Route::post('remove', [TabCommissionController::class, 'removeCommission'])->name('merchant.detail.commission.remove');
                });
                Route::prefix('vat')->group(function () {
                    Route::get('', [TabVatController::class, 'load'])->name('merchant.detail.vat');
                    Route::post('save', [TabVatController::class, 'saveVat'])->name('merchant.detail.vat.save');
                    Route::post('remove', [TabVatController::class, 'removeVat'])->name('merchant.detail.vat.remove');
                });
                Route::prefix('marketing')->group(function () {
                    Route::prefix('discounts')->group(function () {
                        Route::get('data', [TabMarketingController::class, 'loadDiscountsData'])->name('merchant.detail.marketing.discounts.data');
                        Route::get('page', [TabMarketingController::class, 'pageDiscounts'])->name('merchant.detail.marketing.discounts.pagination');
                    });
                    Route::prefix('promo-codes')->group(function () {
                        Route::get('data', [TabMarketingController::class, 'loadPromoCodesData'])->name('merchant.detail.marketing.promo-codes.data');
                        Route::get('', [TabMarketingController::class, 'loadPromoCodes'])->name('merchant.detail.marketing.promo-codes');
                    });
                });
                Route::prefix('order')->group(function () {
                    Route::get('data', [TabOrderController::class, 'loadOrdersData'])->name(
                        'merchant.detail.order.data'
                    );
                    Route::get('page', [TabOrderController::class, 'page'])->name('merchant.detail.order.pagination');
                });
                Route::prefix('product')->group(function () {
                    Route::get('data', [TabProductController::class, 'loadProductsData'])->name('merchant.detail.product.data');
                    Route::get('page', [TabProductController::class, 'page'])->name('merchant.detail.product.pagination');
                });
                Route::prefix('store')->group(function () {
                    Route::get('page', [TabStoreController::class, 'page'])->name('merchant.detail.store.pagination');
                });
                Route::prefix('billingList')->group(function () {
                    Route::get('', [TabBillingController::class, 'billingList'])->name('merchant.detail.billingList');
                    Route::post('add-correction', [TabBillingController::class, 'addCorrection'])->name('merchant.detail.billingList.addCorrection');
                    Route::get('add-return/{operationId}', [TabBillingController::class, 'addReturn'])->name('merchant.detail.billingList.addReturn');
                    Route::delete('delete-operation/{operationId}', [TabBillingController::class, 'deleteOperation'])->name(
                        'merchant.detail.billingList.deleteOperation'
                    );
                    Route::get('correction/download/{fileId}', [TabBillingController::class, 'correctionDownload'])->name(
                        'merchant.detail.download-correction-document'
                    );
                });
                Route::prefix('eventBillingList')->group(function () {
                    Route::get('', [TabPublicEventController::class, 'eventBillingList'])->name('merchant.detail.eventBillingList');
                    Route::get('download-report', [TabPublicEventController::class, 'downloadEventBillingList'])->name(
                        'merchant.detail.eventBillingList.downloadEventBillingList'
                    );
                });
                Route::prefix('extSystems')->group(function () {
                    Route::get('', [TabExtSystemsController::class, 'load'])->name('merchant.detail.extSystems');
                    Route::post('', [TabExtSystemsController::class, 'create'])->name('merchant.detail.extSystems.store');
                    Route::put('', [TabExtSystemsController::class, 'update'])->name('merchant.detail.extSystems.update');
                });
            });
        });

        Route::prefix('commission')->group(function () {
            Route::get('', [MerchantCommissionController::class, 'index'])->name('merchant.commission');
            Route::post('', [MerchantCommissionController::class, 'save'])->name('merchant.commission.save');
        });

        Route::prefix('settlements')->group(function () {
            Route::get('', [MerchantSettlementsController::class, 'index'])->name('merchant.settlements');
            Route::get('page', [MerchantSettlementsController::class, 'page'])->name('merchant.settlements.page');

            Route::get('pay-registry', [MerchantSettlementsController::class, 'getPayRegistry'])->name('merchant.settlements.payRegistry');
            Route::get('pay-registry/page', [MerchantSettlementsController::class, 'getPayRegistryPage'])->name('merchant.settlements.payRegistry.page');

            Route::get('pay-registry/download/{registryFileId}', [MerchantSettlementsController::class, 'downloadPayRegistry'])->name(
                'merchant.settlements.downloadPayRegistry'
            );
            Route::post('pay', [MerchantSettlementsController::class, 'createPayRegistry'])->name('merchant.settlements.createPayRegistry');
            Route::delete('pay-registry/{payRegistryId}', [MerchantSettlementsController::class, 'deletePayRegistry'])->name(
                'merchant.settlements.deletePayRegistry'
            );
        });

        Route::prefix('operator')->group(function () {
            Route::prefix('{id}')->group(function () {
                Route::get('', [MerchantOperatorController::class, 'indexEdit'])->name('merchant.operator.indexEdit');
                Route::put('update', [MerchantOperatorController::class, 'update'])->name('merchant.operator.update');
            });
            Route::get('', [MerchantOperatorController::class, 'indexCreate'])->name('merchant.operator.indexCreate');
            Route::post('save', [MerchantOperatorController::class, 'save'])->name('merchant.operator.save');
            Route::put('change-roles', [MerchantOperatorController::class, 'changeRoles'])->name('merchant.operator.changeRoles');
            Route::delete('', [MerchantOperatorController::class, 'delete'])->name('merchant.operator.delete');
        });
    });

    Route::prefix('billing-reports')->namespace('BillingReport')->group(function () {
        Route::prefix('{type}/{entityId}')->group(function () {
            Route::get('load', [BillingReportController::class, 'load'])->name('billingReport.detail.billing');
            Route::get('list', [BillingReportController::class, 'billingReports'])->name('billingReport.detail.reports');
            Route::get('download/{reportId}', [BillingReportController::class, 'billingReportDownload'])->name(
                'billingReport.detail.download'
            );
            Route::put('billing_cycle', [BillingReportController::class, 'billingCycle'])->name('billingReport.detail.billing_cycle');
            Route::post('create', [BillingReportController::class, 'billingReportCreate'])->name('billingReport.detail.create');
            Route::delete('{reportId}', [BillingReportController::class, 'deleteBillingReport'])->where(['reportId' => '[0-9]+'])->name(
                'billingReport.detail.delete'
            );
            Route::put('{reportId}', [BillingReportController::class, 'billingReportStatusUpdate'])->where(['reportId' => '[0-9]+'])->name(
                'billingReport.detail.updateStatus'
            );
        });
    });

    Route::prefix('marketing')->namespace('Marketing')->group(function () {
        Route::prefix('discounts')->group(function () {
            Route::get('/', [DiscountController::class, 'index'])->name('discount.list');
            Route::post('/', [DiscountController::class, 'create'])->name('discount.save');
            Route::get('/create', [DiscountController::class, 'createPage'])->name('discount.create');
            Route::get('page', [DiscountController::class, 'page'])->name('discount.pagination');
            Route::put('/', [DiscountController::class, 'status'])->name('discount.status');
            Route::delete('/', [DiscountController::class, 'delete'])->name('discount.delete');
            Route::post('/copy', [DiscountController::class, 'copy'])->name('discount.copy');

            Route::prefix('/{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('/edit', [DiscountController::class, 'edit'])->name('discount.edit');
                Route::get('/orders', [DiscountController::class, 'discountOrdersDetail'])->name('discount.orders');
                Route::get('', [DiscountController::class, 'detail'])->name('discount.detail');
            });

            Route::put('/{id}', [DiscountController::class, 'update'])
                ->where(['id' => '[0-9]+'])
                ->name('discount.update');
        });

        Route::prefix('bundles')->group(function () {
            Route::get('/', [BundleController::class, 'index'])->name('bundle.list');
            Route::get('page', [BundleController::class, 'page'])->name('bundle.pagination');
            Route::get('/create', [BundleController::class, 'createPage'])->name('bundle.create');
        });

        Route::prefix('promo-code')->group(function () {
            Route::get('', [PromoCodeController::class, 'index'])->name('promo-code.list');
            Route::post('', [PromoCodeController::class, 'create'])->name('promo-code.save');
            Route::get('create', [PromoCodeController::class, 'createPage'])->name('promo-code.create');
            Route::get('generate', [PromoCodeController::class, 'generate'])->name('promo-code.generate');
            Route::get('check', [PromoCodeController::class, 'checkUnique'])->name('promo-code.check');
            Route::post('status', [PromoCodeController::class, 'status'])->name('promo-code.status');
            Route::delete('delete', [PromoCodeController::class, 'delete'])->name('promo-code.delete');
        });


        // Роут для подарочных сертификатов
        Route::prefix('certificate')->group(function () {

            Route::get('', [CertificateController::class, 'index'])->name('certificate.index');
            Route::get('tabs/{tab}', [CertificateController::class, 'getTab'])->name('certificate.tab');
            Route::put('content', [CertificateController::class, 'storeContent'])->name('certificate.content_save');


            Route::prefix('designs')->group(function () {
                Route::get('', [CertificateDesignController::class, 'index'])->name('certificate.designs');
                Route::get('create', [CertificateDesignController::class, 'createPage'])->name('certificate.designs_add');
                Route::post('', [CertificateDesignController::class, 'create'])->name('certificate.designs_save');

                Route::prefix('{id}')->group(function () {
                    Route::get('', [CertificateDesignController::class, 'editPage'])->name('certificate.designs_edit');
                    Route::delete('', [CertificateDesignController::class, 'delete'])->name('certificate.designs_delete');
                    Route::put('', [CertificateDesignController::class, 'update'])->name('certificate.designs_update');
                });
            });

            Route::prefix('nominals')->group(function () {
                Route::get('', [CertificateNominalController::class, 'index'])->name('certificate.nominals');
                Route::get('create', [CertificateNominalController::class, 'createPage'])->name('certificate.nominals_add');
                Route::post('', [CertificateNominalController::class, 'create'])->name('certificate.nominals_save');

                Route::prefix('{id}')->group(function () {
                    Route::get('', [CertificateNominalController::class, 'editPage'])->name('certificate.nominals_edit');
                    Route::delete('', [CertificateNominalController::class, 'delete'])->name('certificate.nominals_delete');
                    Route::put('', [CertificateNominalController::class, 'update'])->name('certificate.nominals_update');
                });
            });

            Route::prefix('card')->group(function () {
                Route::post('activate', [CertificateCardController::class, 'activate'])->name('certificate.card_activate');
                Route::prefix('{id}')->group(function () {
                    Route::get('', [CertificateCardController::class, 'editPage'])->name('certificate.card_edit');
                    Route::put('', [CertificateCardController::class, 'update'])->name('certificate.card_update');
                    Route::put('deactivate', [CertificateCardController::class, 'deactivate'])->name('certificate.card_deactivate');
                    Route::post('update/expired', [CertificateCardController::class, 'updateExpireAt'])->name('certificate.update_activation_period');
                    Route::post('notify', [CertificateCardController::class, 'sendNotification'])->name('certificate.send_notification');
                });
            });

            Route::prefix('reports')->group(function () {
                Route::post('', [CertificateReportController::class, 'create'])->name('certificate.report_create');
                Route::get('{id}', [CertificateReportController::class, 'download'])->name('certificate.report_download');
            });
        });

        Route::prefix('bonus')->group(function () {
            Route::get('', [BonusController::class, 'index'])->name('bonus.list');
            Route::post('', [BonusController::class, 'create'])->name('bonus.save');
            Route::get('create', [BonusController::class, 'createPage'])->name('bonus.create');
            Route::post('status', [BonusController::class, 'status'])->name('bonus.status');
            Route::delete('delete', [BonusController::class, 'delete'])->name('bonus.delete');

            Route::put('productLimit', [BonusController::class, 'changeProductLimit'])->name('bonus.changeMPP');
        });
    });

    Route::prefix('claims')->namespace('Claim')->group(function () {
        Route::prefix('product-check')->group(function () {
            Route::get('', [ProductCheckClaimController::class, 'index'])->name('productCheckClaims.list');
            Route::get('page', [ProductCheckClaimController::class, 'page'])->name('productCheckClaims.pagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', [ProductCheckClaimController::class, 'detail'])->name('productCheckClaims.detail');
                Route::put('changeStatus', [ProductCheckClaimController::class, 'changeStatus'])->name('productCheckClaims.changeStatus');
            });
        });

        Route::prefix('content')->group(function () {
            Route::get('', [ContentClaimController::class, 'index'])->name('contentClaims.list');
            Route::get('create', [ContentClaimController::class, 'create'])->name('contentClaims.create');
            Route::get('page', [ContentClaimController::class, 'page'])->name('contentClaims.pagination');
            Route::post('createClaim', [ContentClaimController::class, 'saveClaim'])->name('contentClaims.createClaim');
            Route::put('changeStatus', [ContentClaimController::class, 'changeStatuses'])->name('contentClaims.changeStatuses');
            Route::delete('', [ContentClaimController::class, 'deleteClaims'])->name('contentClaims.deleteClaims');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', [ContentClaimController::class, 'detail'])->name('contentClaims.detail');
                Route::put('', [ContentClaimController::class, 'update'])->name('contentClaims.update');
                Route::prefix('documents')->group(function () {
                    Route::get('acceptance-act', [ContentClaimController::class, 'acceptanceAct'])->name('contentClaims.documents.acceptanceAct');
                });
            });
            Route::get('products-by-merchant', [ContentClaimController::class, 'loadProductsByMerchantId'])->name('contentClaims.productsByMerchant');
        });

        Route::prefix('price-change')->group(function () {
            Route::get('', [PriceChangeClaimController::class, 'index'])->name('priceChangeClaims.list');
            Route::get('page', [PriceChangeClaimController::class, 'page'])->name('priceChangeClaims.pagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', [PriceChangeClaimController::class, 'detail'])->name('priceChangeClaims.detail');
                Route::put('changeStatus', [PriceChangeClaimController::class, 'changeStatus'])->name('priceChangeClaims.changeStatus');
                Route::put('changePrice', [PriceChangeClaimController::class, 'changePrice'])->name('priceChangeClaims.changePrice');
            });
        });
    });
    Route::prefix('settings')->namespace('Settings')->group(function () {
        Route::prefix('payment-methods')->group(function () {
            Route::get('', [PaymentMethodsController::class, 'list'])->name('settings.paymentMethods');
            Route::get('{id}/edit', [PaymentMethodsController::class, 'edit'])->name('settings.paymentMethods.edit');
            Route::put('{id}', [PaymentMethodsController::class, 'update'])->name('settings.paymentMethods.update');
        });
        Route::prefix('users')->group(function () {
            Route::get('page', [UsersController::class, 'page'])->name('settings.userListPagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', [UsersController::class, 'detail'])->name('settings.userDetail');
                Route::put('addRoles', [UsersController::class, 'addRoles'])->name('user.addRoles');
                Route::post('deleteRoles', [UsersController::class, 'deleteRoles'])->name('user.deleteRoles');
                Route::put('banUser', [UsersController::class, 'banUser'])->name('user.banUser');
                Route::put('unBanUser', [UsersController::class, 'unBanUser'])->name('user.unBanUser');
            });
            Route::get('', [UsersController::class, 'index'])->name('settings.userList');
            Route::post('', [UsersController::class, 'saveUser'])->name('settings.saveUser');
            Route::get('by-roles', [UsersController::class, 'usersByRoles'])->name('user.byRoles');
            Route::put('banArray', [UsersController::class, 'banArray'])->name('settings.banArray');
            Route::post('updatePassword', [UsersController::class, 'updatePassword'])->name('user.updatePassword');
            Route::get('is-unique', [UsersController::class, 'isUnique'])->name('user.isUnique');
        });

        Route::prefix('roles')->group(function () {
            Route::get('page', [RoleController::class, 'page'])->name('settings.roleListPagination');
            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', [RoleController::class, 'detail'])->name('settings.roleDetail');
                Route::put('', [RoleController::class, 'upsert'])->name('settings.updateRole');
                Route::delete('', [RoleController::class, 'deleteRole'])->name('settings.deleteRole');
                Route::put('updateBlockPermissions', [RoleController::class, 'updateBlockPermissions'])->name('settings.updateBlockPermissions');
            });
            Route::get('', [RoleController::class, 'index'])->name('settings.rolesList');
            Route::post('', [RoleController::class, 'upsert'])->name('settings.createRole');
        });

        Route::prefix('organization-card')->group(function () {
            Route::get('', [OrganizationCardController::class, 'index'])->name('settings.organizationCard');
            Route::put('', [OrganizationCardController::class, 'update'])->name('settings.organizationCard.update');
        });

        Route::prefix('marketing')->group(function () {
            Route::get('', [MarketingController::class, 'index'])->name('settings.marketing');
            Route::put('', [MarketingController::class, 'update'])->name('settings.marketing.update');
        });

        Route::prefix('packages')->group(function () {
            Route::get('', [PackagesController::class, 'list'])->name('settings.packages.list');
        });
    });

    Route::prefix('notifications')->group(function () {
        Route::get('', [NotificationsController::class, 'read'])->name('notifications.get');
        Route::post('', [NotificationsController::class, 'markAll'])->name('notifications.markAll');
    });

    Route::prefix('document-templates')->group(function () {
        Route::get('claim-act', [DocumentTemplatesController::class, 'claimAct'])->name('documentTemplates.claimAct');
        Route::get('acceptance-act', [DocumentTemplatesController::class, 'acceptanceAct'])->name('documentTemplates.acceptanceAct');
        Route::get('inventory', [DocumentTemplatesController::class, 'inventory'])->name('documentTemplates.inventory');
        Route::get('assembling-card', [DocumentTemplatesController::class, 'assemblingCard'])->name('documentTemplates.assemblingCard');
    });

    Route::prefix('orders')->namespace('Order')->group(function () {
        Route::get('', [OrderListController::class, 'index'])->name('orders.list');
        Route::get('page', [OrderListController::class, 'page'])->name('orders.pagination');
        Route::post('byOffers', [OrderListController::class, 'byOffers'])->name('orders.byOffers');

        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('', [OrderDetailController::class, 'detail'])->name('orders.detail');
            Route::put('changeStatus', [OrderDetailController::class, 'changeStatus'])->name('orders.changeStatus');
            Route::put('markAsPaid', [OrderDetailController::class, 'markAsPaid'])->name('orders.markAsPaid');
            Route::put('markAsPaidForce', [OrderDetailController::class, 'markAsPaidForce'])->name('orders.markAsPaidForce');
            Route::put('capturePayment', [OrderDetailController::class, 'capturePayment'])->name('orders.capturePayment');
            Route::put('cancel', [OrderDetailController::class, 'cancel'])->name('orders.cancel');
            Route::put('return', [OrderDetailController::class, 'returnCompletedOrder'])->name('orders.return');
            Route::get('invoice-offer', [OrderDetailController::class, 'invoiceOffer'])->name('order.invoiceOffer');
            Route::get('upd', [OrderDetailController::class, 'upd'])->name('order.upd');

            Route::namespace('Detail')->group(function () {
                Route::prefix('main')->group(function () {
                    Route::get('', [OrderTabMainController::class, 'load'])->name('orders.detail.main');
                    Route::put('', [OrderTabMainController::class, 'save'])->name('orders.detail.main.save');
                });
                Route::prefix('deliveries')->group(function () {
                    Route::prefix('{deliveryId}')->where(['deliveryId' => '[0-9]+'])->group(function () {
                        Route::get('', [TabDeliveriesController::class, 'load'])->name('orders.detail.deliveries');
                        Route::put('', [TabDeliveriesController::class, 'save'])->name('orders.detail.deliveries.save');
                        Route::put('change-status', [TabDeliveriesController::class, 'changeDeliveryStatus'])->name(
                            'orders.detail.deliveries.changeDeliveryStatus'
                        );
                        Route::put('save-delivery-order', [TabDeliveriesController::class, 'saveDeliveryOrder'])->name(
                            'orders.detail.deliveries.saveDeliveryOrder'
                        );
                        Route::put('cancel-delivery-order', [TabDeliveriesController::class, 'cancelDeliveryOrder'])->name(
                            'orders.detail.deliveries.cancelDeliveryOrder'
                        );
                        Route::put('cancel', [TabDeliveriesController::class, 'cancelDelivery'])->name('orders.detail.deliveries.cancel');
                    });
                });
                Route::prefix('shipments')->group(function () {
                    Route::prefix('{shipmentId}')->where(['shipmentId' => '[0-9]+'])->group(function () {
                        Route::put('', [TabShipmentsController::class, 'save'])->name('orders.detail.shipments.save');
                        Route::put('change-status', [TabShipmentsController::class, 'changeShipmentStatus'])->name(
                            'orders.detail.shipments.changeShipmentStatus'
                        );
                        Route::put('mark-as-non-problem', [TabShipmentsController::class, 'markAsNonProblemShipment'])->name(
                            'orders.detail.shipments.markAsNonProblem'
                        );
                        Route::get('barcodes', [TabShipmentsController::class, 'barcodes'])->name('orders.detail.shipments.barcodes');
                        Route::get('cdek-receipt', [TabShipmentsController::class, 'cdekReceipt'])->name('orders.detail.shipments.cdekReceipt');
                        Route::put('cancel', [TabShipmentsController::class, 'cancelShipment'])->name('orders.detail.shipments.cancel');
                        Route::put('/items/{basketItemId}', [TabShipmentsController::class, 'cancelShipmentItem'])->name(
                            'orders.detail.shipments.cancelShipmentItem'
                        );

                        Route::prefix('documents')->group(function () {
                            Route::get('acceptance-act', [TabShipmentsController::class, 'acceptanceAct'])->name(
                                'orders.detail.shipments.documents.acceptanceAct'
                            );
                            Route::get('inventory', [TabShipmentsController::class, 'inventory'])->name('orders.detail.shipments.documents.inventory');
                            Route::get('assembling-card', [TabShipmentsController::class, 'assemblingCard'])->name(
                                'orders.detail.shipments.documents.assemblingCard'
                            );
                            Route::get('upd', [TabShipmentsController::class, 'shipmentUpd'])->name('orders.detail.shipments.documents.upd');
                        });

                        Route::prefix('/shipment-packages')->group(function () {
                            Route::post('addShipmentPackage', [TabShipmentsController::class, 'addShipmentPackage'])
                                ->name('orders.detail.shipments.addShipmentPackage');

                            Route::prefix('/{shipmentPackageId}')->where(['shipmentPackageId' => '[0-9]+'])->group(function () {
                                Route::delete('', [TabShipmentsController::class, 'deleteShipmentPackage'])
                                    ->name('orders.detail.shipments.deleteShipmentPackage');

                                Route::prefix('/items')->group(function () {
                                    Route::post('', [TabShipmentsController::class, 'addShipmentPackageItems'])
                                        ->name('orders.detail.shipments.addShipmentPackageItems');

                                    Route::prefix('/{basketItemId}')->where(['basketItemId' => '[0-9]+'])->group(function () {
                                        Route::put('', [TabShipmentsController::class, 'editShipmentPackageItem'])
                                            ->name('orders.detail.shipments.editShipmentPackageItem');
                                        Route::delete('', [TabShipmentsController::class, 'deleteShipmentPackageItem'])
                                            ->name('orders.detail.shipments.deleteShipmentPackageItem');
                                    });
                                });
                            });
                        });
                    });
                });
            });

            Route::prefix('delivery')->group(function () {
                Route::get('{deliveryId}', [FlowDeliveryController::class, 'detail'])->where(['deliveryId' => '[0-9]+'])->name('orders.delivery');
                Route::put('editDelivery', [FlowDeliveryController::class, 'editDelivery'])->name('orders.delivery.editDelivery');
                Route::put('editShipment', [FlowDeliveryController::class, 'editShipment'])->name('orders.delivery.editShipment');
            });
        });

        Route::prefix('create')->namespace('Create')->group(function () {
            Route::get('', [OrderCreateController::class, 'create'])->name('orders.create');

            Route::post('products', [OrderCreateController::class, 'searchProducts'])->name('orders.searchProducts');
            Route::post('users', [OrderCreateController::class, 'searchCustomer'])->name('orders.searchCustomer');
            Route::post('order', [OrderCreateController::class, 'createOrder'])->name('orders.createOrder');
        });

        Route::prefix('cargos')->namespace('Cargo')->group(function () {
            Route::get('/', [CargoListController::class, 'index'])->name('cargo.list');
            Route::get('/page', [CargoListController::class, 'page'])->name('cargo.pagination');

            Route::prefix('/{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', [CargoDetailController::class, 'index'])->name('cargo.detail');
                Route::put('changeStatus', [CargoDetailController::class, 'changeStatus'])->name('cargo.changeStatus');
                Route::put('cancel', [CargoDetailController::class, 'cancel'])->name('cargo.cancel');
                Route::get('/unshipped-shipments', [CargoDetailController::class, 'getUnshippedShipments'])->name('cargo.unshippedShipments');

                Route::prefix('/shipments')->group(function () {
                    Route::post('addShipmentPackage', [CargoDetailController::class, 'addShipment2Cargo'])
                        ->name('cargo.addShipment2Cargo');

                    Route::prefix('/{shipmentId}')->where(['shipmentId' => '[0-9]+'])->group(function () {
                        Route::delete('', [CargoDetailController::class, 'deleteShipmentFromCargo'])
                            ->name('cargo.deleteShipmentFromCargo');
                    });
                });

                Route::prefix('courier-call')->group(function () {
                    Route::get('status', [CargoDetailController::class, 'checkCourierCallStatus'])->name('cargo.checkCourierCallStatus');
                    Route::post('', [CargoDetailController::class, 'createCourierCall'])->name('cargo.createCourierCall');
                    Route::put('cancel', [CargoDetailController::class, 'cancelCourierCall'])->name('cargo.cancelCourierCall');
                });
            });
        });

        Route::prefix('shipments')->namespace('Shipment')->group(function () {
            Route::get('', [ShipmentListController::class, 'index'])->name('shipment.list');
            Route::get('page', [ShipmentListController::class, 'page'])->name('shipment.pagination');
        });

        Route::prefix('directories')->namespace('Directory')->group(function () {
            Route::prefix('order-statuses')->group(function () {
                Route::get('', [OrderStatusListController::class, 'index'])->name('orderStatuses.list');
                Route::get('page', [OrderStatusListController::class, 'page'])->name('orderStatuses.pagination');
            });
            Route::prefix('order-return-reasons')->group(function () {
                Route::get('', [OrderReturnReasonListController::class, 'list'])->name('orderReturnReasons.list');
                Route::get('page', [OrderReturnReasonListController::class, 'page'])->name('orderReturnReasons.listPage');
                Route::post('save', [OrderReturnReasonListController::class, 'save'])->name('orderReturnReasons.save');
                Route::post('delete', [OrderReturnReasonListController::class, 'delete'])->name('orderReturnReasons.delete');
            });
        });
    });

    Route::prefix('baskets')->namespace('Basket')->group(function () {
        Route::get('', [BasketListController::class, 'index'])->name('baskets.list');
        Route::get('page', [BasketListController::class, 'page'])->name('baskets.pagination');

        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('', [BasketDetailController::class, 'detail'])->name('baskets.detail');
            Route::put('detail', [BasketDetailController::class, 'save'])->name('baskets.detail.save');
        });
    });

    Route::prefix('offers')->namespace('Product')->group(function () {
        Route::get('', [OfferListController::class, 'index'])->name('offers.list');
        Route::get('page', [OfferListController::class, 'page'])->name('offers.listPage');
        Route::post('find', [OfferListController::class, 'findOffers'])->name('offers.find');
        Route::post('', [OfferListController::class, 'createOffer'])->name('offers.create');
        Route::put('change-status', [OfferListController::class, 'changeSaleStatus'])->name('offers.change.saleStatus');
        Route::delete('', [OfferListController::class, 'deleteOffers'])->name('offers.delete');
        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('', [OfferDetailController::class, 'index'])->name('offers.detail');
            Route::get('stocks', [OfferDetailController::class, 'loadStocks'])->name('offers.stocks');
            Route::post('props', [ProductDetailController::class, 'saveOfferProps'])->name('offers.saveOfferProps');
            Route::put('', [OfferListController::class, 'editOffer'])->name('offers.edit');
        });
        Route::get('store-qty-info', [OfferListController::class, 'loadStoreAndQty'])->name('offers.storeAndQty');
        Route::get('validate-offer', [OfferListController::class, 'validateOffer'])->name('offers.validate');
    });

    Route::prefix('products')->namespace('Product')->group(function () {
        Route::get('', [ProductListController::class, 'index'])->name('products.list');
        Route::get('page', [ProductListController::class, 'page'])->name('products.listPage');

        Route::put('approval', [ProductListController::class, 'updateApprovalStatus'])->name('products.massApproval');
        Route::put('production', [ProductListController::class, 'updateProductionStatus'])->name('products.massProduction');
        Route::put('archive', [ProductListController::class, 'updateArchiveStatus'])->name('products.massArchive');
        Route::put('badges', [ProductListController::class, 'attachBadges'])->name('products.attachBadges');

        Route::prefix('properties')->group(function () {
            Route::get('', [PropertiesController::class, 'list'])->name('products.properties.list');
            Route::get('create', [PropertiesController::class, 'create'])->name('products.properties.create');
            Route::put('update', [PropertiesController::class, 'update'])->name('products.properties.update');
            Route::prefix('{id}')->group(function () {
                Route::get('', [PropertiesController::class, 'detail'])->name('products.properties.detail');
                Route::delete('', [PropertiesController::class, 'delete'])->name('products.properties.delete');
            });
        });

        Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::get('detailData', [ProductDetailController::class, 'detailData'])->name('products.detailData');
            Route::get('', [ProductDetailController::class, 'index'])->name('products.detail');
            Route::post('', [ProductDetailController::class, 'saveProduct'])->name('products.saveProduct');
            Route::post('props', [ProductDetailController::class, 'saveProps'])->name('products.saveProps');
            Route::post('image', [ProductDetailController::class, 'saveImage'])->name('products.saveImage');
            Route::post('imageDelete', [ProductDetailController::class, 'deleteImage'])->name('products.deleteImage');
            Route::post('imagesSort', [ProductDetailController::class, 'sortImages'])->name('products.sortImages');
            Route::put('ingredients', [ProductDetailController::class, 'saveIngredients'])->name('products.saveIngredients');
            Route::put('publicEvents', [ProductDetailController::class, 'savePublicEvents'])->name('products.savePublicEvents');
            Route::put('changeApproveStatus', [ProductDetailController::class, 'changeApproveStatus'])->name('products.changeApproveStatus');
            Route::put('reject', [ProductDetailController::class, 'reject'])->name('products.reject');

            Route::prefix('tips')->group(function () {
                Route::post('', [ProductDetailController::class, 'addTip'])->name('product.addTip');
                Route::post('{tipId}/update', [ProductDetailController::class, 'editTip'])->name('product.editTip');
                Route::post('{tipId}/delete', [ProductDetailController::class, 'deleteTip'])->name('product.deleteTip');
            });
        });

        Route::prefix('variant-groups')->namespace('VariantGroup')->group(function () {
            Route::get('', [VariantGroupListController::class, 'index'])->name('variantGroups.list');
            Route::post('', [VariantGroupListController::class, 'create'])->name('variantGroups.create');
            Route::delete('', [VariantGroupListController::class, 'delete'])->name('variantGroups.delete');
            Route::get('page', [VariantGroupListController::class, 'page'])->name('variantGroups.pagination');
            Route::post('byOffers', [VariantGroupListController::class, 'byOffers'])->name('variantGroups.byOffers');

            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', [VariantGroupDetailController::class, 'detail'])->name('variantGroups.detail');
                Route::put('', [VariantGroupDetailController::class, 'save'])->name('variantGroups.detail.save');

                Route::namespace('Detail')->group(function () {
                    Route::prefix('products')->group(function () {
                        Route::get('', [TabProductsController::class, 'load'])->name('variantGroups.detail.products.load');
                        Route::post('', [TabProductsController::class, 'add'])->name('variantGroups.detail.products.add');
                        Route::delete('', [TabProductsController::class, 'delete'])->name('variantGroups.detail.products.delete');
                        Route::prefix('{productId}')->where(['id' => '[0-9]+'])->group(function () {
                            Route::put('set-main', [TabProductsController::class, 'setMain'])->name('variantGroups.detail.products.setMain');
                        });
                    });

                    Route::prefix('properties')->group(function () {
                        Route::get('', [TabPropertiesController::class, 'load'])->name('variantGroups.detail.properties.load');
                        Route::delete('', [TabPropertiesController::class, 'delete'])->name('variantGroups.detail.properties.delete');
                        Route::prefix('{propertyId}')->where(['id' => '[0-9]+'])->group(function () {
                            Route::post('', [TabPropertiesController::class, 'add'])->name('variantGroups.detail.properties.add');
                        });
                    });
                });
            });
        });

        Route::prefix('import-export')->namespace('ImportExport')->group(function () {
            Route::get('export-by-product-ids', [ProductsExportController::class, 'exportByProductIds'])->name('products.exportByProductIds');
            Route::get('export-by-filters', [ProductsExportController::class, 'exportByFilters'])->name('products.exportByFilters');
        });
    });

    Route::prefix('brands')->namespace('Product')->group(function () {
        Route::get('', [BrandController::class, 'list'])->name('brand.list');
        Route::get('page', [BrandController::class, 'page'])->name('brand.listPage');
        Route::post('save', [BrandController::class, 'save'])->name('brand.save');
        Route::post('delete', [BrandController::class, 'delete'])->name('brand.delete');
    });

    Route::prefix('categories')->namespace('Product')->group(function () {
        Route::get('', [CategoryController::class, 'index'])->name('categories.list');
        Route::post('', [CategoryController::class, 'create'])->name('categories.create');
        Route::put('', [CategoryController::class, 'update'])->name('categories.update');
    });

    Route::prefix('content')->namespace('Content')->group(function () {
        Route::prefix('product-group')->namespace('ProductGroup')->group(function () {
            Route::get('/', [ProductGroupListController::class, 'indexPage'])->name('productGroup.listPage');
            Route::get('/{id}', [ProductGroupDetailController::class, 'updatePage'])->where(['id' => '[0-9]+'])->name('productGroup.updatePage');
            Route::get('/create', [ProductGroupDetailController::class, 'createPage'])->name('productGroup.createPage');
            Route::get('/page', [ProductGroupListController::class, 'page'])->name('productGroups.page');
            Route::put('/{id}', [ProductGroupDetailController::class, 'update'])->where(['id' => '[0-9]+'])->name('productGroup.update');
            Route::post('/', [ProductGroupDetailController::class, 'create'])->name('productGroup.create');
            Route::delete('/{id}', [ProductGroupDetailController::class, 'delete'])->where(['id' => '[0-9]+'])->name('productGroup.delete');
            Route::get('/filter', [ProductGroupDetailController::class, 'getFilters'])->name('productGroup.getFilters');
            Route::get('/filter-by-category', [ProductGroupDetailController::class, 'getFiltersByCategory'])->name('productGroup.getFiltersByCategory');
            Route::post('/product', [ProductGroupDetailController::class, 'getProducts'])->name('productGroup.getProducts');
            Route::post('/products-by-offers', [ProductGroupDetailController::class, 'getProductsByOffers'])->name('productGroup.getProductsByOffers');
        });

        Route::prefix('menu')->namespace('Menu')->group(function () {
            Route::get('/', [MenuListController::class, 'index'])->name('menu.list');
            Route::get('/{id}', [MenuDetailController::class, 'index'])->where(['id' => '[0-9]+'])->name('menu.detail');
            Route::put('/{id}', [MenuDetailController::class, 'update'])->where(['id' => '[0-9]+'])->name('menu.update');
        });

        Route::prefix('banner')->namespace('Banner')->group(function () {
            Route::get('/', [BannerListController::class, 'listPage'])->name('banner.listPage');
            Route::get('/{id}', [BannerDetailController::class, 'updatePage'])->where(['id' => '[0-9]+'])->name('banner.updatePage');
            Route::get('/create', [BannerDetailController::class, 'createPage'])->name('banner.createPage');

            Route::get('/page', [BannerListController::class, 'page'])->name('banner.page');
            Route::get('/widgetBanners', [BannerListController::class, 'widgetBanners'])->name('banner.widgetBanners');
            Route::get('/productGroupBanners', [BannerListController::class, 'productGroupBanners'])->name('banner.productGroupBanners');
            Route::post('/', [BannerDetailController::class, 'create'])->where(['id' => '[0-9]+'])->name('banner.create');
            Route::put('/{id}', [BannerDetailController::class, 'update'])->where(['id' => '[0-9]+'])->name('banner.update');
            Route::delete('/{id}', [BannerDetailController::class, 'delete'])->where(['id' => '[0-9]+'])->name('banner.delete');

            Route::get('/initialDate', [BannerDetailController::class, 'bannerInitialDate'])->name('banner.initialData');
        });

        Route::prefix('landing')->namespace('Landing')->group(function () {
            Route::get('/', [LandingListController::class, 'listPage'])->name('landing.listPage');
            Route::get('/{id}', [LandingDetailController::class, 'updatePage'])->where(['id' => '[0-9]+'])->name('landing.updatePage');
            Route::get('/create', [LandingDetailController::class, 'createPage'])->name('landing.createPage');

            Route::get('/page', [LandingListController::class, 'page'])->name('landing.page');
            Route::post('/', [LandingDetailController::class, 'create'])->name('landing.create');
            Route::put('/{id}', [LandingDetailController::class, 'update'])->where(['id' => '[0-9]+'])->name('landing.update');
            Route::delete('/{id}', [LandingDetailController::class, 'delete'])->where(['id' => '[0-9]+'])->name('landing.delete');
            Route::post('/landingCache', [LandingDetailController::class, 'landingCache'])->name('landing.landingCache');
        });

        Route::prefix('seo')->namespace('Seo')->group(function () {
            Route::get('/', [SeoController::class, 'listPage'])->name('seo.listPage');
            Route::get('/{id}', [SeoController::class, 'edit'])->where(['id' => '[0-9]+'])->name('seo.updatePage');

            Route::get('/page', [SeoController::class, 'page'])->name('seo.page');
            Route::put('/{id}', [SeoController::class, 'update'])->where(['id' => '[0-9]+'])->name('seo.update');
        });
        Route::prefix('redirect')->namespace('Redirect')->group(function () {
            Route::get('', [RedirectListController::class, 'index'])->name('redirect.list');
            Route::get('/page', [RedirectListController::class, 'page'])->name('redirect.page');
            Route::post('/import', [RedirectListController::class, 'import'])->name('redirect.import');

            Route::post('/', [RedirectDetailController::class, 'create'])->name('redirect.create');
            Route::put('/{id}', [RedirectDetailController::class, 'update'])->where(['id' => '[0-9]+'])->name('redirect.update');
            Route::delete('/{id}', [RedirectDetailController::class, 'delete'])->where(['id' => '[0-9]+'])->name('redirect.delete');
        });

        Route::prefix('contacts')->namespace('Contacts')->group(function () {
            Route::get('', [ContactsController::class, 'list'])->name('contacts.list');
            Route::post('add', [ContactsController::class, 'add'])->name('contacts.add');
            Route::put('edit', [ContactsController::class, 'edit'])->name('contacts.edit');
            Route::delete('remove', [ContactsController::class, 'remove'])->name('contacts.remove');
        });

        Route::prefix('product-badges')->namespace('ProductBadges')->group(function () {
            Route::get('', [ProductBadgesController::class, 'list'])->name('productBadges.list');
            Route::post('add', [ProductBadgesController::class, 'add'])->name('productBadges.add');
            Route::put('edit', [ProductBadgesController::class, 'edit'])->name('productBadges.edit');
            Route::put('reorder', [ProductBadgesController::class, 'reorder'])->name('productBadges.reorder');
            Route::delete('remove', [ProductBadgesController::class, 'remove'])->name('productBadges.remove');
        });

        Route::prefix('search-requests')->namespace('SearchRequest')->group(function () {
            Route::get('', [SearchRequestController::class, 'list'])->name('searchRequests.list');
            Route::post('create', [SearchRequestController::class, 'create'])->name('searchRequests.create');
            Route::put('update', [SearchRequestController::class, 'update'])->name('searchRequests.update');
            Route::put('reorder', [SearchRequestController::class, 'reorder'])->name('searchRequests.reorder');
            Route::delete('delete', [SearchRequestController::class, 'delete'])->name('searchRequests.delete');
        });

        Route::prefix('search-synonyms')->namespace('SearchSynonym')->group(function () {
            Route::get('', [SearchSynonymController::class, 'list'])->name('searchSynonyms.list');
            Route::get('page', [SearchSynonymController::class, 'page'])->name('searchSynonyms.page');
            Route::post('create', [SearchSynonymController::class, 'create'])->name('searchSynonyms.create');
            Route::put('update', [SearchSynonymController::class, 'update'])->name('searchSynonyms.update');
            Route::delete('delete', [SearchSynonymController::class, 'delete'])->name('searchSynonyms.delete');
        });

        Route::prefix('popular-brands')->namespace('PopularBrand')->group(function () {
            Route::get('', [PopularBrandController::class, 'list'])->name('popularBrands.list');
            Route::post('create', [PopularBrandController::class, 'create'])->name('popularBrands.create');
            Route::put('update', [PopularBrandController::class, 'update'])->name('popularBrands.update');
            Route::put('reorder', [PopularBrandController::class, 'reorder'])->name('popularBrands.reorder');
            Route::delete('delete', [PopularBrandController::class, 'delete'])->name('popularBrands.delete');
        });

        Route::prefix('popular-products')->namespace('PopularProduct')->group(function () {
            Route::get('', [PopularProductController::class, 'list'])->name('popularProducts.list');
            Route::post('create', [PopularProductController::class, 'create'])->name('popularProducts.create');
            Route::put('reorder', [PopularProductController::class, 'reorder'])->name('popularProducts.reorder');
            Route::delete('delete', [PopularProductController::class, 'delete'])->name('popularProducts.delete');
        });

        Route::prefix('categories')->namespace('Category')->group(function () {
            Route::get('', [FrequentCategoryController::class, 'index'])->name('frequentCategories.list');
            Route::put('', [FrequentCategoryController::class, 'editCategories'])->name('frequentCategories.edit');
        });
    });

    Route::prefix('stores')->namespace('Store')->group(function () {
        Route::prefix('merchant-stores')->group(function () {
            Route::get('/', [MerchantStoreController::class, 'index'])->name('merchantStore.list');
            Route::get('/create', [MerchantStoreController::class, 'createPage'])->name('merchantStore.add');

            Route::get('/{id}', [MerchantStoreController::class, 'detailPage'])
                ->where(['id' => '[0-9]+'])
                ->name('merchantStore.edit');

            Route::get('page', [MerchantStoreController::class, 'page'])->name('merchantStore.pagination');

            Route::post('', [MerchantStoreController::class, 'create'])
                ->where(['id' => '[0-9]+'])
                ->name('merchantStore.create');
            Route::put('/{id}', [MerchantStoreController::class, 'update'])
                ->where(['id' => '[0-9]+'])
                ->name('merchantStore.update');
            Route::delete('/{id}', [MerchantStoreController::class, 'delete'])
                ->where(['id' => '[0-9]+'])
                ->name('merchantStore.delete');
            Route::delete('', [MerchantStoreController::class, 'deleteArray'])
                ->where(['id' => '[0-9]+'])
                ->name('merchantStore.deleteArray');

            Route::prefix('working')->group(function () {
                Route::put('/{id}', [MerchantStoreController::class, 'updateWorking'])
                    ->where(['id' => '[0-9]+'])
                    ->name('merchantStore.updateWorking');
            });

            Route::prefix('contacts')->group(function () {
                Route::post('/{id}', [MerchantStoreController::class, 'createContact'])
                    ->name('merchantStore.createContact');
                Route::put('/{id}', [MerchantStoreController::class, 'updateContact'])
                    ->where(['id' => '[0-9]+'])
                    ->name('merchantStore.updateContact');
                Route::delete('/{id}', [MerchantStoreController::class, 'deleteContact'])
                    ->where(['id' => '[0-9]+'])
                    ->name('merchantStore.deleteContact');
            });

            Route::get('/pickup-times', [MerchantStoreController::class, 'pickupTime'])
                ->name('merchantStore.pickupTime');
            Route::put('/pickup-times', [MerchantStoreController::class, 'savePickupTime'])
                ->name('merchantStore.savePickupTime');
        });
    });

    Route::prefix('logistics')->namespace('Logistics')->group(function () {
        Route::prefix('delivery-services')->namespace('DeliveryService')->group(function () {
            Route::get('/', [DeliveryServiceListController::class, 'index'])->name('deliveryService.list');
            Route::get('page', [DeliveryServiceListController::class, 'page'])->name('deliveryService.pagination');

            Route::prefix('/{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', [DeliveryServiceDetailController::class, 'index'])->name('deliveryService.detail');
                Route::put('', [DeliveryServiceDetailController::class, 'save'])->name('deliveryService.detail.save');

                Route::namespace('Detail')->group(function () {
                    Route::prefix('settings')->group(function () {
                        Route::put('', [TabSettingsController::class, 'save'])->name('deliveryService.detail.settings.save');
                    });
                    Route::prefix('limitations')->group(function () {
                        Route::put('', [TabLimitationsController::class, 'save'])->name('deliveryService.detail.limitations.save');
                    });
                });
            });
        });

        Route::prefix('delivery-prices')->group(function () {
            Route::get('', [DeliveryPriceController::class, 'index'])->name('deliveryPrice.index');
            Route::put('delivery-price', [DeliveryPriceController::class, 'save'])->name('deliveryPrice.save');
        });

        Route::prefix('delivery-kpi')->group(function () {
            Route::get('', [DeliveryKpiController::class, 'index'])->name('deliveryKpi.index');

            Route::prefix('main')->group(function () {
                Route::get('', [DeliveryKpiController::class, 'getMain'])->name('deliveryKpi.main.get');
                Route::put('', [DeliveryKpiController::class, 'setMain'])->name('deliveryKpi.main.set');
            });

            Route::prefix('ct')->group(function () {
                Route::get('', [DeliveryKpiController::class, 'getCt'])->name('deliveryKpi.ct.get');
                Route::put('', [DeliveryKpiController::class, 'setCt'])->name('deliveryKpi.ct.set');
            });

            Route::prefix('ppt')->group(function () {
                Route::get('', [DeliveryKpiController::class, 'getPpt'])->name('deliveryKpi.ppt.get');
                Route::put('', [DeliveryKpiController::class, 'setPpt'])->name('deliveryKpi.ppt.set');
            });

            Route::prefix('pct')->group(function () {
                Route::get('', [DeliveryKpiController::class, 'getPct'])->name('deliveryKpi.pct.get');
                Route::put('', [DeliveryKpiController::class, 'setPct'])->name('deliveryKpi.pct.set');
            });
        });
    });

    Route::prefix('communications')->namespace('Communications')->group(function () {
        Route::prefix('statuses')->group(function () {
            Route::get('', [StatusController::class, 'index'])->name('communications.statuses.list');
            Route::post('', [StatusController::class, 'save'])->name('communications.statuses.save');
            Route::delete('{id}', [StatusController::class, 'delete'])->name('communications.statuses.delete');
        });
        Route::prefix('themes')->group(function () {
            Route::get('', [ThemeController::class, 'index'])->name('communications.themes.list');
            Route::post('', [ThemeController::class, 'save'])->name('communications.themes.save');
            Route::delete('{id}', [ThemeController::class, 'delete'])->name('communications.themes.delete');
        });
        Route::prefix('types')->group(function () {
            Route::get('', [TypeController::class, 'index'])->name('communications.types.list');
            Route::post('', [TypeController::class, 'save'])->name('communications.types.save');
            Route::delete('{id}', [TypeController::class, 'delete'])->name('communications.types.delete');
        });

        Route::prefix('chats')->group(function () {
            Route::get('unread', [ChatsController::class, 'unread'])->name('communications.chats.unread');
            Route::get('unread/count', [ChatsController::class, 'unreadCount'])->name('communications.chats.unread.count');
            Route::get('filter', [ChatsController::class, 'filter'])->name('communications.chats.filter');
            Route::put('read', [ChatsController::class, 'read'])->name('communications.chats.read');
            Route::post('send', [ChatsController::class, 'send'])->name('communications.chats.send');
            Route::post('create', [ChatsController::class, 'create'])->name('communications.chats.create');
            Route::post('update', [ChatsController::class, 'update'])->name('communications.chats.update');
            Route::put('update-chat-user', [ChatsController::class, 'updateChatUser'])->name('communications.chats.updateChatUser');
            Route::get('broadcast', [ChatsController::class, 'broadcast'])->name('communications.chats.broadcast');
            Route::get('unlink-messenger-chats', [ChatsController::class, 'unlinkMessengerChats'])->name('communications.chats.unlinkMessengerChats');
        });

        Route::get('channels', [ChannelController::class, 'channels'])->name('communications.channels.list');
    });

    Route::namespace('Customers')->group(function () {
        Route::get('professionals', [CustomerListController::class, 'listProfessional'])->name('professional.list');
        Route::get('referral-partners', [CustomerListController::class, 'listReferralPartner'])->name('referralPartner.list');

        Route::prefix('customers')->group(function () {
            Route::post('', [CustomerListController::class, 'create'])->name('customers.create');

            Route::get('filter', [CustomerListController::class, 'filter'])->name('customers.filter');

            Route::prefix('{id}')->where(['id' => '[0-9]+'])->group(function () {
                Route::get('', [CustomerDetailController::class, 'detail'])->name('customers.detail');
                Route::put('', [CustomerDetailController::class, 'save'])->name('customers.detail.save');
                Route::put('referral', [CustomerDetailController::class, 'referral'])->name('customers.detail.referral');
                Route::put('professional', [CustomerDetailController::class, 'professional'])->name('customers.detail.professional');
                Route::put('portfolios', [CustomerDetailController::class, 'putPortfolios'])->name('customers.detail.portfolio.save');
                Route::post('dial', [CustomerDetailController::class, 'dial'])->name('customers.detail.dial');
                Route::post('auth', [CustomerDetailController::class, 'auth'])->name('customers.detail.auth');
                Route::put('sendSettingPasswordLink', 'CustomerDetailController@sendSettingPasswordLink')->name('customers.detail.sendSettingPasswordLink');

                Route::namespace('Detail')->group(function () {
                    Route::prefix('main')->group(function () {
                        Route::get('', [CustomerTabMainController::class, 'load'])->name('customers.detail.main');
                        Route::delete('certificate/{certificate_id}', [CustomerTabMainController::class, 'deleteCertificate'])->name(
                            'customers.detail.main.certificate.delete'
                        );
                        Route::post('certificate/{file_id}', [CustomerTabMainController::class, 'createCertificate'])->name(
                            'customers.detail.main.certificate.create'
                        );
                        Route::delete('referralContract/{referral_contract_id}', [CustomerTabMainController::class, 'deleteReferralContract'])
                            ->name('customers.detail.main.referralContract.delete');
                        Route::post('referralContract/{file_id}', [CustomerTabMainController::class, 'createReferralContract'])
                            ->name('customers.detail.main.referralContract.create');
                    });
                    Route::prefix('preference')->group(function () {
                        Route::get('', [TabPreferenceController::class, 'load'])->name('customers.detail.preference');
                        Route::post('favorite/{product_id}', [TabPreferenceController::class, 'addFavoriteItem'])->name(
                            'customers.detail.preference.favorite.add'
                        );
                        Route::delete('favorite/{product_id}', [TabPreferenceController::class, 'deleteFavoriteItem'])->name(
                            'customers.detail.preference.favorite.delete'
                        );
                        Route::prefix('{type}')->group(function () {
                            Route::put('brands', [TabPreferenceController::class, 'putBrands'])->name('customers.detail.preference.brand.save');
                            Route::put('categories', [TabPreferenceController::class, 'putCategories'])->name('customers.detail.preference.category.save');
                        });
                    });

                    Route::prefix('newsletter')->group(function () {
                        Route::get('', [TabNewsletterController::class, 'load'])->name('customers.detail.newsletter');
                        Route::put('edit', [TabNewsletterController::class, 'edit'])->name('customers.detail.newsletter.edit');
                    });

                    Route::prefix('promo-product')->group(function () {
                        Route::get('', [TabPromoProductController::class, 'load'])->name('customers.detail.promoProduct');
                        Route::put('', [TabPromoProductController::class, 'save'])->name('customers.detail.promoProduct.save');
                        Route::get('export', [TabPromoProductController::class, 'export'])->name('customers.detail.promoProduct.export');
                    });
                    Route::prefix('promo-page')->group(function () {
                        Route::get('', [TabPromoPageController::class, 'load'])->name('customers.detail.promoPage');
                        Route::post('', [TabPromoPageController::class, 'add'])->name('customers.detail.promoPage.add');
                        Route::delete('', [TabPromoPageController::class, 'delete'])->name('customers.detail.promoPage.delete');
                    });
                    Route::prefix('order-referrer')->group(function () {
                        Route::get('', [TabOrderReferrerController::class, 'load'])->name('customers.detail.orderReferrer');
                        Route::get('excel', [TabOrderReferrerController::class, 'export'])->name('customers.detail.orderReferrer.export');
                        Route::delete('{history_id}', [TabOrderReferrerController::class, 'delete'])->name('customers.detail.orderReferrer.delete');
                    });
                    Route::prefix('billing')->group(function () {
                        Route::get('', [CustomerTabBillingController::class, 'load'])->name('customers.detail.billing');
                        Route::post('correct', [CustomerTabBillingController::class, 'correct'])->name('customers.detail.billing.correct');
                    });
                    Route::prefix('documents')->group(function () {
                        Route::get('', [TabDocumentController::class, 'load'])->name('customers.detail.document');
                        Route::get('export', [TabDocumentController::class, 'export'])->name('customers.detail.document.export');
                        Route::prefix('{document_id}')->group(function () {
                            Route::post('send', [TabDocumentController::class, 'sendEmail'])->name('customers.detail.document.send');
                            Route::delete('delete', [TabDocumentController::class, 'deleteDocument'])->name('customers.detail.document.delete');
                        });

                        Route::post('', [TabDocumentController::class, 'createDocument'])->name('customers.detail.document.create');
                    });
                    Route::prefix('bonuses')->group(function () {
                        Route::get('', [TabBonusController::class, 'load'])->name('customers.detail.bonuses');
                        Route::post('', [TabBonusController::class, 'add'])->name('customers.detail.bonus.add');
                    });
                    Route::get('order', [CustomerTabOrderController::class, 'load'])->name('customers.detail.order');
                    Route::get('promocodes', [TabPromocodesController::class, 'load'])->name('customers.detail.promocodes');
                    Route::prefix('reviews')->group(function () {
                        Route::get('data', [TabReviewsController::class, 'load'])->name('customers.detail.reviews.data');
                        Route::get('page', [TabReviewsController::class, 'page'])->name('customers.detail.reviews.pagination');
                    });
                });
            });

            Route::prefix('activities')->group(function () {
                Route::get('', [ActivitiesController::class, 'list'])->name('customers.activities');
                Route::post('', [ActivitiesController::class, 'save'])->name('customers.activities.save');
            });

            Route::prefix('whitelist')->group(function () {
                Route::get('', [CustomerWhitelistController::class, 'index'])->name('customers.whitelist');
                Route::post('import', [CustomerWhitelistController::class, 'import'])->name('customers.whitelist.import');
                Route::get('export', [CustomerWhitelistController::class, 'export'])->name('customers.whitelist.export');
            });
        });
    });

    Route::prefix('referral')->namespace('Referral')->group(function () {
        Route::prefix('levels')->group(function () {
            Route::get('', [LevelsController::class, 'list'])->name('referral.levels');
            Route::prefix('{level_id}')->group(function () {
                Route::post('', [LevelsController::class, 'detail'])->name('referral.levels.detail');
                Route::put('', [LevelsController::class, 'putLevel'])->name('referral.levels.save');
                Route::prefix('commission')->group(function () {
                    Route::put('', [LevelsController::class, 'putCommission'])->name('referral.levels.commission.save');
                    Route::delete('', [LevelsController::class, 'removeCommission'])->name('referral.levels.commission.remove');
                });
                Route::prefix('special-commission')->group(function () {
                    Route::put('', [LevelsController::class, 'putSpecialCommission'])->name('referral.levels.special-commission.save');
                    Route::delete('', [LevelsController::class, 'removeSpecialCommission'])->name('referral.levels.special-commission.remove');
                });
            });
        });

        Route::prefix('promo-products')->group(function () {
            Route::get('', [MassPromoProductsController::class, 'list'])->name('referral.promo-products.list');
            Route::put('edit', [MassPromoProductsController::class, 'editProduct'])->name('referral.promo-products.edit');
            Route::put('attach', [MassPromoProductsController::class, 'attachProduct'])->name('referral.promo-products.attach');
            Route::delete('', [MassPromoProductsController::class, 'removeProduct'])->name('referral.promo-products.delete');
        });

        Route::prefix('options')->group(function () {
            Route::get('', [OptionsController::class, 'index'])->name('referral.options');
            Route::put('', [OptionsController::class, 'save'])->name('referral.options.save');
        });
    });

    Route::prefix('public-events')->namespace('PublicEvent')->group(function () {
        Route::get('is-code-unique', [PublicEventDetailController::class, 'isCodeUnique'])->name('public-event.isCodeUnique');
        Route::post('save', [PublicEventDetailController::class, 'save'])->name('public-event.save');

        Route::prefix('organizers')->group(function () {
            Route::get('', [OrganizerController::class, 'list'])->name('public-event.organizers.list');
            Route::get('page', [OrganizerController::class, 'page'])->name('public-event.organizers.page');
            Route::post('save', [OrganizerController::class, 'save'])->name('public-event.organizers.save');
            Route::post('delete', [OrganizerController::class, 'delete'])->name('public-event.organizers.delete');
        });

        Route::prefix('specialties')->group(function () {
            Route::get('', [SpecialtyController::class, 'list'])->name('public-event.specialties.list');
            Route::get('page', [SpecialtyController::class, 'page'])->name('public-event.specialties.page');
            Route::post('save', [SpecialtyController::class, 'save'])->name('public-event.specialties.save');
            Route::post('delete', [SpecialtyController::class, 'delete'])->name('public-event.specialties.delete');
            Route::get('{event_id}', [SpecialtyController::class, 'getByEvent'])->name('public-event.specialties.getSpecialties');
            Route::post('{event_id}', [SpecialtyController::class, 'attachEvent'])->name('public-event.specialties.attachSpecialty');
            Route::delete('{event_id}', [SpecialtyController::class, 'detachEvent'])->name('public-event.specialties.detachSpecialty');
        });

        Route::prefix('types')->group(function () {
            Route::get('', [EventTypeController::class, 'list'])->name('public-event.types.list');
            Route::get('page', [EventTypeController::class, 'page'])->name('public-event.types.page');
            Route::post('save', [EventTypeController::class, 'save'])->name('public-event.types.save');
            Route::post('delete', [EventTypeController::class, 'delete'])->name('public-event.types.delete');
        });

        Route::prefix('speakers')->group(function () {
            Route::get('', [SpeakerController::class, 'list'])->name('public-event.speakers.list');
            Route::get('page', [SpeakerController::class, 'page'])->name('public-event.speakers.page');
            Route::get('fullPage', [SpeakerController::class, 'fullPage'])->name('public-event.speakers.fullPage');
            Route::post('save', [SpeakerController::class, 'save'])->name('public-event.speakers.save');
            Route::post('delete', [SpeakerController::class, 'delete'])->name('public-event.speakers.delete');
            Route::get('{stage_id}', [SpeakerController::class, 'getByStage'])->name('public-event.sprint-stage.getSpeakers');
            Route::post('{stage_id}', [SpeakerController::class, 'attachStage'])->name('public-event.sprint-stage.attachSpeaker');
            Route::delete('{stage_id}', [SpeakerController::class, 'detachStage'])->name('public-event.sprint-stage.detachSpeaker');
        });

        Route::prefix('ticket-types')->group(function () {
            Route::get('', [PublicEventTicketTypeController::class, 'list'])->name('public-event.ticket-types.list');
            Route::get('page', [PublicEventTicketTypeController::class, 'page'])->name('public-event.ticket-types.page');
            //Route::get('{sprint_id}', [PublicEventTicketTypeController::class, 'getBySprint'])->name('public-event.sprint.getTicketTypes');
            Route::post('save', [PublicEventTicketTypeController::class, 'save'])->name('public-event.ticket-types.save');
            Route::post('delete', [PublicEventTicketTypeController::class, 'delete'])->name('public-event.ticket-types.delete');
            //Route::post('{sprint_id}', [PublicEventTicketTypeController::class, 'createBySprint'])->name('public-event.sprint.createTicketType');
            Route::post('{stage_id}', [PublicEventTicketTypeController::class, 'attachStage'])->name('public-event.ticket-types.attachStage');
            Route::delete('{stage_id}', [PublicEventTicketTypeController::class, 'detachStage'])->name('public-event.ticket-types.detachStage');
        });

        Route::prefix('sprint-stages')->group(function () {
            Route::get('', [PublicEventSprintStageController::class, 'list'])->name('public-event.sprint-stages.list');
            Route::get('page', [PublicEventSprintStageController::class, 'page'])->name('public-event.sprint-stages.page');
            // Route::get('{sprint_id}', [PublicEventSprintStageController::class, 'getBySprint'])->name('public-event.sprint.getSprintStages');
            Route::post('save', [PublicEventSprintStageController::class, 'save'])->name('public-event.sprint-stages.save');
            Route::post('delete', [PublicEventSprintStageController::class, 'delete'])->name('public-event.sprint-stages.delete');
            // Route::post('{sprint_id}', [PublicEventSprintStageController::class, 'createBySprint'])->name('public-event.sprint.createSprintStage');
            Route::post('{type_id}', [PublicEventTicketTypeController::class, 'attachType'])->name('public-event.sprint-stages.attachType');
            Route::delete('{type_id}', [PublicEventTicketTypeController::class, 'detachType'])->name('public-event.sprint-stages.detachType');
        });

        Route::prefix('sprint-documents')->group(function () {
            Route::get('', [PublicEventSprintDocumentController::class, 'list'])->name('public-event.sprint-documents.list');
            Route::get('page', [PublicEventSprintDocumentController::class, 'page'])->name('public-event.sprint-documents.page');
            Route::post('save', [PublicEventSprintDocumentController::class, 'save'])->name('public-event.sprint-documents.save');
            Route::post('delete', [PublicEventSprintDocumentController::class, 'delete'])->name('public-event.sprint-documents.delete');
            Route::get('{sprint_id}', [PublicEventSprintDocumentController::class, 'getBySprint'])->name('public-event.sprint-documents.getBySprint');
        });

        Route::prefix('professions')->group(function () {
            Route::get('', [PublicEventProfessionController::class, 'list'])->name('public-event.professions.list');
            Route::get('names', [PublicEventProfessionController::class, 'names'])->name('public-event.professions.names');
            Route::get('page', [PublicEventProfessionController::class, 'page'])->name('public-event.professions.page');
            Route::get('{event_id}', [PublicEventProfessionController::class, 'getByEvent'])->name('public-event.event.getProfessions');
            Route::post('save', [PublicEventProfessionController::class, 'save'])->name('public-event.professions.save');
            Route::post('delete', [PublicEventProfessionController::class, 'delete'])->name('public-event.professions.delete');
            Route::post('{event_id}', [PublicEventProfessionController::class, 'createByEvent'])->name('public-event.event.createProfession');
        });

        Route::prefix('places')->group(function () {
            Route::get('', [PlaceController::class, 'list'])->name('public-event.places.list');
            Route::get('page', [PlaceController::class, 'page'])->name('public-event.places.page');
            Route::get('fullList', [PlaceController::class, 'fullList'])->name('public-event.places.fullList');
            Route::post('save', [PlaceController::class, 'save'])->name('public-event.places.save');
            Route::post('delete', [PlaceController::class, 'delete'])->name('public-event.places.delete');
            Route::post('media', [PlaceController::class, 'media'])->name('public-event.places.media');
        });

        Route::prefix('media')->group(function () {
            Route::get('fullList', [MediaController::class, 'fullList'])->name('public-event.media.fullList');
            Route::post('save', [MediaController::class, 'save'])->name('public-event.media.save');
            Route::post('delete', [MediaController::class, 'delete'])->name('public-event.media.delete');
        });

        Route::prefix('sprint-sell-status')->group(function () {
            Route::post('save', [PublicEventSprintSellStatusController::class, 'save'])->name('public-event.sprint-sell-status.save');
            Route::post('delete', [PublicEventSprintSellStatusController::class, 'delete'])->name('public-event.sprint-sell-status.delete');
        });

        Route::prefix('sprints')->group(function () {
            Route::get('', [PublicEventSprintController::class, 'list'])->name('public-event.sprints.list');
            Route::get('page', [PublicEventSprintController::class, 'page'])->name('public-event.sprints.page');
            Route::post('save', [PublicEventSprintController::class, 'save'])->name('public-event.sprints.save');
            Route::post('delete', [PublicEventSprintController::class, 'delete'])->name('public-event.sprints.delete');
        });

        Route::get('statuses', [PublicEventStatusController::class, 'index'])->name('public-event.statuses.list');
        Route::get('event-statuses', [PublicEventStatusController::class, 'event'])->name('public-event.event-statuses.list');
        Route::get('list', [PublicEventListController::class, 'load'])->name('public-event.fullList');

        Route::prefix('{event_id}')->group(function () {
            Route::post('add-organizer-by-id', [PublicEventDetailController::class, 'addOrganizerById'])->name('public-event.addOrganizerById');
            Route::post('add-organizer-by-value', [PublicEventDetailController::class, 'addOrganizerByValue'])->name('public-event.addOrganizerByValue');

            Route::post('save-media', [PublicEventDetailController::class, 'saveMedia'])->name('public-event.saveMedia');
            Route::post('delete-media', [PublicEventDetailController::class, 'deleteMedia'])->name('public-event.deleteMedia');

            Route::get('recommendations', [PublicEventDetailController::class, 'recommendations'])->name('public-event.recommendations');
            Route::post('recommendation/{recommendation_id}', [PublicEventDetailController::class, 'attachRecommendation'])->name(
                'public-event.attachRecommendation'
            );
            Route::delete('recommendation/{recommendation_id}', [PublicEventDetailController::class, 'detachRecommendation'])->name(
                'public-event.detachRecommendation'
            );

            Route::prefix('sprints')->group(function () {
                Route::get('', [PublicEventDetailController::class, 'getSprints'])->name('public-event.getSprints');
                Route::post('', [PublicEventDetailController::class, 'createSprint'])->name('public-event.createSprint');
                Route::post('delete', [PublicEventDetailController::class, 'deleteSprint'])->name('public-event.deleteSprint');
            });

            Route::get('orders', [PublicEventOrdersController::class, 'getList'])->name('public-event.orders.list');
            Route::get('tickets', [PublicEventTicketsController::class, 'getList'])->name('public-event.tickets.list');
            Route::get('tickets/file', [PublicEventTicketsController::class, 'getFile'])->name('public-event.tickets.file');
            Route::post('tickets/comment', [PublicEventTicketsController::class, 'editComment'])->name('public-event.tickets.editComment');

            Route::get('load', [PublicEventDetailController::class, 'load'])->name('public-event.load');
            Route::get('', [PublicEventDetailController::class, 'index'])->name('public-event.detail');
        });


        Route::get('', [PublicEventListController::class, 'list'])->name('public-event.list');
        Route::get('list/page', [PublicEventListController::class, 'page'])->name('public-event.list.page');
    });

    Route::prefix('communications/service-notifications')->namespace('ServiceNotification')->group(function () {
        Route::prefix('')->group(function () {
            Route::get('', [ServiceNotificationController::class, 'page'])->name('communications.service-notification.list');
            Route::get('list', [ServiceNotificationController::class, 'list'])->name('communications.service-notification.page');
            Route::get('{id}/templates', [TemplateController::class, 'listNotification'])->name(
                'communications.service-notification.template.listNotification'
            );
            Route::get('{id}/templatesReload', [TemplateController::class, 'pageNotification'])->name('communications.service-notification.template.reload');
            Route::post('save', [ServiceNotificationController::class, 'save'])->name('communications.service-notification.save');
            Route::post('delete', [ServiceNotificationController::class, 'delete'])->name('communications.service-notification.delete');
            Route::post('send', [ServiceNotificationController::class, 'send'])->name('communications.service-notification.send');
        });

        Route::prefix('variables')->group(function () {
            Route::get('', [VariableController::class, 'page'])->name('communications.variable.list');
            Route::get('list', [VariableController::class, 'list'])->name('communications.variable.page');
            Route::post('save', [VariableController::class, 'save'])->name('communications.variable.save');
            Route::post('delete', [VariableController::class, 'delete'])->name('communications.variable.delete');
        });

        Route::prefix('templates')->group(function () {
            Route::get('list', [TemplateController::class, 'list'])->name('communications.service-notification.template.fullList');
            Route::post('save', [TemplateController::class, 'save'])->name('communications.service-notification.template.save');
            Route::post('delete', [TemplateController::class, 'delete'])->name('communications.service-notification.template.delete');
        });

        Route::prefix('system-alerts')->group(function () {
            Route::post('save', [SystemAlertController::class, 'save'])->name('communications.service-notification.system-alert.save');
            Route::post('delete', [SystemAlertController::class, 'delete'])->name('communications.service-notification.system-alert.delete');
            Route::get('{service_notification_id}', [SystemAlertController::class, 'pageNotification'])->name(
                'communications.service-notification.system-alert.page'
            );
        });
    });

    Route::prefix('organizers')->namespace('PublicEvent')->group(function () {
        Route::get('available', [PublicEventDetailController::class, 'availableOrganizers'])->name('public-event.availableOrganizers');
    });
});
