<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController as V1AuthController;
use App\Http\Controllers\Api\V1\ForumController as V1ForumController;
use App\Http\Controllers\Api\V1\OrderController as V1OrderController;
use App\Http\Controllers\Api\V1\AppointmentController as V1AppointmentController;
use App\Http\Controllers\Api\V1\NotificationController as V1NotificationController;
use App\Http\Controllers\Api\V1\DashboardController as V1DashboardController;
use App\Http\Controllers\Api\V1\ActivityController as V1ActivityController;
use App\Http\Controllers\Api\V1\ProductController as V1ProductController;
use App\Http\Controllers\Api\V1\ReturnController as V1ReturnController;
use App\Http\Controllers\Api\V1\DisputeController as V1DisputeController;
use App\Http\Controllers\Api\V1\SearchController as V1SearchController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CustomerAuthApiController;
use App\Http\Controllers\Api\CustomerAiApiController;
use App\Http\Controllers\Api\CustomerCartApiController;
use App\Http\Controllers\Api\CustomerOrderApiController;
use App\Http\Controllers\Api\CustomerAppointmentApiController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminSettingsController;
use App\Http\Controllers\Api\AdminUsersController;
use App\Http\Controllers\Api\AdminVendorsController;
use App\Http\Controllers\Api\AdminVendorsListController;
use App\Http\Controllers\Api\AdminOrdersController;
use App\Http\Controllers\Api\AdminPayoutsController;
use App\Http\Controllers\Api\AdminCategoriesController;
use App\Http\Controllers\Api\AdminEmailTemplatesController;
use App\Http\Controllers\Admin\VendorApplicationController;

// =============================================================================
// API V1 (strict, versioned, standardized)
// =============================================================================
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/login', [V1AuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/auth/me', [V1AuthController::class, 'me']);
        Route::post('/auth/logout', [V1AuthController::class, 'logout']);

        // Forum module
        Route::prefix('forum')->group(function () {
            Route::get('/threads', [V1ForumController::class, 'index']);
            Route::get('/threads/{thread}', [V1ForumController::class, 'show']);
        });

        // Orders module
        Route::prefix('orders')->group(function () {
            Route::get('/', [V1OrderController::class, 'index']);
            Route::get('/{id}', [V1OrderController::class, 'show']);
        });

        // Products module
        Route::prefix('products')->group(function () {
            Route::get('/', [V1ProductController::class, 'index']);
            Route::get('/{id}', [V1ProductController::class, 'show']);
        });

        // Returns module
        Route::prefix('returns')->group(function () {
            Route::get('/', [V1ReturnController::class, 'index']);
            Route::get('/{id}', [V1ReturnController::class, 'show']);
            Route::post('/', [V1ReturnController::class, 'store']);
            Route::patch('/{id}/status', [V1ReturnController::class, 'updateStatus'])->middleware('api.role:admin');
        });

        // Disputes module
        Route::prefix('disputes')->group(function () {
            Route::get('/', [V1DisputeController::class, 'index']);
            Route::post('/', [V1DisputeController::class, 'store']);
            Route::post('/orders/{orderId}/escalate', [V1DisputeController::class, 'escalate']);
            Route::post('/orders/{orderId}/respond', [V1DisputeController::class, 'vendorRespond']);
            Route::post('/orders/{orderId}/resolve', [V1DisputeController::class, 'resolve']);
        });

        // Appointments module
        Route::prefix('appointments')->group(function () {
            Route::get('/', [V1AppointmentController::class, 'index']);
            Route::get('/{id}', [V1AppointmentController::class, 'show']);
        });

        // Notifications module
        Route::prefix('notifications')->group(function () {
            Route::get('/', [V1NotificationController::class, 'index']);
            Route::get('/unread-count', [V1NotificationController::class, 'unreadCount']);
            Route::patch('/{id}/read', [V1NotificationController::class, 'markRead']);
            Route::patch('/read-all', [V1NotificationController::class, 'markAllRead']);
        });

        // Dashboards module
        Route::prefix('dashboards')->group(function () {
            Route::get('/summary', [V1DashboardController::class, 'summary']);
        });

        // Universal search module
        Route::get('/search', [V1SearchController::class, 'index']);

        // Activity logs module (admin-only)
        Route::prefix('activity')->middleware('api.role:admin')->group(function () {
            Route::get('/logs', [V1ActivityController::class, 'index']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Plantix AI — API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by RouteServiceProvider within the "api" group.
|
| Roles: admin, vendor, customer, expert
| Auth:  Laravel Sanctum (Bearer token)
|
*/

// =============================================================================
// Stripe Webhooks  — NO auth, NO CSRF (api group is already CSRF-exempt)
// Stripe signs every request; signature is verified inside the controller.
// =============================================================================
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('api.stripe.webhook');

// =============================================================================
// Customer API  (prefix: /api/customer)
// =============================================================================
Route::prefix('customer')->group(function () {

    // ── Public auth (no token required) ──────────────────────────────────────
    Route::post('/auth/register',   [CustomerAuthApiController::class, 'register']);
    Route::post('/auth/login',      [CustomerAuthApiController::class, 'login']);
    Route::post('/password/forgot', [CustomerAuthApiController::class, 'forgotPassword']);
    Route::get('/csrf',             fn () => response()->noContent());

    // ── Available experts (public, for booking page) ──────────────────────────
    Route::get('/appointments/experts', [CustomerAppointmentApiController::class, 'experts']);

    // ── Protected (Bearer token required) ────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth & profile
        Route::post('/auth/logout',   [CustomerAuthApiController::class, 'logout']);
        Route::get('/auth/me',        [CustomerAuthApiController::class, 'me']);
        Route::put('/auth/profile',   [CustomerAuthApiController::class, 'updateProfile']);
        Route::post('/auth/password', [CustomerAuthApiController::class, 'changePassword']);

        // Cart
        Route::get('/cart',           [CustomerCartApiController::class, 'index']);
        Route::post('/cart/add',      [CustomerCartApiController::class, 'addItem']);
        Route::patch('/cart/{id}',    [CustomerCartApiController::class, 'updateItem']);
        Route::delete('/cart/{id}',   [CustomerCartApiController::class, 'removeItem']);
        Route::delete('/cart',        [CustomerCartApiController::class, 'clear']);
        Route::post('/cart/coupon',   [CustomerCartApiController::class, 'applyCoupon']);
        Route::delete('/cart/coupon', [CustomerCartApiController::class, 'removeCoupon']);

        // Orders
        Route::get('/orders',                [CustomerOrderApiController::class, 'index']);
        Route::get('/orders/{id}',           [CustomerOrderApiController::class, 'show']);
        Route::post('/orders/{id}/cancel',   [CustomerOrderApiController::class, 'cancel']);
        Route::post('/orders/{id}/return',   [CustomerOrderApiController::class, 'requestReturn']);
        Route::get('/orders/{id}/invoice',   [CustomerOrderApiController::class, 'invoice']);

        // Appointments
        Route::get('/appointments',                              [CustomerAppointmentApiController::class, 'index']);
        Route::post('/appointments',                             [CustomerAppointmentApiController::class, 'store']);
        Route::get('/appointments/slots',                        [CustomerAppointmentApiController::class, 'slots']);
        Route::get('/appointments/{id}',                         [CustomerAppointmentApiController::class, 'show']);
        Route::get('/appointments/{id}/check-payment',           [CustomerAppointmentApiController::class, 'checkPayment']);
        Route::post('/appointments/{id}/cancel',                 [CustomerAppointmentApiController::class, 'cancel']);
        Route::patch('/appointments/{id}/reschedule',            [CustomerAppointmentApiController::class, 'reschedule']);
        Route::post('/appointments/{id}/reschedule-response',    [CustomerAppointmentApiController::class, 'rescheduleResponse']);

        // AI & Agri tools
        Route::prefix('ai')->group(function () {
            Route::post('/crop-recommendation',        [CustomerAiApiController::class, 'cropRecommendation']);
            Route::post('/fertilizer-recommendation',  [CustomerAiApiController::class, 'fertilizerRecommendation']);
            Route::post('/disease-detection',          [CustomerAiApiController::class, 'diseaseDetectionStore']);
            Route::get('/disease-detection/{id}/status', [CustomerAiApiController::class, 'diseaseDetectionStatus']);
        });

        // Weather
        Route::get('/weather', [CustomerAiApiController::class, 'weather']);

        // Crop Plans
        Route::get('/crop-plans',  [CustomerAiApiController::class, 'cropPlansIndex']);
        Route::post('/crop-plans', [CustomerAiApiController::class, 'cropPlansStore']);

        // Notifications
        Route::get('/notifications',                          [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count',             [NotificationController::class, 'unreadCount']);
        Route::patch('/notifications/{notification}/read',    [NotificationController::class, 'markAsRead']);
        Route::patch('/notifications/mark-all-read',          [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{notification}',        [NotificationController::class, 'destroy']);
    });
});
// =============================================================================
// Admin API  (prefix: /api/admin)
// =============================================================================
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin,staff'])->group(function () {

    // Dashboard
    Route::get('/dashboard/stats',          [AdminDashboardController::class, 'stats'])->middleware('permission:reports,reports')->name('api.admin.dashboard.stats');
    Route::get('/dashboard/earnings',       [AdminDashboardController::class, 'earnings'])->middleware('permission:reports,reports')->name('api.admin.dashboard.earnings');
    Route::get('/dashboard/user-orders',    [AdminDashboardController::class, 'userOrders'])->middleware('permission:reports,reports')->name('api.admin.dashboard.user-orders');

    // Settings
    Route::get('/settings/currency',        [AdminSettingsController::class, 'currency'])->middleware('permission:global-setting,settings.app.globals')->name('api.admin.settings.currency');
    Route::get('/settings/placeholder',     [AdminSettingsController::class, 'placeholder'])->middleware('permission:global-setting,settings.app.globals')->name('api.admin.settings.placeholder');
    Route::get('/settings/global',          [AdminSettingsController::class, 'global'])->middleware('permission:global-setting,settings.app.globals')->name('api.admin.settings.global');
    Route::get('/settings/payment-methods', [AdminSettingsController::class, 'paymentMethods'])->middleware('permission:payment-method,payment-method')->name('api.admin.settings.payment-methods');

    // Users
    Route::get('/users',                                    [AdminUsersController::class, 'index'])->middleware('permission:users,users')->name('api.admin.users.index');
    Route::get('/users/{id}',                               [AdminUsersController::class, 'show'])->middleware('permission:users,users.view')->name('api.admin.users.show');
    Route::post('/users',                                   [AdminUsersController::class, 'store'])->middleware('permission:users,users.create')->name('api.admin.users.store');
    Route::post('/users/{id}',                              [AdminUsersController::class, 'update'])->middleware('permission:users,users.edit')->name('api.admin.users.update');
    Route::post('/users/{id}/send-password-reset',          [AdminUsersController::class, 'sendPasswordReset'])->middleware('permission:users,users.edit')->name('api.admin.users.send-password-reset');
    Route::patch('/users/{id}/toggle-active',               [AdminUsersController::class, 'toggleActive'])->middleware('permission:users,users.edit')->name('api.admin.users.toggle-active');
    Route::delete('/users/{id}',                            [AdminUsersController::class, 'destroy'])->middleware('permission:users,users.edit')->name('api.admin.users.destroy');

    // Vendors
    Route::get('/vendors/top',              [AdminVendorsController::class, 'top'])->middleware('permission:vendors,vendors')->name('api.admin.vendors.top');
    Route::get('/vendor-applications',      [VendorApplicationController::class, 'index'])->middleware('permission:vendors,vendors')->name('api.admin.vendor-applications.index');
    Route::get('/vendor-applications/{application}', [VendorApplicationController::class, 'show'])->middleware('permission:vendors,vendors.view')->name('api.admin.vendor-applications.show');
    Route::post('/vendor-applications/{application}/under-review', [VendorApplicationController::class, 'underReview'])->middleware('permission:vendors,vendors.edit')->name('api.admin.vendor-applications.under-review');
    Route::post('/vendor-applications/{application}/approve', [VendorApplicationController::class, 'approve'])->middleware('permission:vendors,vendors.edit')->name('api.admin.vendor-applications.approve');
    Route::post('/vendor-applications/{application}/reject', [VendorApplicationController::class, 'reject'])->middleware('permission:vendors,vendors.edit')->name('api.admin.vendor-applications.reject');
    Route::post('/vendor-applications/{application}/suspend', [VendorApplicationController::class, 'suspend'])->middleware('permission:vendors,vendors.edit')->name('api.admin.vendor-applications.suspend');

    // Orders (full CRUD + status management)
    Route::get('/orders',                   [AdminOrdersController::class, 'index'])->middleware('permission:orders,orders.view')->name('api.admin.orders.index');
    Route::get('/orders/recent',            [AdminOrdersController::class, 'recent'])->middleware('permission:orders,orders.view')->name('api.admin.orders.recent');
    Route::get('/orders/{id}',              [AdminOrdersController::class, 'show'])->middleware('permission:orders,orders.view')->name('api.admin.orders.show');
    Route::patch('/orders/{id}/status',     [AdminOrdersController::class, 'updateStatus'])->middleware('permission:orders,orders.status')->name('api.admin.orders.update-status');
    Route::post('/orders/{id}/cancel',      [AdminOrdersController::class, 'cancel'])->middleware('permission:orders,orders.status')->name('api.admin.orders.cancel');

    // Payouts
    Route::get('/payouts/recent',           [AdminPayoutsController::class, 'recent'])->middleware('permission:reports,reports')->name('api.admin.payouts.recent');

    // Categories
    Route::get('/categories',               [AdminCategoriesController::class, 'index'])->middleware('permission:category,categories')->name('api.admin.categories.index');
    Route::post('/categories',              [AdminCategoriesController::class, 'store'])->middleware('permission:category,categories.create')->name('api.admin.categories.store');
    Route::get('/categories/{id}',          [AdminCategoriesController::class, 'show'])->middleware('permission:category,categories')->name('api.admin.categories.show');
    Route::put('/categories/{id}',          [AdminCategoriesController::class, 'update'])->middleware('permission:category,categories.edit')->name('api.admin.categories.update');
    Route::delete('/categories/{id}',       [AdminCategoriesController::class, 'destroy'])->middleware('permission:category,categories.edit')->name('api.admin.categories.destroy');

    // Email Templates
    Route::get('/email-templates',          [AdminEmailTemplatesController::class, 'index'])->middleware('permission:email-template,email-templates.index')->name('api.admin.email-templates.index');
    Route::post('/email-templates',         [AdminEmailTemplatesController::class, 'store'])->middleware('permission:email-template,email-templates.edit')->name('api.admin.email-templates.store');
    Route::get('/email-templates/{id}',     [AdminEmailTemplatesController::class, 'show'])->middleware('permission:email-template,email-templates.index')->name('api.admin.email-templates.show');
    Route::put('/email-templates/{id}',     [AdminEmailTemplatesController::class, 'update'])->middleware('permission:email-template,email-templates.edit')->name('api.admin.email-templates.update');
    Route::delete('/email-templates/{id}',  [AdminEmailTemplatesController::class, 'destroy'])->middleware('permission:email-template,email-templates.delete')->name('api.admin.email-templates.destroy');

    // Vendors (list and operations)
    Route::get('/vendors-list',             [AdminVendorsListController::class, 'index'])->middleware('permission:vendors,vendors')->name('api.admin.vendors-list.index');
    Route::put('/vendors/{id}/status',      [AdminVendorsListController::class, 'updateStatus'])->middleware('permission:vendors,vendors.toggle')->name('api.admin.vendors.update-status');
    Route::delete('/vendors/{id}',          [AdminVendorsListController::class, 'destroy'])->middleware('permission:vendors,vendors.edit')->name('api.admin.vendors.destroy');
});