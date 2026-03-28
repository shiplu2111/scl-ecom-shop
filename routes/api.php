<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\AdminAuthController;
use App\Http\Controllers\API\V1\AdminPaymentCredentialController;

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

    // Public Maintenance status
    Route::get('/maintenance', [\App\Http\Controllers\API\V1\SettingsController::class, 'maintenance']);

    // Public Cookie settings
    Route::get('/cookie-settings', [\App\Http\Controllers\API\V1\SettingsController::class, 'cookieSettings']);

    // Public Category & Brand catalog
    Route::get('/categories', [\App\Http\Controllers\API\V1\CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [\App\Http\Controllers\API\V1\CategoryController::class, 'show']);
    Route::get('/brands', [\App\Http\Controllers\API\V1\BrandController::class, 'index']);
    Route::get('/brands/{slug}', [\App\Http\Controllers\API\V1\BrandController::class, 'show']);

    // Newsletter
    Route::post('/subscribe', [\App\Http\Controllers\API\V1\SubscriberController::class, 'subscribe']);
    Route::get('/unsubscribe', [\App\Http\Controllers\API\V1\SubscriberController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

    // Cart System (Guest + Auth mapped by X-Cart-Session)
    Route::get('/cart', [\App\Http\Controllers\API\V1\CartController::class, 'index']);
    Route::post('/cart/items', [\App\Http\Controllers\API\V1\CartController::class, 'add']);
    Route::delete('/cart/items/{itemId}', [\App\Http\Controllers\API\V1\CartController::class, 'remove']);
    Route::post('/cart/apply-coupon', [\App\Http\Controllers\API\V1\CartController::class, 'applyCoupon']);
    Route::delete('/cart/remove-coupon', [\App\Http\Controllers\API\V1\CartController::class, 'removeCoupon']);

    // Protected Auth routes mapping explicit wishlists seamlessly natively natively elegantly cleanly efficiently implicitly fluently dynamically
    Route::middleware(['auth:api', 'single.device', 'user.active'])->group(function () {
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

        // Support Tickets
        Route::get('/tickets', [\App\Http\Controllers\API\V1\TicketController::class, 'index']);
        Route::post('/tickets', [\App\Http\Controllers\API\V1\TicketController::class, 'store']);
        Route::get('/tickets/{id}', [\App\Http\Controllers\API\V1\TicketController::class, 'show']);
        Route::post('/tickets/{id}/reply', [\App\Http\Controllers\API\V1\TicketController::class, 'reply']);

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
        Route::post('/forget-password', [\App\Http\Controllers\API\V1\Admin\PasswordController::class, 'forgetPassword']);
        Route::post('/reset-password', [\App\Http\Controllers\API\V1\Admin\PasswordController::class, 'resetPassword']);
        
        // Protected Admin routes
        Route::middleware(['auth:admin', 'single.device:admin'])->group(function () {
            Route::post('/logout', [AdminAuthController::class, 'logout']);
            Route::post('/refresh', [AdminAuthController::class, 'refresh']);
            // Review
            Route::get('/reviews', [ProductReviewController::class, 'index']);

            // Newsletter
            Route::post('/change-password', [\App\Http\Controllers\API\V1\Admin\PasswordController::class, 'changePassword']);

            // Admin Profile Management
            Route::get('/profile', [\App\Http\Controllers\API\V1\Admin\AdminProfileController::class, 'show']);
            Route::put('/profile', [\App\Http\Controllers\API\V1\Admin\AdminProfileController::class, 'update']);
            Route::post('/profile/avatar', [\App\Http\Controllers\API\V1\Admin\AdminProfileController::class, 'uploadAvatar']);

            // Spatie Role & Permission Administration
            Route::apiResource('roles', \App\Http\Controllers\API\V1\RoleController::class);
            Route::apiResource('permissions', \App\Http\Controllers\API\V1\PermissionController::class);

            // Newsletter Management
            Route::get('/subscribers', [\App\Http\Controllers\API\V1\Admin\SubscriberController::class, 'index']);
            Route::delete('/subscribers/{subscriber}', [\App\Http\Controllers\API\V1\Admin\SubscriberController::class, 'destroy']);
            Route::post('/subscribers/send-email', [\App\Http\Controllers\API\V1\Admin\SubscriberController::class, 'sendEmail']);
            
            // Campaign Management
            Route::get('/campaigns', [\App\Http\Controllers\API\V1\Admin\CampaignController::class, 'index']);
            Route::get('/campaigns/{campaign}', [\App\Http\Controllers\API\V1\Admin\CampaignController::class, 'show']);
            
            // Banner Management
            Route::apiResource('banners', \App\Http\Controllers\API\V1\Admin\BannerController::class);
            Route::patch('banners/{banner}/toggle-status', [\App\Http\Controllers\API\V1\Admin\BannerController::class, 'toggleStatus']);
            
            // FAQ Management
            Route::post('faqs/bulk-delete', [\App\Http\Controllers\API\V1\Admin\FaqController::class, 'bulkDelete']);
            Route::post('faqs/reorder', [\App\Http\Controllers\API\V1\Admin\FaqController::class, 'reorder']);
            Route::apiResource('faqs', \App\Http\Controllers\API\V1\Admin\FaqController::class);
            Route::middleware(['role:super_admin'])->group(function() {
                Route::post('admins/{id}/assign-roles', [\App\Http\Controllers\API\V1\AdminRoleController::class, 'assignRoles']);
                
                // Admin Management
                Route::apiResource('admins', \App\Http\Controllers\API\V1\AdminUserController::class);
                Route::put('admins/{id}/status', [\App\Http\Controllers\API\V1\AdminUserController::class, 'toggleStatus']);

                Route::apiResource('payment-credentials', AdminPaymentCredentialController::class);

                // Credential Management for Courier and Payment
                Route::get('credentials/couriers', [\App\Http\Controllers\CourierCredentialController::class, 'index']);
                Route::post('credentials/couriers', [\App\Http\Controllers\CourierCredentialController::class, 'store']);
                Route::get('credentials/payments', [\App\Http\Controllers\PaymentCredentialController::class, 'index']);
                Route::post('credentials/payments', [\App\Http\Controllers\PaymentCredentialController::class, 'store']);

                // Sitemap Management
                Route::get('sitemap/status', [\App\Http\Controllers\API\V1\Admin\SitemapController::class, 'getStatus']);
                Route::post('sitemap/generate', [\App\Http\Controllers\API\V1\Admin\SitemapController::class, 'generate']);

                // Backup Management
                Route::get('backups', [\App\Http\Controllers\API\V1\Admin\BackupController::class, 'index']);
                Route::post('backups', [\App\Http\Controllers\API\V1\Admin\BackupController::class, 'create']);
                Route::get('backups/{id}/download', [\App\Http\Controllers\API\V1\Admin\BackupController::class, 'download']);
                Route::delete('backups/{id}', [\App\Http\Controllers\API\V1\Admin\BackupController::class, 'destroy']);
            });

            // Admin Notifications
            Route::get('/notifications', [\App\Http\Controllers\API\V1\Admin\NotificationController::class, 'index']);
            Route::put('/notifications/read-all', [\App\Http\Controllers\API\V1\Admin\NotificationController::class, 'markAllAsRead']);
            Route::put('/notifications/{id}/read', [\App\Http\Controllers\API\V1\Admin\NotificationController::class, 'markAsRead']);

            // Admin Dashboard
            Route::prefix('dashboard')->group(function () {
                Route::get('/stats', [\App\Http\Controllers\API\V1\Admin\DashboardController::class, 'stats']);
                Route::get('/charts', [\App\Http\Controllers\API\V1\Admin\DashboardController::class, 'charts']);
                Route::get('/recent-orders', [\App\Http\Controllers\API\V1\Admin\DashboardController::class, 'recentOrders']);
            });

            // Admin Product Management
            Route::apiResource('/categories', \App\Http\Controllers\API\V1\Admin\CategoryController::class)->middleware('permission:categories_read|categories_create|categories_update|categories_delete');
            Route::apiResource('/brands', \App\Http\Controllers\API\V1\Admin\BrandController::class)->middleware('permission:brands_read|brands_create|brands_update|brands_delete');
            
            Route::post('/products/bulk-delete', [\App\Http\Controllers\API\V1\Admin\ProductController::class, 'bulkDelete'])->middleware('permission:products_delete');
            Route::get('/products/export', [\App\Http\Controllers\API\V1\Admin\ProductController::class, 'export'])->middleware('permission:products_read');
            Route::apiResource('/products', \App\Http\Controllers\API\V1\Admin\ProductController::class)->middleware('permission:products_read|products_create|products_update|products_delete');
            Route::post('/products/{id}/upload-image', [\App\Http\Controllers\API\V1\Admin\ProductController::class, 'uploadImage'])->middleware('permission:products_update');

            // Admin Coupon Management
            Route::apiResource('coupons', \App\Http\Controllers\API\V1\CouponController::class)->only(['index', 'show'])->middleware('permission:coupons_read');
            Route::apiResource('coupons', \App\Http\Controllers\API\V1\CouponController::class)->only(['store'])->middleware('permission:coupons_create');
            Route::apiResource('coupons', \App\Http\Controllers\API\V1\CouponController::class)->only(['update'])->middleware('permission:coupons_update');
            Route::apiResource('coupons', \App\Http\Controllers\API\V1\CouponController::class)->only(['destroy'])->middleware('permission:coupons_delete');
            
            // Admin Order Management
            Route::get('/orders', [\App\Http\Controllers\API\V1\AdminOrderController::class, 'index'])->middleware('permission:orders_read');
            Route::get('/orders/{id}', [\App\Http\Controllers\API\V1\AdminOrderController::class, 'show'])->middleware('permission:orders_read');
            Route::put('/orders/{id}/status', [\App\Http\Controllers\API\V1\AdminOrderController::class, 'updateStatus'])->middleware('permission:orders_update');

            // Admin Customer Management
            Route::get('/customers', [\App\Http\Controllers\API\V1\Admin\CustomerController::class, 'index'])->middleware('permission:customers_read');
            Route::get('/customers/export', [\App\Http\Controllers\API\V1\Admin\CustomerController::class, 'export'])->middleware('permission:customers_read');
            Route::get('/customers/{id}', [\App\Http\Controllers\API\V1\Admin\CustomerController::class, 'show'])->middleware('permission:customers_read');
            Route::put('/customers/{id}/toggle-status', [\App\Http\Controllers\API\V1\Admin\CustomerController::class, 'toggleStatus'])->middleware('permission:customers_update');
            Route::get('/customers/{id}/activities', [\App\Http\Controllers\API\V1\Admin\CustomerController::class, 'activities'])->middleware('permission:customers_read');

            // Admin Courier Integration
            Route::post('/orders/{order}/dispatch', [\App\Http\Controllers\API\V1\Admin\CourierController::class, 'dispatchOrder'])->middleware('permission:orders_update');
            Route::get('/orders/{order}/track', [\App\Http\Controllers\API\V1\Admin\CourierController::class, 'trackOrder'])->middleware('permission:orders_read');

            // Admin Inventory Management
            Route::put('/variants/{id}/stock', [\App\Http\Controllers\API\V1\Admin\InventoryController::class, 'updateStock'])->middleware('permission:inventory_update');
            Route::get('/variants/{id}/inventory-history', [\App\Http\Controllers\API\V1\Admin\InventoryController::class, 'history'])->middleware('permission:inventory_read');

            // Admin Settings
            Route::get('/settings/{group}', [\App\Http\Controllers\API\V1\Admin\SettingsController::class, 'getByGroup'])->middleware('permission:manage_settings');
            Route::put('/settings/{group}', [\App\Http\Controllers\API\V1\Admin\SettingsController::class, 'updateGroup'])->middleware('permission:manage_settings');
            Route::post('/settings/email/test', [\App\Http\Controllers\API\V1\Admin\SettingsController::class, 'testEmail'])->middleware('permission:manage_settings');
            Route::post('/settings/sms/test', [\App\Http\Controllers\API\V1\Admin\SettingsController::class, 'testSms'])->middleware('permission:manage_settings');

            // Admin Activity Logs
            Route::get('/activity-logs', [\App\Http\Controllers\API\V1\Admin\ActivityLogController::class, 'index'])->middleware('permission:activity_logs_read');

            // Admin Announcement Management
            Route::apiResource('/announcements', \App\Http\Controllers\API\V1\Admin\AnnouncementController::class)->middleware('permission:manage_settings');
            Route::put('/announcements/{announcement}/toggle-status', [\App\Http\Controllers\API\V1\Admin\AnnouncementController::class, 'toggleStatus'])->middleware('permission:manage_settings');

            // Admin Transaction Management
            Route::get('/transactions', [\App\Http\Controllers\API\V1\Admin\TransactionController::class, 'index'])->middleware('permission:transactions_read');
            Route::get('/transactions/{id}', [\App\Http\Controllers\API\V1\Admin\TransactionController::class, 'show'])->middleware('permission:transactions_read');

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

            // Admin Ticket Management
            Route::get('/tickets', [\App\Http\Controllers\API\V1\Admin\TicketController::class, 'index'])->middleware('permission:tickets_read');
            Route::get('/tickets/{id}', [\App\Http\Controllers\API\V1\Admin\TicketController::class, 'show'])->middleware('permission:tickets_read');
            Route::post('/tickets/{id}/reply', [\App\Http\Controllers\API\V1\Admin\TicketController::class, 'reply'])->middleware('permission:tickets_update');
            Route::put('/tickets/{id}/status', [\App\Http\Controllers\API\V1\Admin\TicketController::class, 'updateStatus'])->middleware('permission:tickets_update');

            // Admin Location Management
            Route::get('/locations/divisions/{id}/districts', [\App\Http\Controllers\Api\V1\Admin\Location\DivisionController::class, 'districts'])->middleware('permission:locations_read');
            Route::get('/locations/districts/{id}/thanas', [\App\Http\Controllers\Api\V1\Admin\Location\DistrictController::class, 'thanas'])->middleware('permission:locations_read');

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
