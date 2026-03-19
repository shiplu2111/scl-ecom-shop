<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\AdminAuthController;

Route::prefix('v1')->group(function () {
    
    // Public User Auth & OTP
    Route::post('/send-otp', [\App\Http\Controllers\API\V1\OtpController::class, 'send']);
    Route::post('/verify-otp', [\App\Http\Controllers\API\V1\OtpController::class, 'verify']);
    
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Public Location API
    Route::get('/locations/divisions', [\App\Http\Controllers\Api\V1\LocationController::class, 'divisions']);
    Route::get('/locations/districts', [\App\Http\Controllers\Api\V1\LocationController::class, 'districts']);
    Route::get('/locations/thanas', [\App\Http\Controllers\Api\V1\LocationController::class, 'thanas']);

    // Public Product catalog
    Route::get('/products', [\App\Http\Controllers\API\V1\ProductController::class, 'index']);
    Route::get('/products/{slug}', [\App\Http\Controllers\API\V1\ProductController::class, 'show']);

    // Cart System (Guest + Auth mapped by X-Cart-Session)
    Route::get('/cart', [\App\Http\Controllers\API\V1\CartController::class, 'index']);
    Route::post('/cart/items', [\App\Http\Controllers\API\V1\CartController::class, 'add']);
    Route::delete('/cart/items/{itemId}', [\App\Http\Controllers\API\V1\CartController::class, 'remove']);
    Route::post('/cart/apply-coupon', [\App\Http\Controllers\API\V1\CartController::class, 'applyCoupon']);
    Route::delete('/cart/remove-coupon', [\App\Http\Controllers\API\V1\CartController::class, 'removeCoupon']);

    // Protected Auth routes mapping explicit wishlists seamlessly natively natively elegantly cleanly efficiently implicitly fluently dynamically
    Route::middleware(['auth:api', 'single.device'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);

        // Profile & Avatar Management
        Route::get('/profile', [\App\Http\Controllers\API\V1\UserController::class, 'profile']);
        Route::post('/profile', [\App\Http\Controllers\API\V1\UserController::class, 'updateProfile']);
        Route::post('/profile/avatar', [\App\Http\Controllers\API\V1\UserController::class, 'uploadAvatar']);

        // Address Management
        Route::get('/addresses', [\App\Http\Controllers\API\V1\UserController::class, 'listAddresses']);
        Route::post('/addresses', [\App\Http\Controllers\API\V1\UserController::class, 'createAddress']);
        Route::put('/addresses/{id}', [\App\Http\Controllers\API\V1\UserController::class, 'updateAddress']);
        Route::delete('/addresses/{id}', [\App\Http\Controllers\API\V1\UserController::class, 'deleteAddress']);
        Route::put('/addresses/{id}/default', [\App\Http\Controllers\API\V1\UserController::class, 'setDefaultAddress']);

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\API\V1\NotificationController::class, 'index']);
        Route::put('/notifications/read-all', [\App\Http\Controllers\API\V1\NotificationController::class, 'markAllAsRead']);
        Route::put('/notifications/{id}/read', [\App\Http\Controllers\API\V1\NotificationController::class, 'markAsRead']);

        // Wishlist mapping
        Route::get('/wishlists', [\App\Http\Controllers\API\V1\WishlistController::class, 'index']);
        Route::post('/wishlists/toggle', [\App\Http\Controllers\API\V1\WishlistController::class, 'toggle']);
        
        // Orders & Checkout seamlessly mapping
        Route::post('/checkout', [\App\Http\Controllers\API\V1\OrderController::class, 'checkout']);
        Route::get('/orders', [\App\Http\Controllers\API\V1\OrderController::class, 'index']);
        Route::get('/orders/{id}', [\App\Http\Controllers\API\V1\OrderController::class, 'show']);

        // Payment Interfaces
        Route::post('/payment/initiate', [\App\Http\Controllers\API\V1\PaymentController::class, 'initiate']);
        Route::post('/payment/{gateway}/verify', [\App\Http\Controllers\API\V1\PaymentController::class, 'verify']);
        Route::get('/payment/{gateway}/verify', [\App\Http\Controllers\API\V1\PaymentController::class, 'verify']);
    });

    // Public payment webhooks
    Route::post('/payment/{gateway}/webhook', [\App\Http\Controllers\API\V1\PaymentController::class, 'webhook']);

    // Admin Auth
    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminAuthController::class, 'login']);
        
        // Protected Admin routes
        Route::middleware(['auth:admin', 'single.device:admin'])->group(function () {
            Route::post('/logout', [AdminAuthController::class, 'logout']);
            Route::post('/refresh', [AdminAuthController::class, 'refresh']);
            Route::get('/me', [AdminAuthController::class, 'me']);

            // Spatie Role & Permission Administration
            Route::middleware(['role:super_admin'])->group(function() {
                Route::apiResource('roles', \App\Http\Controllers\API\V1\RoleController::class);
                Route::apiResource('permissions', \App\Http\Controllers\API\V1\PermissionController::class);
                Route::post('admins/{id}/assign-roles', [\App\Http\Controllers\API\V1\AdminRoleController::class, 'assignRoles']);
                
                // Admin Management
                Route::apiResource('admins', \App\Http\Controllers\API\V1\AdminUserController::class);
                Route::put('admins/{id}/status', [\App\Http\Controllers\API\V1\AdminUserController::class, 'toggleStatus']);

                // Credential Management for Courier and Payment
                Route::get('credentials/couriers', [\App\Http\Controllers\CourierCredentialController::class, 'index']);
                Route::post('credentials/couriers', [\App\Http\Controllers\CourierCredentialController::class, 'store']);
                Route::get('credentials/payments', [\App\Http\Controllers\PaymentCredentialController::class, 'index']);
                Route::post('credentials/payments', [\App\Http\Controllers\PaymentCredentialController::class, 'store']);
            });

            // Admin Product Management
            Route::post('/products', [\App\Http\Controllers\API\V1\ProductController::class, 'store'])->middleware('permission:products_create');
            Route::post('/products/{id}/upload-image', [\App\Http\Controllers\API\V1\ProductController::class, 'uploadImage'])->middleware('permission:products_update');

            // Admin Coupon Management
            Route::apiResource('coupons', \App\Http\Controllers\API\V1\CouponController::class)->only(['index', 'show'])->middleware('permission:coupons_read');
            Route::apiResource('coupons', \App\Http\Controllers\API\V1\CouponController::class)->only(['store'])->middleware('permission:coupons_create');
            Route::apiResource('coupons', \App\Http\Controllers\API\V1\CouponController::class)->only(['update'])->middleware('permission:coupons_update');
            Route::apiResource('coupons', \App\Http\Controllers\API\V1\CouponController::class)->only(['destroy'])->middleware('permission:coupons_delete');
            
            // Admin Order Management
            Route::get('/orders', [\App\Http\Controllers\API\V1\AdminOrderController::class, 'index'])->middleware('permission:orders_read');
            Route::get('/orders/{id}', [\App\Http\Controllers\API\V1\AdminOrderController::class, 'show'])->middleware('permission:orders_read');
            Route::put('/orders/{id}/status', [\App\Http\Controllers\API\V1\AdminOrderController::class, 'updateStatus'])->middleware('permission:orders_update');

            // Admin Courier Integration
            Route::post('/orders/{order}/dispatch', [\App\Http\Controllers\API\V1\Admin\CourierController::class, 'dispatchOrder'])->middleware('permission:orders_update');
            Route::get('/orders/{order}/track', [\App\Http\Controllers\API\V1\Admin\CourierController::class, 'trackOrder'])->middleware('permission:orders_read');

            // Admin Inventory Management
            Route::put('/variants/{variant}/stock', [\App\Http\Controllers\API\V1\Admin\InventoryController::class, 'updateStock'])->middleware('permission:inventory_update');
            Route::get('/variants/{variant}/inventory-history', [\App\Http\Controllers\API\V1\Admin\InventoryController::class, 'history'])->middleware('permission:inventory_read');

            // Admin Settings
            Route::get('/settings/{group}', [\App\Http\Controllers\API\V1\Admin\SettingsController::class, 'getByGroup'])->middleware('permission:manage_settings');
            Route::put('/settings/{group}', [\App\Http\Controllers\API\V1\Admin\SettingsController::class, 'updateGroup'])->middleware('permission:manage_settings');

            // Admin Activity Logs
            Route::get('/activity-logs', [\App\Http\Controllers\API\V1\Admin\ActivityLogController::class, 'index'])->middleware('permission:activity_logs_read');

            // Admin Blog & CMS
            Route::prefix('blog')->group(function () {
                Route::get('/categories', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'getCategories'])->middleware('permission:blog_categories_read');
                Route::post('/categories', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'createCategory'])->middleware('permission:blog_categories_create');
                Route::put('/categories/{id}', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'updateCategory'])->middleware('permission:blog_categories_update');
                Route::delete('/categories/{id}', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'deleteCategory'])->middleware('permission:blog_categories_delete');

                Route::get('/tags', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'getTags'])->middleware('permission:blog_tags_read');
                Route::post('/tags', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'createTag'])->middleware('permission:blog_tags_create');
                Route::put('/tags/{id}', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'updateTag'])->middleware('permission:blog_tags_update');
                Route::delete('/tags/{id}', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'deleteTag'])->middleware('permission:blog_tags_delete');

                Route::get('/posts', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'getPosts'])->middleware('permission:blog_posts_read');
                Route::post('/posts', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'createPost'])->middleware('permission:blog_posts_create');
                Route::put('/posts/{id}', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'updatePost'])->middleware('permission:blog_posts_update');
                Route::delete('/posts/{id}', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'deletePost'])->middleware('permission:blog_posts_delete');
            });

            Route::get('/pages', [\App\Http\Controllers\API\V1\Admin\PageController::class, 'index'])->middleware('permission:pages_read');
            Route::post('/pages', [\App\Http\Controllers\API\V1\Admin\PageController::class, 'store'])->middleware('permission:pages_create');
            Route::get('/pages/{id}', [\App\Http\Controllers\API\V1\Admin\PageController::class, 'show'])->middleware('permission:pages_read');
            Route::put('/pages/{id}', [\App\Http\Controllers\API\V1\Admin\PageController::class, 'update'])->middleware('permission:pages_update');
            Route::delete('/pages/{id}', [\App\Http\Controllers\API\V1\Admin\PageController::class, 'destroy'])->middleware('permission:pages_delete');

            // Admin Location Management
            foreach (['divisions' => \App\Http\Controllers\Api\V1\Admin\Location\DivisionController::class,
                      'districts' => \App\Http\Controllers\Api\V1\Admin\Location\DistrictController::class,
                      'thanas' => \App\Http\Controllers\Api\V1\Admin\Location\ThanaController::class] as $route => $controller) {
                Route::apiResource("locations/{$route}", $controller)->only(['index', 'show'])->middleware('permission:locations_read');
                Route::apiResource("locations/{$route}", $controller)->only(['store'])->middleware('permission:locations_create');
                Route::apiResource("locations/{$route}", $controller)->only(['update'])->middleware('permission:locations_update');
                Route::apiResource("locations/{$route}", $controller)->only(['destroy'])->middleware('permission:locations_delete');
            }
        });
    });

    // Public Blog & CMS
    Route::get('/blog/categories', [\App\Http\Controllers\API\V1\BlogController::class, 'getCategories']);
    Route::get('/blog/tags', [\App\Http\Controllers\API\V1\BlogController::class, 'getTags']);
    Route::get('/blog/posts', [\App\Http\Controllers\API\V1\BlogController::class, 'getPosts']);
    Route::get('/blog/posts/{slug}', [\App\Http\Controllers\API\V1\BlogController::class, 'showPost']);
    Route::get('/pages/{slug}', [\App\Http\Controllers\API\V1\PageController::class, 'show']);
});
