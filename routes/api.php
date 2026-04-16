<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\AdminAuthController;
use App\Http\Controllers\API\V1\AdminPaymentCredentialController;
use App\Http\Controllers\API\V1\SocialAuthController;
use App\Http\Controllers\API\V1\SettingsController;
use App\Http\Controllers\API\V1\Admin\CourierCredentialController;

// Social Auth (Matches Google Console: /API/auth/{provider}/callback)
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])->name('social.callback');

Route::prefix('V1')->group(function () {
    
    // Public User Auth & OTP
    Route::post('/send-otp', [\App\Http\Controllers\API\V1\OtpController::class, 'send']);
    Route::post('/verify-otp', [\App\Http\Controllers\API\V1\OtpController::class, 'verify']);
    
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/check-exists', [AuthController::class, 'checkExists']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Public Location API
    Route::get('/locations/divisions', [\App\Http\Controllers\API\V1\LocationController::class, 'divisions']);
    Route::get('/locations/districts', [\App\Http\Controllers\API\V1\LocationController::class, 'districts']);
    Route::get('/locations/thanas', [\App\Http\Controllers\API\V1\LocationController::class, 'thanas']);

    
    // Public Settings API
    Route::get('/settings/maintenance', [SettingsController::class, 'maintenance']);
    Route::get('/settings/cookie', [SettingsController::class, 'cookieSettings']);
    Route::get('/settings/footer', [SettingsController::class, 'footer']);
    Route::get('/settings/standard', [SettingsController::class, 'standard']);
    Route::get('/settings/currency', [SettingsController::class, 'currency']);
    Route::get('/settings/social-auth-status', [SettingsController::class, 'socialAuthStatus']);
    Route::get('/settings/cod', [SettingsController::class, 'cod']);
    Route::get('/settings/gateways', [SettingsController::class, 'gateways']);

    // Public Product catalog
    Route::get('/products', [\App\Http\Controllers\API\V1\ProductController::class, 'index']);
    Route::get('/products/filters', [\App\Http\Controllers\API\V1\ProductController::class, 'filters']);
    Route::get('/products/{slug}', [\App\Http\Controllers\API\V1\ProductController::class, 'show']);
    Route::get('/banners', [\App\Http\Controllers\API\V1\BannerController::class, 'index']);
    Route::get('/search/suggestions', [\App\Http\Controllers\API\V1\SearchController::class, 'suggestions']);
    Route::post('/search/log', [\App\Http\Controllers\API\V1\SearchController::class, 'log']);
    Route::get('/search/trending', [\App\Http\Controllers\API\V1\SearchController::class, 'trending']);
    Route::get('/flash-sales/active', [\App\Http\Controllers\API\V1\FlashSaleController::class, 'active']);
    Route::get('/flash-sales/featured', [\App\Http\Controllers\API\V1\FlashSaleController::class, 'featured']);
    Route::get('/products/{id}/reviews', [\App\Http\Controllers\API\V1\ReviewController::class, 'productReviews']);

    // Public Maintenance status
    Route::get('/maintenance', [\App\Http\Controllers\API\V1\SettingsController::class, 'maintenance']);

    // Public Currency settings
    Route::get('/currency', [\App\Http\Controllers\API\V1\SettingsController::class, 'currency']);

    // Public Cookie settings
    Route::get('/cookie-settings', [\App\Http\Controllers\API\V1\SettingsController::class, 'cookieSettings']);

    // Public Footer settings
    Route::get('/footer', [\App\Http\Controllers\API\V1\SettingsController::class, 'footer']);

    // Public Site Standard Information (Logo, SEO, etc.)
    Route::get('/settings/standard', [\App\Http\Controllers\API\V1\SettingsController::class, 'standard']);
    Route::get('/settings/social-auth', [\App\Http\Controllers\API\V1\SettingsController::class, 'socialAuthStatus']);
    Route::get('/announcements/active', [\App\Http\Controllers\API\V1\AnnouncementController::class, 'active']);


    // Public Category & Brand catalog
    Route::get('/categories', [\App\Http\Controllers\API\V1\CategoryController::class, 'index']);
    Route::get('/categories/{id}/subcategories', [\App\Http\Controllers\API\V1\CategoryController::class, 'subcategories']);
    Route::get('/categories/{slug}', [\App\Http\Controllers\API\V1\CategoryController::class, 'show']);
    Route::get('/brands', [\App\Http\Controllers\API\V1\BrandController::class, 'index']);
    Route::get('/brands/{slug}', [\App\Http\Controllers\API\V1\BrandController::class, 'show']);

    // Newsletter
    Route::post('/subscribe', [\App\Http\Controllers\API\V1\SubscriberController::class, 'subscribe']);
    Route::get('/unsubscribe', [\App\Http\Controllers\API\V1\SubscriberController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

    // Cart System (Guest + Auth mapped by X-Cart-Session)
    Route::get('/cart', [\App\Http\Controllers\API\V1\CartController::class, 'index']);
    Route::post('/cart/items', [\App\Http\Controllers\API\V1\CartController::class, 'add']);
    Route::put('/cart/items/{itemId}', [\App\Http\Controllers\API\V1\CartController::class, 'updateQuantity']);
    Route::delete('/cart/items/{itemId}', [\App\Http\Controllers\API\V1\CartController::class, 'remove']);
    Route::post('/cart/apply-coupon', [\App\Http\Controllers\API\V1\CartController::class, 'applyCoupon']);
    Route::delete('/cart/remove-coupon', [\App\Http\Controllers\API\V1\CartController::class, 'removeCoupon']);
    
    // Wishlist System (Guest + Auth handled in Controller)
    Route::get('/wishlists', [\App\Http\Controllers\API\V1\WishlistController::class, 'index']);
    Route::post('/wishlists/toggle', [\App\Http\Controllers\API\V1\WishlistController::class, 'toggle']);

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
        Route::get('/addresses', [\App\Http\Controllers\API\V1\UserAddressController::class, 'index']);
        Route::get('/addresses/default', [\App\Http\Controllers\API\V1\UserAddressController::class, 'default']);
        Route::post('/addresses', [\App\Http\Controllers\API\V1\UserAddressController::class, 'store']);
        Route::put('/addresses/{id}', [\App\Http\Controllers\API\V1\UserAddressController::class, 'update']);
        Route::delete('/addresses/{id}', [\App\Http\Controllers\API\V1\UserAddressController::class, 'destroy']);
        Route::put('/addresses/{id}/default', [\App\Http\Controllers\API\V1\UserAddressController::class, 'setDefault']);

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\API\V1\NotificationController::class, 'index']);
        Route::put('/notifications/read-all', [\App\Http\Controllers\API\V1\NotificationController::class, 'markAllAsRead']);
        Route::put('/notifications/{id}/read', [\App\Http\Controllers\API\V1\NotificationController::class, 'markAsRead']);

        // Dashboard Stats
        Route::get('/dashboard/stats', [\App\Http\Controllers\API\V1\DashboardController::class, 'stats']);
        Route::get('/dashboard/activity', [\App\Http\Controllers\API\V1\DashboardController::class, 'activity']);

        
        // Orders & Checkout seamlessly mapping
        Route::post('/checkout', [\App\Http\Controllers\API\V1\OrderController::class, 'checkout']);
        Route::get('/orders', [\App\Http\Controllers\API\V1\OrderController::class, 'index']);
        Route::get('/orders/{id}', [\App\Http\Controllers\API\V1\OrderController::class, 'show']);
        Route::get('/orders/{id}/track', [\App\Http\Controllers\API\V1\OrderController::class, 'track']);
        Route::get('/orders/{id}/invoice', [\App\Http\Controllers\API\V1\InvoiceController::class, 'downloadUserInvoice']);

        // Support Tickets
        Route::get('/tickets', [\App\Http\Controllers\API\V1\TicketController::class, 'index']);
        Route::post('/tickets', [\App\Http\Controllers\API\V1\TicketController::class, 'store']);
        Route::get('/tickets/{id}', [\App\Http\Controllers\API\V1\TicketController::class, 'show']);
        Route::post('/tickets/{id}/reply', [\App\Http\Controllers\API\V1\TicketController::class, 'reply']);

        // Product Reviews
        Route::post('/reviews', [\App\Http\Controllers\API\V1\ReviewController::class, 'store']);
        Route::get('/reviews/my', [\App\Http\Controllers\API\V1\ReviewController::class, 'myReviews']);

        // Payment Interfaces
        Route::post('/payment/initiate', [\App\Http\Controllers\API\V1\PaymentController::class, 'initiate']);
    });

    // Public Payment verification (outside auth for guest/redirect support)
    Route::post('/payment/{gateway}/verify', [\App\Http\Controllers\API\V1\PaymentController::class, 'verify']);
    Route::get('/payment/{gateway}/verify', [\App\Http\Controllers\API\V1\PaymentController::class, 'verify']);

    // Public payment webhooks (called by UddoktaPay server)
    Route::post('/payment/{gateway}/webhook', [\App\Http\Controllers\API\V1\PaymentController::class, 'webhook']);
    Route::post('/payment/uddoktapay/webhook', [\App\Http\Controllers\API\V1\PaymentController::class, 'webhook'])->defaults('gateway', 'uddoktapay');

    // Public Courier Webhooks (called by courier servers — no auth middleware)
    Route::post('/webhooks/steadfast', [\App\Http\Controllers\API\V1\Webhook\SteadfastWebhookController::class, 'handle'])
        ->name('webhooks.steadfast');

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
            // Route::get('/reviews', [ProductReviewController::class, 'index']);

            // Newsletter
            Route::post('/change-password', [\App\Http\Controllers\API\V1\Admin\PasswordController::class, 'changePassword']);

            // Admin Profile Management
            Route::get('/profile', [\App\Http\Controllers\API\V1\Admin\AdminProfileController::class, 'show']);
            Route::put('/profile', [\App\Http\Controllers\API\V1\Admin\AdminProfileController::class, 'update']);
            Route::post('/profile/avatar', [\App\Http\Controllers\API\V1\Admin\AdminProfileController::class, 'uploadAvatar']);
            Route::get('/profile/activities', [\App\Http\Controllers\API\V1\Admin\AdminProfileController::class, 'activities']);

            // Spatie Role & Permission Administration
            Route::apiResource('roles', \App\Http\Controllers\API\V1\RoleController::class);
            Route::apiResource('permissions', \App\Http\Controllers\API\V1\PermissionController::class);

            // Newsletter Management
            Route::get('/subscribers', [\App\Http\Controllers\API\V1\Admin\SubscriberController::class, 'index'])->middleware('permission:newsletter_read');
            Route::delete('/subscribers/{subscriber}', [\App\Http\Controllers\API\V1\Admin\SubscriberController::class, 'destroy'])->middleware('permission:newsletter_delete');
            Route::post('/subscribers/send-email', [\App\Http\Controllers\API\V1\Admin\SubscriberController::class, 'sendEmail'])->middleware('permission:newsletter_update');
            
            // Campaign Management
            Route::get('/campaigns', [\App\Http\Controllers\API\V1\Admin\CampaignController::class, 'index'])->middleware('permission:campaigns_read');
            Route::get('/campaigns/{campaign}', [\App\Http\Controllers\API\V1\Admin\CampaignController::class, 'show'])->middleware('permission:campaigns_read');
            
            // Banner Management
            Route::apiResource('banners', \App\Http\Controllers\API\V1\Admin\BannerController::class)->middleware('permission:banners_read|banners_create|banners_update|banners_delete');
            Route::patch('banners/{banner}/toggle-status', [\App\Http\Controllers\API\V1\Admin\BannerController::class, 'toggleStatus'])->middleware('permission:banners_update');
            
            // FAQ Management
            Route::post('faqs/bulk-delete', [\App\Http\Controllers\API\V1\Admin\FaqController::class, 'bulkDelete'])->middleware('permission:faqs_delete');
            Route::post('faqs/reorder', [\App\Http\Controllers\API\V1\Admin\FaqController::class, 'reorder'])->middleware('permission:faqs_update');
            Route::apiResource('faqs', \App\Http\Controllers\API\V1\Admin\FaqController::class)->middleware('permission:faqs_read|faqs_create|faqs_update|faqs_delete');
            Route::middleware(['permission:users_read|users_create|users_update|users_delete'])->group(function() {
                Route::post('admins/{id}/assign-roles', [\App\Http\Controllers\API\V1\AdminRoleController::class, 'assignRoles'])->middleware('permission:roles_update');
                
                // Admin Management
                Route::apiResource('admins', \App\Http\Controllers\API\V1\AdminUserController::class)->middleware('permission:users_read|users_create|users_update|users_delete');
                Route::put('admins/{id}/status', [\App\Http\Controllers\API\V1\AdminUserController::class, 'toggleStatus'])->middleware('permission:users_update');
            });

            Route::middleware(['permission:credentials_read|credentials_create|credentials_update|credentials_delete'])->group(function() {
                Route::apiResource('payment-credentials', AdminPaymentCredentialController::class);
                
                // Courier specific actions
                Route::get('couriers/steadfast/balance', [\App\Http\Controllers\API\V1\Admin\CourierController::class, 'getBalance']);
                Route::post('orders/{order}/return', [\App\Http\Controllers\API\V1\Admin\CourierController::class, 'createReturn'])->middleware('role:super_admin|permission:orders_update');

                // Credential Management for Courier and Payment
                Route::get('credentials/couriers', [CourierCredentialController::class, 'index']);
                Route::post('credentials/couriers', [CourierCredentialController::class, 'store']);
                Route::post('credentials/couriers/webhook-token/generate', [CourierCredentialController::class, 'generateWebhookToken']);
                Route::get('credentials/payments', [\App\Http\Controllers\PaymentCredentialController::class, 'index']);
                Route::post('credentials/payments', [\App\Http\Controllers\PaymentCredentialController::class, 'store']);
            });

            Route::middleware(['permission:sitemap_read|sitemap_create'])->group(function() {
                // Sitemap Management
                Route::get('sitemap/status', [\App\Http\Controllers\API\V1\Admin\SitemapController::class, 'getStatus']);
                Route::post('sitemap/generate', [\App\Http\Controllers\API\V1\Admin\SitemapController::class, 'generate']);
            });

            Route::middleware(['permission:backups_read|backups_create|backups_delete'])->group(function() {
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
            Route::get('/orders/{id}/invoice', [\App\Http\Controllers\API\V1\InvoiceController::class, 'downloadAdminInvoice'])->middleware('permission:orders_read');
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

            // Admin Supplier Management
            Route::apiResource('/suppliers', \App\Http\Controllers\API\V1\Admin\SupplierController::class)->middleware('permission:suppliers_read|suppliers_create|suppliers_update|suppliers_delete');
            Route::post('/suppliers/{id}/attach-products', [\App\Http\Controllers\API\V1\Admin\SupplierController::class, 'attachProducts'])->middleware('permission:suppliers_update');

            // Admin Purchase Management
            Route::post('/purchases/{id}/receive', [\App\Http\Controllers\API\V1\Admin\PurchaseController::class, 'receive'])->middleware('permission:purchases_update');
            Route::apiResource('/purchases', \App\Http\Controllers\API\V1\Admin\PurchaseController::class)->middleware('permission:purchases_read|purchases_create|purchases_update|purchases_delete');

            Route::get('/inventory', [\App\Http\Controllers\API\V1\Admin\InventoryController::class, 'index'])->middleware('permission:inventory_read');
            Route::post('/inventory/adjust', [\App\Http\Controllers\API\V1\Admin\InventoryController::class, 'adjust'])->middleware('permission:inventory_update');
            Route::get('/inventory/history/{sku?}', [\App\Http\Controllers\API\V1\Admin\InventoryController::class, 'history'])->middleware('permission:stock_movements_read');

            // Admin Stock Adjustment (Returns & Damages)
            Route::apiResource('/stock-adjustments', \App\Http\Controllers\API\V1\Admin\StockAdjustmentController::class)->only(['index', 'store', 'show'])->middleware('permission:stock_adjustments_read|stock_adjustments_create');

            // Admin Inventory Reports
            Route::prefix('reports/inventory')->group(function () {
                Route::get('/stock', [\App\Http\Controllers\API\V1\Admin\InventoryReportController::class, 'stockReport']);
                Route::get('/stats', [\App\Http\Controllers\API\V1\Admin\InventoryReportController::class, 'inventoryStats']);
                Route::get('/profit-loss', [\App\Http\Controllers\API\V1\Admin\InventoryReportController::class, 'profitLoss']);
            })->middleware('permission:reports_read');

            // Admin Settings
            Route::get('/settings/{group}', [\App\Http\Controllers\API\V1\Admin\SettingsController::class, 'getByGroup']);
            Route::put('/settings/{group}', [\App\Http\Controllers\API\V1\Admin\SettingsController::class, 'updateGroup'])->middleware('permission:manage_settings');
            Route::post('/settings/email/test', [\App\Http\Controllers\API\V1\Admin\SettingsController::class, 'testEmail'])->middleware('permission:manage_settings');
            Route::post('/settings/sms/test', [\App\Http\Controllers\API\V1\Admin\SettingsController::class, 'testSms'])->middleware('permission:manage_settings');

            // Admin Activity Logs
            Route::get('/activity-logs', [\App\Http\Controllers\API\V1\Admin\ActivityLogController::class, 'index'])->middleware('permission:activity_logs_read');

            // Admin Announcement Management
            Route::apiResource('/announcements', \App\Http\Controllers\API\V1\Admin\AnnouncementController::class)->middleware('permission:manage_settings');
            Route::put('/announcements/{announcement}/toggle-status', [\App\Http\Controllers\API\V1\Admin\AnnouncementController::class, 'toggleStatus'])->middleware('permission:manage_settings');
 
            // Admin Flash Sale Management
            Route::apiResource('/flash-sales', \App\Http\Controllers\API\V1\Admin\FlashSaleController::class)->middleware('permission:flash_sales_read|flash_sales_create|flash_sales_update|flash_sales_delete');

            // Admin Review Management
            Route::get('/reviews', [\App\Http\Controllers\API\V1\Admin\ReviewController::class, 'index'])->middleware('permission:manage_reviews');
            Route::put('/reviews/{id}/toggle-status', [\App\Http\Controllers\API\V1\Admin\ReviewController::class, 'toggleStatus'])->middleware('permission:manage_reviews');
            Route::delete('/reviews/{id}', [\App\Http\Controllers\API\V1\Admin\ReviewController::class, 'destroy'])->middleware('permission:manage_reviews');

            // Admin Transaction Management
            Route::get('/transactions', [\App\Http\Controllers\API\V1\Admin\TransactionController::class, 'index'])->middleware('permission:transactions_read');
            Route::post('/transactions', [\App\Http\Controllers\API\V1\Admin\TransactionController::class, 'store'])->middleware('permission:transactions_create');
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
                Route::get('/posts/{id}', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'show'])->middleware('permission:blog_posts_read');
                Route::post('/posts', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'createPost'])->middleware('permission:blog_posts_create');
                Route::put('/posts/{id}', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'updatePost'])->middleware('permission:blog_posts_update');
                Route::delete('/posts/{id}', [\App\Http\Controllers\API\V1\Admin\BlogController::class, 'deletePost'])->middleware('permission:blog_posts_delete');

                Route::get('/comments', [\App\Http\Controllers\API\V1\Admin\BlogCommentController::class, 'index'])->middleware('permission:blog_comments_read');
                Route::put('/comments/{id}/status', [\App\Http\Controllers\API\V1\Admin\BlogCommentController::class, 'updateStatus'])->middleware('permission:blog_comments_update');
                Route::delete('/comments/{id}', [\App\Http\Controllers\API\V1\Admin\BlogCommentController::class, 'destroy'])->middleware('permission:blog_comments_delete');
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
            Route::get('/locations/divisions/{id}/districts', [\App\Http\Controllers\API\V1\Admin\Location\DivisionController::class, 'districts'])->middleware('permission:delivery_read');
            Route::get('/locations/districts/{id}/thanas', [\App\Http\Controllers\API\V1\Admin\Location\DistrictController::class, 'thanas'])->middleware('permission:delivery_read');


            foreach (['divisions' => \App\Http\Controllers\API\V1\Admin\Location\DivisionController::class,
                      'districts' => \App\Http\Controllers\API\V1\Admin\Location\DistrictController::class,
                      'thanas' => \App\Http\Controllers\API\V1\Admin\Location\ThanaController::class] as $route => $controller) {

                Route::apiResource("locations/{$route}", $controller)->only(['index', 'show'])->middleware('permission:delivery_read');
                Route::apiResource("locations/{$route}", $controller)->only(['store'])->middleware('permission:delivery_create');
                Route::apiResource("locations/{$route}", $controller)->only(['update'])->middleware('permission:delivery_update');
                Route::apiResource("locations/{$route}", $controller)->only(['destroy'])->middleware('permission:delivery_delete');
            }
        });
    });

    // Public Blog & CMS
    Route::get('/blog/categories', [\App\Http\Controllers\API\V1\BlogController::class, 'getCategories']);
    Route::get('/blog/tags', [\App\Http\Controllers\API\V1\BlogController::class, 'getTags']);
    Route::get('/blog/posts', [\App\Http\Controllers\API\V1\BlogController::class, 'getPosts']);
    Route::get('/blog/posts/{slug}', [\App\Http\Controllers\API\V1\BlogController::class, 'showPost']);
    Route::post('/blog/posts/{slug}/comments', [\App\Http\Controllers\API\V1\BlogController::class, 'storeComment'])->middleware(['auth:api', 'user.active']);
    Route::get('/pages/{slug}', [\App\Http\Controllers\API\V1\PageController::class, 'show']);
});
