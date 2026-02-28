<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CustomerAuthApiController;
use App\Http\Controllers\Api\CustomerCartApiController;
use App\Http\Controllers\Api\CustomerOrderApiController;
use App\Http\Controllers\Api\CustomerAppointmentApiController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminSettingsController;
use App\Http\Controllers\Api\AdminUsersController;
use App\Http\Controllers\Api\AdminVendorsController;
use App\Http\Controllers\Api\AdminVendorsListController;
use App\Http\Controllers\Api\AdminOrdersController;
use App\Http\Controllers\Api\AdminPayoutsController;
use App\Http\Controllers\Api\AdminCategoriesController;
use App\Http\Controllers\Api\AdminEmailTemplatesController;

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
        Route::post('/cart/add',      [CustomerCartApiController::class, 'add']);
        Route::patch('/cart/{id}',    [CustomerCartApiController::class, 'update']);
        Route::delete('/cart/{id}',   [CustomerCartApiController::class, 'remove']);
        Route::delete('/cart',        [CustomerCartApiController::class, 'clear']);
        Route::post('/cart/coupon',   [CustomerCartApiController::class, 'applyCoupon']);
        Route::delete('/cart/coupon', [CustomerCartApiController::class, 'removeCoupon']);

        // Orders
        Route::get('/orders',                [CustomerOrderApiController::class, 'index']);
        Route::get('/orders/{id}',           [CustomerOrderApiController::class, 'show']);
        Route::post('/orders/{id}/cancel',   [CustomerOrderApiController::class, 'cancel']);
        Route::post('/orders/{id}/return',   [CustomerOrderApiController::class, 'requestReturn']);

        // Appointments
        Route::get('/appointments',                      [CustomerAppointmentApiController::class, 'index']);
        Route::post('/appointments',                     [CustomerAppointmentApiController::class, 'store']);
        Route::get('/appointments/{id}',                 [CustomerAppointmentApiController::class, 'show']);
        Route::post('/appointments/{id}/cancel',         [CustomerAppointmentApiController::class, 'cancel']);
        Route::patch('/appointments/{id}/reschedule',    [CustomerAppointmentApiController::class, 'reschedule']);

        // Notifications
        Route::get('/notifications',                          [NotificationController::class, 'index']);
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
    Route::get('/dashboard/stats',          [AdminDashboardController::class, 'stats'])->name('api.admin.dashboard.stats');
    Route::get('/dashboard/earnings',       [AdminDashboardController::class, 'earnings'])->name('api.admin.dashboard.earnings');
    Route::get('/dashboard/user-orders',    [AdminDashboardController::class, 'userOrders'])->name('api.admin.dashboard.user-orders');

    // Settings
    Route::get('/settings/currency',        [AdminSettingsController::class, 'currency'])->name('api.admin.settings.currency');
    Route::get('/settings/placeholder',     [AdminSettingsController::class, 'placeholder'])->name('api.admin.settings.placeholder');
    Route::get('/settings/global',          [AdminSettingsController::class, 'global'])->name('api.admin.settings.global');
    Route::get('/settings/payment-methods', [AdminSettingsController::class, 'paymentMethods'])->name('api.admin.settings.payment-methods');

    // Users
    Route::get('/users',                                    [AdminUsersController::class, 'index'])->name('api.admin.users.index');
    Route::get('/users/{id}',                               [AdminUsersController::class, 'show'])->name('api.admin.users.show');
    Route::post('/users',                                   [AdminUsersController::class, 'store'])->name('api.admin.users.store');
    Route::post('/users/{id}',                              [AdminUsersController::class, 'update'])->name('api.admin.users.update');
    Route::post('/users/{id}/send-password-reset',          [AdminUsersController::class, 'sendPasswordReset'])->name('api.admin.users.send-password-reset');
    Route::post('/users/{id}/wallet-topup',                 [AdminUsersController::class, 'walletTopup'])->name('api.admin.users.wallet-topup');
    Route::patch('/users/{id}/toggle-active',               [AdminUsersController::class, 'toggleActive'])->name('api.admin.users.toggle-active');
    Route::delete('/users/{id}',                            [AdminUsersController::class, 'destroy'])->name('api.admin.users.destroy');

    // Vendors
    Route::get('/vendors/top',              [AdminVendorsController::class, 'top'])->name('api.admin.vendors.top');

    // Orders (full CRUD + status management)
    Route::get('/orders',                   [AdminOrdersController::class, 'index'])->name('api.admin.orders.index');
    Route::get('/orders/recent',            [AdminOrdersController::class, 'recent'])->name('api.admin.orders.recent');
    Route::get('/orders/{id}',              [AdminOrdersController::class, 'show'])->name('api.admin.orders.show');
    Route::patch('/orders/{id}/status',     [AdminOrdersController::class, 'updateStatus'])->name('api.admin.orders.update-status');
    Route::post('/orders/{id}/cancel',      [AdminOrdersController::class, 'cancel'])->name('api.admin.orders.cancel');

    // Payouts
    Route::get('/payouts/recent',           [AdminPayoutsController::class, 'recent'])->name('api.admin.payouts.recent');

    // Categories
    Route::get('/categories',               [AdminCategoriesController::class, 'index'])->name('api.admin.categories.index');
    Route::post('/categories',              [AdminCategoriesController::class, 'store'])->name('api.admin.categories.store');
    Route::get('/categories/{id}',          [AdminCategoriesController::class, 'show'])->name('api.admin.categories.show');
    Route::put('/categories/{id}',          [AdminCategoriesController::class, 'update'])->name('api.admin.categories.update');
    Route::delete('/categories/{id}',       [AdminCategoriesController::class, 'destroy'])->name('api.admin.categories.destroy');

    // Email Templates
    Route::get('/email-templates',          [AdminEmailTemplatesController::class, 'index'])->name('api.admin.email-templates.index');
    Route::post('/email-templates',         [AdminEmailTemplatesController::class, 'store'])->name('api.admin.email-templates.store');
    Route::get('/email-templates/{id}',     [AdminEmailTemplatesController::class, 'show'])->name('api.admin.email-templates.show');
    Route::put('/email-templates/{id}',     [AdminEmailTemplatesController::class, 'update'])->name('api.admin.email-templates.update');
    Route::delete('/email-templates/{id}',  [AdminEmailTemplatesController::class, 'destroy'])->name('api.admin.email-templates.destroy');

    // Vendors (list and operations)
    Route::get('/vendors-list',             [AdminVendorsListController::class, 'index'])->name('api.admin.vendors-list.index');
    Route::put('/vendors/{id}/status',      [AdminVendorsListController::class, 'updateStatus'])->name('api.admin.vendors.update-status');
    Route::delete('/vendors/{id}',          [AdminVendorsListController::class, 'destroy'])->name('api.admin.vendors.destroy');
});