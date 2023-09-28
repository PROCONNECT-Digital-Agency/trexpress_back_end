<?php

use App\Http\Controllers\API\v1\Auth\LoginController;
use App\Http\Controllers\API\v1\Auth\RegisterController;
use App\Http\Controllers\API\v1\Auth\VerifyAuthController;
use App\Http\Controllers\API\v1\Dashboard\Admin;
use App\Http\Controllers\API\v1\Dashboard\Seller;
use App\Http\Controllers\API\v1\Dashboard\User;
use App\Http\Controllers\API\v1\Dashboard\Deliveryman;
use App\Http\Controllers\API\v1\Dashboard\Payment;
use App\Http\Controllers\API\v1\PushNotificationController;
use App\Http\Controllers\API\v1\Rest;
use App\Http\Controllers\API\v1\GalleryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'middleware' => ['localization']], function (){
    // Methods without AuthCheck
    Route::post('/auth/register', [RegisterController::class, 'register'])->middleware('sessions');
    Route::post('/auth/login', [LoginController::class, 'login'])->middleware('sessions');
    Route::post('/auth/logout', [LoginController::class, 'logout'])->middleware('sessions');
    Route::post('/auth/verify/phone', [VerifyAuthController::class, 'verifyPhone'])->middleware('sessions');
    Route::post('/auth/after-verify', [VerifyAuthController::class, 'afterVerifyEmail'])->middleware('sessions');

    Route::post('/auth/forgot/password', [LoginController::class, 'forgetPassword'])->middleware('sessions');
    Route::post('/auth/forgot/password/confirm', [LoginController::class, 'forgetPasswordVerify'])->middleware('sessions');

   // Route::get('/login/{provider}', [LoginController::class,'redirectToProvider']);
    Route::post('/auth/{provider}/callback', [LoginController::class,'handleProviderCallback']);

    Route::group(['prefix' => 'install'], function () {
        Route::get('/init/check', [Rest\InstallController::class, 'checkInitFile']);
        Route::post('/init/set', [Rest\InstallController::class, 'setInitFile']);
        Route::post('/database/update', [Rest\InstallController::class, 'setDatabase']);
        Route::post('/admin/create', [Rest\InstallController::class, 'createAdmin']);
        Route::post('/migration/run', [Rest\InstallController::class, 'migrationRun']);
        Route::post('/check/licence', [Rest\InstallController::class, 'licenceCredentials']);
        Route::post('/currency/create', [Rest\InstallController::class, 'createCurrency']);
        Route::post('/languages/create', [Rest\InstallController::class, 'createLanguage']);
    });

    Route::group(['prefix' => 'rest'], function () {

        /* Languages */
        Route::get('translations/paginate', [Rest\SettingController::class, 'translationsPaginate']);
        Route::get('settings', [Rest\SettingController::class, 'settingsInfo']);
        Route::get('system/information', [Rest\SettingController::class, 'systemInformation']);

        /* Languages */
        Route::get('languages/default', [Rest\LanguageController::class, 'default']);
        Route::get('languages/active', [Rest\LanguageController::class, 'active']);
        Route::get('languages/{id}', [Rest\LanguageController::class, 'show']);
        Route::get('languages', [Rest\LanguageController::class, 'index']);

        /* Currencies */
        Route::get('currencies', [Rest\CurrencyController::class, 'index']);
        Route::get('currencies/active', [Rest\CurrencyController::class, 'active']);

        /* CouponCheck */
        Route::post('coupons/check', Rest\CouponController::class);
        Route::post('cashback/check', [Rest\ProductController::class, 'checkCashback']);

        /* Products */
        Route::post('products/review/{uuid}', [Rest\ProductController::class, 'addProductReview']);
        Route::get('products/calculate', [Rest\ProductController::class, 'productsCalculate']);
        Route::get('products/paginate', [Rest\ProductController::class, 'paginate']);
        Route::get('products/brand/{id}', [Rest\ProductController::class, 'productsByBrand']);
        Route::get('products/shop/{uuid}', [Rest\ProductController::class, 'productsByShopUuid']);
        Route::get('products/category/{uuid}', [Rest\ProductController::class, 'productsByCategoryUuid']);
        Route::get('products/search', [Rest\ProductController::class, 'productsSearch']);
        Route::get('products/most-sold', [Rest\ProductController::class, 'mostSoldProducts']);
        Route::get('products/discount', [Rest\ProductController::class, 'discountProducts']);
        Route::get('products/ids', [Rest\ProductController::class, 'productsByIDs']);
        Route::get('products/{uuid}', [Rest\ProductController::class, 'show']);
        Route::get('products/brands/{id}', [Rest\ProductController::class, 'getByBrandId']);

        /* Categories */
        Route::get('categories/paginate', [Rest\CategoryController::class, 'paginate']);
        Route::get('categories/search', [Rest\CategoryController::class, 'categoriesSearch']);
        Route::get('categories/{uuid}', [Rest\CategoryController::class, 'show']);
        Route::get('categories/product/paginate', [Rest\CategoryController::class, 'products']);

        /* Brands */
        Route::get('brands/paginate', [Rest\BrandController::class, 'paginate']);
        Route::get('brands/{id}', [Rest\BrandController::class, 'show']);

        /* Shops */
        Route::get('shops/paginate', [Rest\ShopController::class, 'paginate']);
        Route::get('shops/nearby', [Rest\ShopController::class, 'nearbyShops']);
        Route::get('shops/search', [Rest\ShopController::class, 'shopsSearch']);
        Route::get('shops/deliveries', [Rest\ShopController::class, 'shopsDeliveryByIDs']);
        Route::get('shops/{uuid}', [Rest\ShopController::class, 'show']);
        Route::get('shops', [Rest\ShopController::class, 'shopsByIDs']);

        /* Banners */
        Route::get('banners/paginate', [Rest\BannerController::class, 'paginate']);
        Route::post('banners/{id}/liked', [Rest\BannerController::class, 'likedBanner'])->middleware('sanctum.check');
        Route::get('banners/{id}/products', [Rest\BannerController::class, 'bannerProducts']);
        Route::get('banners/{id}', [Rest\BannerController::class, 'show']);

        /* FAQS */
        Route::get('faqs/paginate', [Rest\FAQController::class, 'paginate']);

        /* Payments */
        Route::get('payments', [Rest\PaymentController::class, 'index']);
        Route::get('payments/{id}', [Rest\PaymentController::class, 'show']);

        /* Blogs */
        Route::get('blogs/paginate', [Rest\BlogController::class, 'paginate']);
        Route::get('blogs/{uuid}', [Rest\BlogController::class, 'show']);

        Route::get('term', [Rest\FAQController::class, 'term']);

        Route::get('policy', [Rest\FAQController::class, 'policy']);

        Route::get('filter', [Rest\FilterController::class, 'productFilter']);

        Route::get('delivery',[Rest\DeliveryController::class,'paginate']);

        Route::get('delivery/{id}',[Rest\DeliveryController::class,'show']);

        Route::get('search',[Rest\SearchController::class,'searchAll']);

        Route::get('extra-group',[Rest\ExtraController::class,'extrasGroupList']);

        Route::get('webhook-payment',[Rest\WebHookController::class,'webhook']);

    });

    Route::group(['prefix' => 'payments', 'middleware' => ['sanctum.check'], 'as' => 'payment.'], function (){

        /* Transactions */
        Route::post('{type}/{id}/transactions', [Payment\TransactionController::class, 'store']);
        Route::put('{type}/{id}/transactions', [Payment\TransactionController::class, 'updateStatus']);
    });

    Route::post('payments/atmos-token', [AtmosController::class, 'token']);
    Route::post('payments/card', [AtmosController::class, 'card']);

    Route::group(['prefix' => 'dashboard'], function () {
        /* Galleries */
        Route::get('/galleries/paginate', [GalleryController::class, 'paginate']);
        Route::get('/galleries/storage/files', [GalleryController::class, 'getStorageFiles']);
        Route::post('/galleries/storage/files/delete', [GalleryController::class, 'deleteStorageFile']);
        Route::post('/galleries', [GalleryController::class, 'store']);

        Route::post('user/profile/password/update', [User\ProfileController::class, 'passwordUpdate']);

        // Notifications
        Route::apiResource('notifications',PushNotificationController::class)
            ->only(['index', 'show']);
        Route::post('notifications/{id}/read-at',   [PushNotificationController::class, 'readAt']);
        Route::post('notifications/read-all',       [PushNotificationController::class, 'readAll']);

        // USER BLOCK
        Route::group(['prefix' => 'user', 'middleware' => ['sanctum.check'], 'as' => 'user.'], function () {
            Route::get('profile/show', [User\ProfileController::class, 'show']);
            Route::put('profile/update', [User\ProfileController::class, 'update']);
            Route::delete('profile/delete', [User\ProfileController::class, 'delete']);
            Route::post('profile/firebase/token/update', [User\ProfileController::class, 'fireBaseTokenUpdate']);
            Route::get('profile/liked/looks', [User\ProfileController::class, 'likedLooks']);
            Route::get('profile/notifications-statistic',       [User\ProfileController::class, 'notificationStatistic']);

            Route::post('addresses/default/{id}', [User\AddressController::class, 'setDefaultAddress']);
            Route::post('addresses/active/{id}', [User\AddressController::class, 'setActiveAddress']);
            Route::apiResource('addresses', User\AddressController::class);
            Route::post('orders/review/{id}', [User\OrderController::class, 'addOrderReview']);
            Route::get('orders/paginate', [User\OrderController::class, 'paginate']);
            Route::post('orders/{id}/status/change', [User\OrderController::class, 'orderStatusChange']);
            Route::post('orders/create', [User\OrderController::class,'store']);
            Route::apiResource('orders', User\OrderController::class);

            Route::get('/invites/paginate', [User\InviteController::class, 'paginate']);
            Route::post('/shop/invitation/{uuid}/link', [User\InviteController::class, 'create']);

            Route::get('/point/histories', [User\WalletController::class, 'pointHistories']);

            Route::get('/wallet/histories', [User\WalletController::class, 'walletHistories']);
            Route::post('/wallet/withdraw', [User\WalletController::class, 'store']);
            Route::post('/wallet/history/{uuid}/status/change', [User\WalletController::class, 'changeStatus']);

            /* Transaction */
            Route::get('transactions/paginate', [User\TransactionController::class, 'paginate']);
            Route::get('transactions/{id}', [User\TransactionController::class, 'show']);

            /* Shop */
            Route::post('shops', [Seller\ShopController::class, 'shopCreate']);
            Route::get('shops', [Seller\ShopController::class, 'shopShow']);
            Route::put('shops', [Seller\ShopController::class, 'shopUpdate']);

            /* Ticket */
            Route::get('tickets/paginate', [User\TicketController::class, 'paginate']);
            Route::apiResource('tickets', User\TicketController::class);

            /* Export */
            Route::get('export/order/{id}/pdf', [User\ExportController::class, 'orderExportPDF']);
            /* Add review to delivery man */
            Route::post('deliveryman/review/{id}', [User\DeliveryManController::class, 'addReviewToDeliveryMan']);

            Route::get('point-deliveries', [User\PointDeliveryController::class, 'index']);
            Route::get('point-deliveries/{id}', [User\PointDeliveryController::class, 'show']);

            Route::apiResource('parcel-orders',       User\ParcelOrderController::class);
            Route::post('parcel-orders/{id}/status/change',      [User\ParcelOrderController::class, 'orderStatusChange']);

            Route::post('update/notifications',                 [User\ProfileController::class, 'notificationsUpdate']);
            Route::get('notifications',                         [User\ProfileController::class, 'notifications']);

        });

        // DELIVERYMAN BLOCK
        Route::group(['prefix' => 'deliveryman', 'middleware' => ['sanctum.check', 'role:deliveryman'], 'as' => 'deliveryman.'], function () {
            Route::get('orders/paginate', [Deliveryman\OrderController::class, 'paginate']);
            Route::get('orders/{id}', [Deliveryman\OrderController::class, 'show']);
            Route::post('order/details/{id}/status/update', [Deliveryman\OrderController::class, 'statusChange']);

            Route::get('statistics/count', [Deliveryman\DashboardController::class, 'countStatistics']);

            Route::post('settings', [Deliveryman\DeliveryManSettingController::class, 'store']);
            Route::post('settings/location', [Deliveryman\DeliveryManSettingController::class, 'updateLocation']);
            Route::post('settings/online', [Deliveryman\DeliveryManSettingController::class, 'online']);
            Route::get('settings', [Deliveryman\DeliveryManSettingController::class, 'show']);

            /* Report Orders */
            Route::get('order/report', [Deliveryman\OrderController::class, 'report']);

            Route::get('parcel-orders/paginate',            [Deliveryman\ParcelOrderController::class,  'paginate']);
            Route::post('parcel-orders/{id}/status/update', [Deliveryman\ParcelOrderController::class,  'orderStatusUpdate']);
            Route::post('parcel-order/{id}/current',        [Deliveryman\ParcelOrderController::class,  'setCurrent']);
            Route::post('parcel-order/{id}/attach/me',      [Deliveryman\ParcelOrderController::class,  'orderDeliverymanUpdate']);
        });

        // SELLER BLOCK
        Route::group(['prefix' => 'seller', 'middleware' => ['sanctum.check', 'role:seller|moderator'], 'as' => 'seller.'], function () {
            /* Dashboard */
            Route::get('statistics/count', [Seller\DashboardController::class, 'countStatistics']);
            Route::get('statistics/sum', [Seller\DashboardController::class, 'sumStatistics']);
            Route::get('statistics/customer/top', [Seller\DashboardController::class, 'topCustomersStatistics']);
            Route::get('statistics/products/top', [Seller\DashboardController::class, 'topProductsStatistics']);
            Route::get('statistics/orders/sales', [Seller\DashboardController::class, 'ordersSalesStatistics']);
            Route::get('statistics/orders/count', [Seller\DashboardController::class, 'ordersCountStatistics']);

            /* Extras */
            Route::get('extras/groups', [Seller\ExtraController::class, 'extraGroupList']);
            Route::get('extras/groups/{id}', [Seller\ExtraController::class, 'extraGroupDetails']);
            Route::get('extras/group/{id}/values', [Seller\ExtraController::class, 'extraValueList']);
            Route::get('extras/value/{id}', [Seller\ExtraController::class, 'extraValueDetails']);
            Route::get('extras/value', [Seller\ExtraController::class, 'extraValue']);

            /* Extras */
            Route::get('units/paginate', [Seller\UnitController::class, 'paginate']);
            Route::get('units/{id}', [Seller\UnitController::class, 'show']);

            /* Seller Shop */
            Route::get('shops', [Seller\ShopController::class, 'shopShow']);
            Route::put('shops', [Seller\ShopController::class, 'shopUpdate']);
            Route::post('shops/visibility/status', [Seller\ShopController::class, 'setVisibilityStatus']);
            Route::post('shops/working/status', [Seller\ShopController::class, 'setWorkingStatus']);

            /* Seller Product */
            Route::get('products/out-of-stock', [Seller\ProductController::class, 'outOfStock']);
            Route::post('products/import', [Seller\ProductController::class, 'fileImport']);
            Route::get('products/export', [Seller\ProductController::class, 'fileExport']);
            Route::get('products/paginate', [Seller\ProductController::class, 'paginate']);
            Route::get('products/search', [Seller\ProductController::class, 'productsSearch']);
            Route::post('products/{uuid}/stocks', [Seller\ProductController::class, 'addInStock']);
            Route::post('products/{uuid}/properties', [Seller\ProductController::class, 'addProductProperties']);
            Route::post('products/{uuid}/extras', [Seller\ProductController::class, 'addProductExtras']);
            Route::post('products/delete/all', [Seller\ProductController::class, 'deleteAll']);
            Route::apiResource('products', Seller\ProductController::class);


            /* Seller Shop Users */
            Route::get('shop/users/paginate', [Seller\UserController::class, 'shopUsersPaginate']);
            Route::get('shop/users/role/deliveryman', [Seller\UserController::class, 'getDeliveryman']);
            Route::get('shop/users/{uuid}', [Seller\UserController::class, 'shopUserShow']);

            /* Seller Users */
            Route::get('users/paginate', [Seller\UserController::class, 'paginate']);
            Route::get('users/{uuid}', [Seller\UserController::class, 'show']);
            Route::post('users', [Seller\UserController::class, 'store']);
            Route::post('users/{uuid}/change/status', [Seller\UserController::class, 'setUserActive']);
            Route::post('users/{uuid}/address', [Seller\UserController::class, 'userAddressCreate']);

            /* Seller Invite */
            Route::get('shops/invites/paginate', [Seller\InviteController::class, 'paginate']);
            Route::post('/shops/invites/{id}/status/change', [Seller\InviteController::class, 'changeStatus']);

            /* Seller Coupon */
            Route::get('discounts/paginate', [Seller\DiscountController::class, 'paginate']);
            Route::post('discounts/{id}/active/status', [Seller\DiscountController::class, 'setActiveStatus']);
            Route::apiResource('discounts', Seller\DiscountController::class)->except('index');

            /* Seller Banner */
            Route::get('looks/paginate', [Seller\BannerController::class, 'paginate']);
            Route::post('looks/active/{id}', [Seller\BannerController::class, 'setActiveBanner']);
            Route::apiResource('looks', Seller\BannerController::class);

            /* Seller Order */
            Route::get('orders/paginate', [Seller\OrderController::class, 'paginate']);
            Route::post('order/details/{id}/deliveryman', [Seller\OrderController::class, 'orderDetailDeliverymanUpdate']);
            Route::post('order/details/{id}/status', [Seller\OrderController::class, 'orderDetailStatusUpdate']);
            Route::apiResource('orders', Seller\OrderController::class)->except('index');
            Route::post('orders/{id}/all-status/change', [Seller\OrderController::class,'allOrderStatusChange']);
            Route::get('order/report', [Seller\OrderController::class, 'report']);


            /* Seller Deliveries */
            Route::post('deliveries/{id}/active/status', [Seller\DeliveryController::class, 'setActive']);
            Route::get('deliveries/types', [Seller\DeliveryController::class, 'deliveryTypes']);
            Route::apiResource('deliveries', Seller\DeliveryController::class);

            /* Seller Subscription */
            Route::get('subscriptions', [Seller\SubscriptionController::class, 'index']);
            Route::post('subscriptions/{id}/attach', [Seller\SubscriptionController::class, 'subscriptionAttach']);

            //  Report
            Route::get('orders/report/chart', [Seller\OrderController::class, 'ordersReportChart']);
            Route::get('orders/report/paginate', [Seller\OrderController::class, 'ordersReportPaginate']);
            Route::get('products/report/chart', [Seller\ProductController::class, 'productReportChart']);
            Route::get('products/report/paginate', [Seller\ProductController::class, 'productReportPaginate']);
            Route::get('stocks/report/paginate', [Seller\ProductController::class, 'stockReportPaginate']);
            Route::get('categories/report/chart', [Seller\CategoryController::class, 'reportChart']);
            Route::get('categories/report/paginate', [Seller\CategoryController::class, 'reportPaginate']);
            Route::get('product/{product}/report/stock', [Seller\ProductController::class, 'productStockReport']);
            Route::get('product/{product}/report/extras', [Seller\ProductController::class, 'productExtrasReport']);
            Route::get('products/report/compare', [Seller\ProductController::class, 'productReportCompare']);
            Route::get('categories/report/compare', [Seller\CategoryController::class, 'reportCompare']);
            Route::get('variations/report/paginate', [Seller\ProductController::class, 'variationsReportPaginate']);
            Route::get('variations/report/chart', [Seller\ProductController::class, 'variationsReportChart']);
            Route::get('variations/report/compare', [Seller\ProductController::class, 'variationsReportCompare']);
            Route::get('overview/report/leaderboards/{limit}', [Seller\OverviewController::class, 'leaderboards']);
            Route::get('overview/report/chart', [Seller\OverviewController::class, 'reportChart']);
            Route::get('revenue/report/chart', [Seller\RevenueController::class, 'reportChart']);
            Route::get('revenue/report/paginate', [Seller\RevenueController::class, 'reportPaginate']);
            Route::get('shops-with-seller', [Seller\ShopController::class, 'getWithSeller']);
            Route::get('shops/report/paginate', [Seller\ShopController::class, 'reportPaginate']);
            Route::get('shops/report/chart', [Seller\ShopController::class, 'reportChart']);
            Route::get('shops/report/compare', [Seller\ShopController::class, 'reportCompare']);

            Route::apiResource('point-deliveries', Seller\PointDeliveryController::class);
            Route::delete('point-deliveries/delete', [Seller\PointDeliveryController::class, 'destroy']);

            /* Order Refunds */
            Route::get('order-refunds/paginate', [Seller\OrderRefundController::class, 'paginate']);
            Route::delete('order-refunds/delete', [Seller\OrderRefundController::class, 'destroy']);
            Route::apiResource('order-refunds', Seller\OrderRefundController::class);

        });

        // ADMIN BLOCK
        Route::group(['prefix' => 'admin', 'middleware' => ['sanctum.check', 'role:admin|manager'], 'as' => 'admin.'], function () {
            /* Dashboard */
            Route::get('statistics/count', [Admin\DashboardController::class, 'countStatistics']);
            Route::get('statistics/sum', [Admin\DashboardController::class, 'sumStatistics']);
            Route::get('statistics/customer/top', [Admin\DashboardController::class, 'topCustomersStatistics']);
            Route::get('statistics/products/top', [Admin\DashboardController::class, 'topProductsStatistics']);
            Route::get('statistics/orders/sales', [Admin\DashboardController::class, 'ordersSalesStatistics']);
            Route::get('statistics/orders/count', [Admin\DashboardController::class, 'ordersCountStatistics']);

            /* Terms & Condition */
            Route::post('term', [Admin\TermsController::class, 'store']);
            Route::get('term', [Admin\TermsController::class, 'show']);
            Route::put('term/{id}', [Admin\TermsController::class, 'update']);

            /* Privacy & Policy */
            Route::post('policy', [Admin\PrivacyPolicyController::class, 'store']);
            Route::get('policy', [Admin\PrivacyPolicyController::class, 'show']);
            Route::put('policy/{id}', [Admin\PrivacyPolicyController::class, 'update']);

            /* Reviews */
            Route::get('reviews/paginate', [Admin\ReviewController::class, 'paginate']);
            Route::apiResource('reviews', Admin\ReviewController::class);

            /* Languages */
            Route::get('languages/default', [Admin\LanguageController::class, 'getDefaultLanguage']);
            Route::post('languages/default/{id}', [Admin\LanguageController::class, 'setDefaultLanguage']);
            Route::get('languages/active', [Admin\LanguageController::class, 'getActiveLanguages']);
            Route::post('languages/{id}/image/delete', [Admin\LanguageController::class, 'imageDelete']);
            Route::apiResource('languages', Admin\LanguageController::class);

            /* Languages */
            Route::get('currencies/default', [Admin\CurrencyController::class, 'getDefaultCurrency']);
            Route::post('currencies/default/{id}', [Admin\CurrencyController::class, 'setDefaultCurrency']);
            Route::get('currencies/active', [Admin\CurrencyController::class, 'getActiveCurrencies']);
            Route::apiResource('currencies', Admin\CurrencyController::class);

            /* Categories */
            Route::post('categories/check-position', [Admin\CategoryController::class, 'checkPosition']);
            Route::put('categories/set-position/{id}', [Admin\CategoryController::class, 'setPosition']);
            Route::get('categories/export', [Admin\CategoryController::class, 'fileExport']);
            Route::post('categories/{uuid}/image/delete', [Admin\CategoryController::class, 'imageDelete']);
            Route::get('categories/search', [Admin\CategoryController::class, 'categoriesSearch']);
            Route::get('categories/paginate', [Admin\CategoryController::class, 'paginate']);
            Route::post('categories/import', [Admin\CategoryController::class, 'fileImport']);
            Route::apiResource('categories', Admin\CategoryController::class);

            /* Brands */
            Route::get('brands/export', [Admin\BrandController::class, 'fileExport']);
            Route::post('brands/import', [Admin\BrandController::class, 'fileImport']);
            Route::post('brands/{uuid}/image/delete', [Admin\BrandController::class, 'imageDelete']);
            Route::get('brands/paginate', [Admin\BrandController::class, 'paginate']);
            Route::get('brands/search', [Admin\BrandController::class, 'brandsSearch']);
            Route::apiResource('brands', Admin\BrandController::class);

            /* Banner */
            Route::get('banners/paginate', [Admin\BannerController::class, 'paginate']);
            Route::post('banners/active/{id}', [Admin\BannerController::class, 'setActiveBanner']);
            Route::apiResource('banners', Admin\BannerController::class);

            /* Units */
            Route::get('units/paginate', [Admin\UnitController::class, 'paginate']);
            Route::post('units/active/{id}', [Admin\UnitController::class, 'setActiveUnit']);
            Route::apiResource('units', Admin\UnitController::class);

            /* Shops */
            Route::get('shops/search', [Admin\ShopController::class, 'shopsSearch']);
            Route::get('shops/paginate', [Admin\ShopController::class, 'paginate']);
            Route::get('shops/nearby', [Admin\ShopController::class, 'nearbyShops']);
            Route::post('shops/{uuid}/image/delete', [Admin\ShopController::class, 'imageDelete']);
            Route::post('shops/{uuid}/status/change', [Admin\ShopController::class, 'statusChange']);
            Route::apiResource('shops', Admin\ShopController::class);

            /* Extras Group & Value */
            Route::get('extra/groups/types', [Admin\ExtraGroupController::class, 'typesList']);
            Route::apiResource('extra/groups', Admin\ExtraGroupController::class);
            Route::apiResource('extra/values', Admin\ExtraValueController::class);

            /* Products */
            Route::get('products/out-of-stock', [Admin\ProductController::class, 'outOfStock']);
            Route::get('products/export', [Admin\ProductController::class, 'fileExport']);
            Route::post('products/import', [Admin\ProductController::class, 'fileImport']);
            Route::get('products/paginate', [Admin\ProductController::class, 'paginate']);
            Route::get('products/search', [Admin\ProductController::class, 'productsSearch']);
            Route::post('products/{uuid}/stocks', [Admin\ProductController::class, 'addInStock']);
            Route::post('products/{uuid}/properties', [Admin\ProductController::class, 'addProductProperties']);
            Route::post('products/{uuid}/extras', [Admin\ProductController::class, 'addProductExtras']);
            Route::post('products/{uuid}/active', [Admin\ProductController::class, 'setActive']);
            Route::post('products/delete/all', [Admin\ProductController::class, 'deleteAll']);
            Route::apiResource('products', Admin\ProductController::class);

            /* Orders */
            Route::get('orders/paginate', [Admin\OrderController::class, 'paginate']);
            Route::apiResource('orders', Admin\OrderController::class);
            Route::post('orders/{id}/all-status/change', [Admin\OrderController::class,'allOrderStatusChange']);
            Route::put('order/{id}/deliveryman', [Admin\OrderController::class, 'orderDeliverymanUpdate']);

            /* Order Details */
            Route::get('order/details/paginate', [Admin\OrderDetailController::class, 'paginate']);
            Route::get('order/calculate/products', [Admin\OrderDetailController::class, 'calculateOrderProducts']);
            Route::post('order/details/{id}/status', [Admin\OrderDetailController::class, 'orderDetailStatusUpdate']);
            Route::apiResource('order/details', Admin\OrderDetailController::class);

            /* Users Address */
            Route::post('/users/{uuid}/addresses', [Admin\UserAddressController::class, 'store']);

            /* Users */
            Route::get('users/search', [Admin\UserController::class, 'usersSearch']);
            Route::get('users/paginate', [Admin\UserController::class, 'paginate']);
            Route::post('users/{uuid}/role/update', [Admin\UserController::class, 'updateRole']);
            Route::get('users/{uuid}/wallets/history', [Admin\UserController::class, 'walletHistories']);
            Route::post('users/{uuid}/wallets', [Admin\UserController::class, 'topUpWallet']);
            Route::put('users/{uuid}/wallet-clear', [Admin\UserController::class, 'walletClear']);
            Route::post('users/{uuid}/active', [Admin\UserController::class, 'setActive']);
            Route::apiResource('users', Admin\UserController::class);
            Route::get('roles', Admin\RoleController::class);

            /* Users Wallet Histories */
            Route::get('/wallet/histories/paginate', [Admin\WalletHistoryController::class, 'paginate']);
            Route::post('/wallet/history/{uuid}/status/change', [Admin\WalletHistoryController::class, 'changeStatus']);

            /* Subscriptions */
            Route::apiResource('subscriptions', Admin\SubscriptionController::class);

            /* Point */
            Route::get('points/paginate', [Admin\PointController::class, 'paginate']);
            Route::post('points/{id}/active', [Admin\PointController::class, 'setActive']);
            Route::apiResource('points', Admin\PointController::class);

            /* Payments */
            Route::post('payments/{id}/active/status', [Admin\PaymentController::class, 'setActive']);
            Route::apiResource('payments', Admin\PaymentController::class)->except('store', 'delete');

            /* SMS Gateways */
            Route::post('sms-gateways/{id}/active/status', [Admin\SMSGatewayController::class, 'setActive']);
            Route::apiResource('sms-gateways', Admin\SMSGatewayController::class)->except('store', 'delete');

            /* Translations */
            Route::get('translations/paginate', [Admin\TranslationController::class, 'paginate']);
            Route::apiResource('translations', Admin\TranslationController::class);

            /* Transaction */
            Route::get('transactions/paginate', [Admin\TransactionController::class, 'paginate']);
            Route::get('transactions/{id}', [Admin\TransactionController::class, 'show']);

            Route::get('tickets/paginate', [Admin\TicketController::class, 'paginate']);
            Route::post('tickets/{id}/status', [Admin\TicketController::class, 'setStatus']);
            Route::get('tickets/statuses', [Admin\TicketController::class, 'getStatuses']);
            Route::apiResource('tickets', Admin\TicketController::class);

            /* Deliveries */
            Route::get('delivery/types', [Admin\DeliveryController::class, 'deliveryTypes']);
            Route::apiResource('deliveries', Admin\DeliveryController::class);

            /* FAQS */
            Route::get('faqs/paginate', [Admin\FAQController::class, 'paginate']);
            Route::post('faqs/{uuid}/active/status', [Admin\FAQController::class, 'setActiveStatus']);
            Route::apiResource('faqs', Admin\FAQController::class)->except('index');

            /* Blogs */
            Route::get('blogs/paginate', [Admin\BlogController::class, 'paginate']);
            Route::post('blogs/{uuid}/publish', [Admin\BlogController::class, 'blogPublish']);
            Route::post('blogs/{uuid}/active/status', [Admin\BlogController::class, 'setActiveStatus']);
            Route::apiResource('blogs', Admin\BlogController::class)->except('index');

            /* Settings */
            Route::get('settings/system/information', [Admin\SettingController::class, 'systemInformation']);
            Route::get('settings/system/cache/clear', [Admin\SettingController::class, 'clearCache']);
            Route::apiResource('settings', Admin\SettingController::class);
            Route::post('backup/history', [Admin\BackupController::class, 'download']);
            Route::get('backup/history', [Admin\BackupController::class, 'histories']);

            // Auto updates
            Route::post('/project-upload', [Admin\ProjectController::class, 'projectUpload']);
            Route::post('/project-update', [Admin\ProjectController::class, 'projectUpdate']);

            /* Findex */

            //  Report
            Route::get('orders/report/chart', [Admin\OrderController::class, 'ordersReportChart']);
            Route::get('orders/report/paginate', [Admin\OrderController::class, 'ordersReportPaginate']);
            Route::get('products/report/chart', [Admin\ProductController::class, 'productReportChart']);
            Route::get('products/report/compare', [Admin\ProductController::class, 'productReportCompare']);
            Route::get('products/report/paginate', [Admin\ProductController::class, 'productReportPaginate']);
            Route::get('product/{product}/report/stock', [Admin\ProductController::class, 'productStockReport']);
            Route::get('product/{product}/report/extras', [Admin\ProductController::class, 'productExtrasReport']);
            Route::post('products/{uuid}/status/change', [Admin\ProductController::class, 'setStatus']);
            Route::get('stocks/report/paginate', [Admin\ProductController::class, 'stockReportPaginate']);
            Route::get('categories/report/chart', [Admin\CategoryController::class, 'reportChart']);
            Route::get('categories/report/paginate', [Admin\CategoryController::class, 'reportPaginate']);
            Route::get('categories/report/compare', [Admin\CategoryController::class, 'reportCompare']);
            Route::get('variations/report/paginate', [Admin\ProductController::class, 'variationsReportPaginate']);
            Route::get('variations/report/chart', [Admin\ProductController::class, 'variationsReportChart']);
            Route::get('variations/report/compare', [Admin\ProductController::class, 'variationsReportCompare']);
            Route::get('overview/report/leaderboards/{limit}', [Admin\OverviewController::class, 'leaderboards']);
            Route::get('overview/report/chart', [Admin\OverviewController::class, 'reportChart']);
            Route::get('revenue/report/chart', [Admin\RevenueController::class, 'reportChart']);
            Route::get('revenue/report/paginate', [Admin\RevenueController::class, 'reportPaginate']);
            Route::get('shops-with-seller', [Admin\ShopController::class, 'getWithSeller']);
            Route::get('shops/report/paginate', [Admin\ShopController::class, 'reportPaginate']);
            Route::get('shops/report/chart', [Admin\ShopController::class, 'reportChart']);
            Route::get('shops/report/compare', [Admin\ShopController::class, 'reportCompare']);

            // Point Delivery
            Route::apiResource('point-deliveries', Admin\PointDeliveryController::class);
            Route::delete('point-deliveries/delete', [Admin\PointDeliveryController::class, 'destroy']);


            Route::get('deliveryman-settings/paginate', [Admin\DeliveryManSettingController::class, 'paginate']);
            Route::delete('deliveryman-settings/delete', [Admin\DeliveryManSettingController::class, 'destroy']);

            Route::apiResource('deliveryman-settings', Admin\DeliveryManSettingController::class)
                ->except('index', 'destroy');

            /* Parcel Orders */
            Route::get('parcel-order/export',            [Admin\ParcelOrderController::class, 'fileExport']);
            Route::post('parcel-order/import',           [Admin\ParcelOrderController::class, 'fileImport']);
            Route::post('parcel-order/{id}/deliveryman', [Admin\ParcelOrderController::class, 'orderDeliverymanUpdate']);
            Route::post('parcel-order/{id}/status',      [Admin\ParcelOrderController::class, 'orderStatusUpdate']);
            Route::apiResource('parcel-orders',       Admin\ParcelOrderController::class);
            Route::delete('parcel-orders/delete',        [Admin\ParcelOrderController::class, 'destroy']);

            /* Parcel Options */
            Route::apiResource('parcel-options',    Admin\ParcelOptionController::class);
            Route::delete('parcel-options/delete',           [Admin\ParcelOptionController::class, 'destroy']);
            Route::get('parcel-options/drop/all',            [Admin\ParcelOptionController::class, 'dropAll']);
            Route::get('parcel-options/restore/all',         [Admin\ParcelOptionController::class, 'restoreAll']);
            Route::get('parcel-options/truncate/db',         [Admin\ParcelOptionController::class, 'truncate']);

            /* Parcel Order Setting */
            Route::apiResource('parcel-order-settings',    Admin\ParcelOrderSettingController::class);
            Route::delete('parcel-order-settings/delete',    [Admin\ParcelOrderSettingController::class, 'destroy']);
            Route::get('parcel-order-settings/drop/all',     [Admin\ParcelOrderSettingController::class, 'dropAll']);
            Route::get('parcel-order-settings/restore/all',  [Admin\ParcelOrderSettingController::class, 'restoreAll']);
            Route::get('parcel-order-settings/truncate/db',  [Admin\ParcelOrderSettingController::class, 'truncate']);

            /* Seller Coupon */
            Route::get('coupons/paginate', [Admin\CouponController::class, 'paginate']);
            Route::apiResource('coupons', Admin\CouponController::class);

            /* Notifications */
            Route::apiResource('notifications', Admin\NotificationController::class);
            Route::delete('notifications/delete',   [Admin\NotificationController::class, 'destroy']);

            /* Order Refunds */
            Route::get('order-refunds/paginate',    [Admin\OrderRefundController::class, 'paginate']);
            Route::delete('order-refunds/delete',   [Admin\OrderRefundController::class, 'destroy']);
            Route::apiResource('order-refunds', Admin\OrderRefundController::class);
        });

    });
});
