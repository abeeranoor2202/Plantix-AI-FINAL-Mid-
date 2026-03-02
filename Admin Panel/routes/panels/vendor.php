<?php

/*
|=============================================================================
| Plantix AI — Vendor / Store Panel Routes  (/vendor/*)
|=============================================================================
|
| Auth guard : 'vendor'   (see config/auth.php)
| Middleware : EnsureVendorGuard  →  alias 'vendor.auth'
|
| Vendors manage their own store, products, orders, earnings, and coupons.
| All routes are scoped to the authenticated vendor – controllers enforce
| ownership so a vendor can never access another vendor's data.
|
*/

use Illuminate\Support\Facades\Route;

Route::prefix('vendor')->name('vendor.')->group(function () {

    // ── Vendor Auth (guest-only) ──────────────────────────────────────────────
    Route::middleware('guest:vendor')->group(function () {
        Route::get('/login',  [\App\Http\Controllers\Vendor\Auth\VendorLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Vendor\Auth\VendorLoginController::class, 'login'])->middleware('throttle:5,1');

        // Password reset
        Route::get('/password/forgot',        [\App\Http\Controllers\Vendor\Auth\VendorForgotPasswordController::class, 'showLinkRequestForm'])->name('password.forgot');
        Route::post('/password/email',         [\App\Http\Controllers\Vendor\Auth\VendorForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/password/reset/{token}', [\App\Http\Controllers\Vendor\Auth\VendorResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/password/reset',         [\App\Http\Controllers\Vendor\Auth\VendorResetPasswordController::class, 'reset'])->name('password.update');
    });

    Route::post('/logout', [\App\Http\Controllers\Vendor\Auth\VendorLoginController::class, 'logout'])->name('logout');

    // ── Protected Vendor Routes  [EnsureVendorGuard] ──────────────────────────
    Route::middleware('vendor.auth')->group(function () {

        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Vendor\VendorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/',          [\App\Http\Controllers\Vendor\VendorDashboardController::class, 'index'])->name('home');

        // ── Products (vendor-scoped) ──────────────────────────────────────────
        Route::get('/products',            [\App\Http\Controllers\Vendor\VendorProductController::class, 'index'])->name('products.index');
        Route::get('/products/create',     [\App\Http\Controllers\Vendor\VendorProductController::class, 'create'])->name('products.create');
        Route::post('/products',           [\App\Http\Controllers\Vendor\VendorProductController::class, 'store'])->name('products.store');
        Route::get('/products/{id}',       [\App\Http\Controllers\Vendor\VendorProductController::class, 'show'])->name('products.show');
        Route::get('/products/{id}/edit',  [\App\Http\Controllers\Vendor\VendorProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{id}',       [\App\Http\Controllers\Vendor\VendorProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}',    [\App\Http\Controllers\Vendor\VendorProductController::class, 'destroy'])->name('products.destroy');

        // ── Orders (vendor-scoped) ────────────────────────────────────────────
        Route::get('/orders',               [\App\Http\Controllers\Vendor\VendorOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}',          [\App\Http\Controllers\Vendor\VendorOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/status',  [\App\Http\Controllers\Vendor\VendorOrderController::class, 'updateStatus'])->name('orders.status');

        // ── Coupons (vendor-scoped) ───────────────────────────────────────────
        Route::get('/coupons',               [\App\Http\Controllers\Vendor\VendorCouponController::class, 'index'])->name('coupons.index');
        Route::get('/coupons/create',        [\App\Http\Controllers\Vendor\VendorCouponController::class, 'create'])->name('coupons.create');
        Route::post('/coupons',              [\App\Http\Controllers\Vendor\VendorCouponController::class, 'store'])->name('coupons.store');
        Route::get('/coupons/{id}/edit',     [\App\Http\Controllers\Vendor\VendorCouponController::class, 'edit'])->name('coupons.edit');
        Route::put('/coupons/{id}',          [\App\Http\Controllers\Vendor\VendorCouponController::class, 'update'])->name('coupons.update');
        Route::delete('/coupons/{id}',       [\App\Http\Controllers\Vendor\VendorCouponController::class, 'destroy'])->name('coupons.destroy');
        Route::post('/coupons/{id}/toggle',  [\App\Http\Controllers\Vendor\VendorCouponController::class, 'toggle'])->name('coupons.toggle');
        // ── Categories & Attributes (read-only, admin-created) ────────────────────
        Route::get('/categories',  [\App\Http\Controllers\Vendor\VendorCategoryController::class, 'index'])->name('categories.index');
        Route::get('/attributes',  [\App\Http\Controllers\Vendor\VendorCategoryController::class, 'attributes'])->name('attributes.index');

        // ── Returns & Refunds (vendor view) ─────────────────────────────────────
        Route::get('/returns',                  [\App\Http\Controllers\Vendor\VendorReturnController::class, 'index'])->name('returns.index');
        Route::get('/returns/{id}',             [\App\Http\Controllers\Vendor\VendorReturnController::class, 'show'])->name('returns.show');
        Route::post('/returns/{id}/note',       [\App\Http\Controllers\Vendor\VendorReturnController::class, 'addNote'])->name('returns.note');
        Route::post('/returns/{id}/approve',    [\App\Http\Controllers\Vendor\VendorReturnController::class, 'approve'])->name('returns.approve');
        Route::post('/returns/{id}/reject',     [\App\Http\Controllers\Vendor\VendorReturnController::class, 'reject'])->name('returns.reject');

        // ── Return Reasons (configurable) ────────────────────────────────────────
        Route::get('/return-reasons',                    [\App\Http\Controllers\Vendor\VendorReturnReasonController::class, 'index'])->name('return-reasons.index');
        Route::post('/return-reasons',                   [\App\Http\Controllers\Vendor\VendorReturnReasonController::class, 'store'])->name('return-reasons.store');
        Route::patch('/return-reasons/{id}',             [\App\Http\Controllers\Vendor\VendorReturnReasonController::class, 'update'])->name('return-reasons.update');
        Route::patch('/return-reasons/{id}/toggle',      [\App\Http\Controllers\Vendor\VendorReturnReasonController::class, 'toggle'])->name('return-reasons.toggle');
        Route::delete('/return-reasons/{id}',            [\App\Http\Controllers\Vendor\VendorReturnReasonController::class, 'destroy'])->name('return-reasons.destroy');

        // ── Product Reviews (vendor view) ─────────────────────────────────────
        Route::get('/reviews',       [\App\Http\Controllers\Vendor\VendorReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{id}',  [\App\Http\Controllers\Vendor\VendorReviewController::class, 'show'])->name('reviews.show');

        // ── Inventory / Stock Management ────────────────────────────────────────
        Route::get('/inventory',                     [\App\Http\Controllers\Vendor\VendorInventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/{id}/update',        [\App\Http\Controllers\Vendor\VendorInventoryController::class, 'update'])->name('inventory.update');
        // ── Profile & Store Settings ──────────────────────────────────────────
        Route::get('/profile',            [\App\Http\Controllers\Vendor\VendorProfileController::class, 'show'])->name('profile');
        Route::put('/profile',            [\App\Http\Controllers\Vendor\VendorProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/store',      [\App\Http\Controllers\Vendor\VendorProfileController::class, 'updateStore'])->name('profile.store.update');
        Route::post('/profile/password',  [\App\Http\Controllers\Vendor\VendorProfileController::class, 'changePassword'])->name('profile.password');

    }); // end vendor.auth
}); // end /vendor prefix
