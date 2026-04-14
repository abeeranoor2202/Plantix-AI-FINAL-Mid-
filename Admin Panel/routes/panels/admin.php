<?php

/*
|=============================================================================
| Plantix AI — Admin Panel Routes  (/admin/*)
|=============================================================================
|
| Auth guard : 'admin'   (see config/auth.php)
| Middleware : EnsureAdminGuard  →  alias 'admin'
| RBAC       : PermissionMiddleware  →  alias 'permission'
|              Super-admins (role=admin, no role_id) bypass all RBAC checks.
|
| Roles: admin, vendor, customer, expert  (strict 4-role RBAC)
|
*/

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    // ── Admin Auth (guest-only) ───────────────────────────────────────────────
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login',  [\App\Http\Controllers\Admin\Auth\AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Admin\Auth\AdminLoginController::class, 'login'])->middleware('throttle:3,1');

        Route::get('/password/email',         [\App\Http\Controllers\Admin\Auth\AdminForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/password/email',        [\App\Http\Controllers\Admin\Auth\AdminForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/password/reset/{token}', [\App\Http\Controllers\Admin\Auth\AdminResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/password/reset',        [\App\Http\Controllers\Admin\Auth\AdminResetPasswordController::class, 'reset'])->name('password.update');
    });

    Route::post('/logout', [\App\Http\Controllers\Admin\Auth\AdminLoginController::class, 'logout'])->name('logout');

    // ── Protected Admin Routes  [EnsureAdminGuard] ────────────────────────────
    Route::middleware('admin')->group(function () {

        // ── Dashboard ─────────────────────────────────────────────────────────
        Route::get('/',          [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
        Route::get('/dashboard', [\App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

        // ── Admin Profile Management ──────────────────────────────────────────
        Route::get('/users/profile',              [\App\Http\Controllers\UserController::class, 'profile'])->name('users.profile');
        Route::post('/users/profile/update/{id}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.profile.update');

        // ── Customer Account Management ───────────────────────────────────────
        Route::middleware(['permission:users,users'])->group(function () {
            Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users');
        });
        Route::middleware(['permission:users,users.edit'])->group(function () {
            Route::get('/users/edit/{id}', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        });
        Route::middleware(['permission:users,users.create'])->group(function () {
            Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
            Route::post('/users/api/store', [\App\Http\Controllers\UserController::class, 'storeWebUser'])->name('users.api.store');
        });
        Route::middleware(['permission:users,users.view'])->group(function () {
            Route::get('/users/view/{id}', [\App\Http\Controllers\UserController::class, 'view'])->name('users.view');
        });
        Route::post('/users/store',              [\App\Http\Controllers\UserController::class, 'storeAdminUsers'])->middleware(['permission:users,users.create'])->name('users.store');
        Route::post('/users/update/{id}',        [\App\Http\Controllers\UserController::class, 'updateAdminUsers'])->middleware(['permission:users,users.edit'])->name('users.update');
        Route::post('/users/update-profile/{id}',[\App\Http\Controllers\UserController::class, 'updateUserProfile'])->middleware(['permission:users,users.edit'])->name('users.update-profile');
        Route::post('/users/send-reset/{id}',    [\App\Http\Controllers\UserController::class, 'sendPasswordReset'])->middleware(['permission:users,users.edit'])->name('users.send-reset');
        Route::get('/users/delete/{id}',         [\App\Http\Controllers\UserController::class, 'deleteAdminUsers'])->middleware(['permission:users,users.delete'])->name('users.delete');

        // ── Admin Sub-users ───────────────────────────────────────────────────
        Route::middleware(['permission:admins,admin.users'])->group(function () {
            Route::get('admin-users', [\App\Http\Controllers\UserController::class, 'adminUsers'])->name('admin.users');
        });
        Route::middleware(['permission:admins,admin.users.create'])->group(function () {
            Route::get('admin-users/create', [\App\Http\Controllers\UserController::class, 'createAdminUsers'])->name('admin.users.create');
        });
        Route::middleware(['permission:admins,admin.users.store'])->group(function () {
            Route::post('admin-users/store', [\App\Http\Controllers\UserController::class, 'storeAdminUsers'])->name('admin.users.store');
        });
        Route::middleware(['permission:admins,admin.users.delete'])->group(function () {
            Route::get('admin-users/delete/{id}', [\App\Http\Controllers\UserController::class, 'deleteAdminUsers'])->name('admin.users.delete');
        });
        Route::middleware(['permission:admins,admin.users.edit'])->group(function () {
            Route::get('admin-users/edit/{id}', [\App\Http\Controllers\UserController::class, 'editAdminUsers'])->name('admin.users.edit');
        });
        Route::middleware(['permission:admins,admin.users.update'])->group(function () {
            Route::post('admin-users/update/{id}', [\App\Http\Controllers\UserController::class, 'updateAdminUsers'])->name('admin.users.update');
        });

        // ── Vendor Management ─────────────────────────────────────────────────
        Route::middleware(['permission:vendors,vendors'])->group(function () {
            Route::get('/vendors', [\App\Http\Controllers\UserController::class, 'vendors'])->name('vendors');
            Route::get('/vendors/create', [\App\Http\Controllers\UserController::class, 'vendorCreate'])->name('vendors.create');
        });
        Route::prefix('/vendor-applications')->name('vendor-applications.')->middleware(['permission:vendors,vendors'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'index'])->name('index');
            Route::get('/{application}', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'show'])->middleware(['permission:vendors,vendors.view'])->name('show');
            Route::post('/{application}/under-review', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'underReview'])->middleware(['permission:vendors,vendors.edit'])->name('under-review');
            Route::post('/{application}/approve', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'approve'])->middleware(['permission:vendors,vendors.edit'])->name('approve');
            Route::post('/{application}/reject', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'reject'])->middleware(['permission:vendors,vendors.edit'])->name('reject');
            Route::post('/{application}/suspend', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'suspend'])->middleware(['permission:vendors,vendors.edit'])->name('suspend');
        });
        Route::middleware(['permission:vendors,vendors.view'])->group(function () {
            Route::get('/vendors/view/{id}', [\App\Http\Controllers\UserController::class, 'vendorView'])->name('vendors.view');
        });
        Route::middleware(['permission:vendors,vendors.edit'])->group(function () {
            Route::get('/vendors/edit/{id}', [\App\Http\Controllers\UserController::class, 'vendorEdit'])->name('vendors.edit');
        });
        Route::post('/vendors/store', [\App\Http\Controllers\UserController::class, 'vendorStore'])->middleware(['permission:vendors,vendors.edit'])->name('vendors.store');
        Route::post('/vendors/update/{id}', [\App\Http\Controllers\UserController::class, 'vendorUpdate'])->middleware(['permission:vendors,vendors.edit'])->name('vendors.update');
        Route::post('/vendors/delete/{id}', [\App\Http\Controllers\UserController::class, 'vendorDelete'])->middleware(['permission:vendors,vendors.edit'])->name('vendors.delete');
        Route::middleware(['permission:vendors,vendors.toggle'])->group(function () {
            Route::post('/vendors/{id}/toggle', [\App\Http\Controllers\UserController::class, 'vendorToggle'])->name('vendors.toggle');
        });

        // ── Products ──────────────────────────────────────────────────────────
        Route::prefix('/products')->name('products.')->group(function () {
            Route::get('/',          [\App\Http\Controllers\Admin\AdminProductController::class, 'index'])->middleware(['permission:products,products.view'])->name('index');
            Route::get('/create',    [\App\Http\Controllers\Admin\AdminProductController::class, 'create'])->middleware(['permission:products,products.create'])->name('create');
            Route::post('/',         [\App\Http\Controllers\Admin\AdminProductController::class, 'store'])->middleware(['permission:products,products.create'])->name('store');
            Route::get('/{id}',      [\App\Http\Controllers\Admin\AdminProductController::class, 'show'])->middleware(['permission:products,products.view'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AdminProductController::class, 'edit'])->middleware(['permission:products,products.edit'])->name('edit');
            Route::put('/{id}',      [\App\Http\Controllers\Admin\AdminProductController::class, 'update'])->middleware(['permission:products,products.edit'])->name('update');
            Route::delete('/{id}',   [\App\Http\Controllers\Admin\AdminProductController::class, 'destroy'])->middleware(['permission:products,products.delete'])->name('destroy');
            Route::post('/{id}/toggle-featured', [\App\Http\Controllers\Admin\AdminProductController::class, 'toggleFeatured'])->middleware(['permission:products,products.toggle'])->name('toggle-featured');
            Route::post('/{id}/toggle-active', [\App\Http\Controllers\Admin\AdminProductController::class, 'toggleActive'])->middleware(['permission:products,products.toggle'])->name('toggle-active');
            Route::post('/{id}/toggle-returnable', [\App\Http\Controllers\Admin\AdminProductController::class, 'toggleReturnable'])->middleware(['permission:products,products.toggle'])->name('toggle-returnable');
            Route::post('/{id}/toggle-refundable', [\App\Http\Controllers\Admin\AdminProductController::class, 'toggleRefundable'])->middleware(['permission:products,products.toggle'])->name('toggle-refundable');
        });

        // ── Categories ────────────────────────────────────────────────────────
        Route::middleware(['permission:category,categories'])->group(function () {
            Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories');
        });
        Route::middleware(['permission:category,categories.edit'])->group(function () {
            Route::get('/categories/edit/{id}', [\App\Http\Controllers\CategoryController::class, 'edit'])->name('categories.edit');
        });
        Route::middleware(['permission:category,categories.create'])->group(function () {
            Route::get('/categories/create', [\App\Http\Controllers\CategoryController::class, 'create'])->name('categories.create');
        });
        // Category CRUD (no-Firebase)
        Route::post('/categories/store',         [\App\Http\Controllers\CategoryController::class, 'store'])->middleware(['permission:category,categories.create'])->name('categories.store');
        Route::post('/categories/update/{id}',   [\App\Http\Controllers\CategoryController::class, 'update'])->middleware(['permission:category,categories.edit'])->name('categories.update');
        Route::post('/categories/toggle/{id}',   [\App\Http\Controllers\CategoryController::class, 'togglePublish'])->middleware(['permission:category,categories.edit'])->name('categories.toggle');
        Route::delete('/categories/delete/{id}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->middleware(['permission:category,categories.edit'])->name('categories.destroy');

        // ── Attributes ────────────────────────────────────────────────────────
        Route::middleware(['permission:item-attribute,attributes'])->group(function () {
            Route::get('/attributes', [\App\Http\Controllers\AttributeController::class, 'index'])->name('attributes');
        });
        Route::middleware(['permission:item-attribute,attributes.edit'])->group(function () {
            Route::get('/attributes/edit/{id}', [\App\Http\Controllers\AttributeController::class, 'edit'])->name('attributes.edit');
        });
        Route::middleware(['permission:item-attribute,attributes.create'])->group(function () {
            Route::get('/attributes/create', [\App\Http\Controllers\AttributeController::class, 'create'])->name('attributes.create');
        });
        // Attribute CRUD (no-Firebase)
        Route::post('/attributes/store',         [\App\Http\Controllers\AttributeController::class, 'store'])->middleware(['permission:item-attribute,attributes.create'])->name('attributes.store');
        Route::post('/attributes/update/{id}',   [\App\Http\Controllers\AttributeController::class, 'update'])->middleware(['permission:item-attribute,attributes.edit'])->name('attributes.update');
        Route::delete('/attributes/delete/{id}', [\App\Http\Controllers\AttributeController::class, 'destroy'])->middleware(['permission:item-attribute,attributes.edit'])->name('attributes.destroy');

        // ── Coupons / Discounts ───────────────────────────────────────────────
        Route::middleware(['permission:coupons,coupons'])->group(function () {
            Route::get('/coupons', [\App\Http\Controllers\CouponController::class, 'index'])->name('coupons');
        });
        Route::middleware(['permission:coupons,coupons.edit'])->group(function () {
            Route::get('/coupons/edit/{id}', [\App\Http\Controllers\CouponController::class, 'edit'])->name('coupons.edit');
        });
        Route::middleware(['permission:coupons,coupons.create'])->group(function () {
            Route::get('/coupons/create',      [\App\Http\Controllers\CouponController::class, 'create'])->name('coupons.create');
            Route::get('/coupons/create/{id}', [\App\Http\Controllers\CouponController::class, 'create'])->name('coupons.create.vendor');
        });
        // Coupon CRUD (no-Firebase)
        Route::post('/coupons/store',         [\App\Http\Controllers\CouponController::class, 'store'])->middleware(['permission:coupons,coupons.create'])->name('coupons.store');
        Route::post('/coupons/update/{id}',   [\App\Http\Controllers\CouponController::class, 'update'])->middleware(['permission:coupons,coupons.edit'])->name('coupons.update');
        Route::post('/coupons/toggle/{id}',   [\App\Http\Controllers\CouponController::class, 'toggle'])->middleware(['permission:coupons,coupons.edit'])->name('coupons.toggle');
        Route::delete('/coupons/delete/{id}', [\App\Http\Controllers\CouponController::class, 'destroy'])->middleware(['permission:coupons,coupons.edit'])->name('coupons.destroy');

        // ── Orders ────────────────────────────────────────────────────────────
        Route::prefix('/orders')->name('orders.')->middleware(['permission:orders,orders.view'])->group(function () {
            Route::get('/',             [\App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('index');
            Route::get('/{id}',         [\App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('show');
            Route::post('/{id}/status', [\App\Http\Controllers\Admin\AdminOrderController::class, 'updateStatus'])->middleware(['permission:orders,orders.status'])->name('status');
        });

        // ── Appointments ──────────────────────────────────────────────────────
        Route::prefix('/appointments')->name('appointments.')->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'index'])->middleware(['permission:appointments,appointments.view'])->name('index');
            Route::get('/create',          [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'create'])->middleware(['permission:appointments,appointments.create'])->name('create');
            Route::post('/',               [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'store'])->middleware(['permission:appointments,appointments.create'])->name('store');
            Route::get('/{id}',            [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'show'])->middleware(['permission:appointments,appointments.view'])->name('show');
            Route::get('/{id}/edit',       [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'edit'])->middleware(['permission:appointments,appointments.edit'])->name('edit');
            Route::put('/{id}',            [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'update'])->middleware(['permission:appointments,appointments.edit'])->name('update');
            Route::delete('/{id}',         [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'destroy'])->middleware(['permission:appointments,appointments.edit'])->name('destroy');
            Route::post('/{id}/status',    [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'updateStatus'])->middleware(['permission:appointments,appointments.edit'])->name('status');
            Route::post('/{id}/confirm',   [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'confirm'])->middleware(['permission:appointments,appointments.assign'])->name('confirm');
            Route::post('/{id}/cancel',    [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'cancel'])->middleware(['permission:appointments,appointments.cancel'])->name('cancel');
            Route::post('/{id}/complete',  [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'complete'])->middleware(['permission:appointments,appointments.complete'])->name('complete');
            Route::post('/{id}/refund',    [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'refund'])->middleware(['permission:appointments,appointments.refund'])->name('refund');
            Route::post('/{id}/reassign',  [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'reassign'])->middleware(['permission:appointments,appointments.reassign'])->name('reassign');
        });

        // ── Expert Management ─────────────────────────────────────────────────
        Route::prefix('/experts')->name('experts.')->group(function () {
            Route::get('/',                  [\App\Http\Controllers\Admin\AdminExpertController::class, 'index'])->middleware(['permission:experts,experts.view'])->name('index');
            Route::get('/create',            [\App\Http\Controllers\Admin\AdminExpertController::class, 'create'])->middleware(['permission:experts,experts.create'])->name('create');
            Route::post('/',                 [\App\Http\Controllers\Admin\AdminExpertController::class, 'store'])->middleware(['permission:experts,experts.create'])->name('store');
            Route::get('/export',            [\App\Http\Controllers\Admin\AdminExpertController::class, 'export'])->middleware(['permission:experts,experts.view'])->name('export');

            // ── Applications sub-group ────────────────────────────────────
            Route::prefix('/applications')->name('applications.')->group(function () {
                Route::get('/',            [\App\Http\Controllers\Admin\AdminExpertController::class, 'applications'])->middleware(['permission:experts,experts.view'])->name('index');
                Route::post('/{id}/under-review', [\App\Http\Controllers\Admin\AdminExpertController::class, 'applicationUnderReview'])->middleware(['permission:experts,experts.moderate'])->name('under-review');
                Route::post('/{id}/approve',      [\App\Http\Controllers\Admin\AdminExpertController::class, 'applicationApprove'])->middleware(['permission:experts,experts.moderate'])->name('approve');
                Route::post('/{id}/reject',       [\App\Http\Controllers\Admin\AdminExpertController::class, 'applicationReject'])->middleware(['permission:experts,experts.moderate'])->name('reject');
            });

            // ── Individual expert actions ─────────────────────────────────
            Route::get('/{id}',              [\App\Http\Controllers\Admin\AdminExpertController::class, 'show'])->middleware(['permission:experts,experts.view'])->name('show');
            Route::get('/{id}/edit',         [\App\Http\Controllers\Admin\AdminExpertController::class, 'edit'])->middleware(['permission:experts,experts.edit'])->name('edit');
            Route::put('/{id}',              [\App\Http\Controllers\Admin\AdminExpertController::class, 'update'])->middleware(['permission:experts,experts.edit'])->name('update');
            Route::delete('/{id}',           [\App\Http\Controllers\Admin\AdminExpertController::class, 'destroy'])->middleware(['permission:experts,experts.edit'])->name('destroy');
            Route::get('/{id}/logs',         [\App\Http\Controllers\Admin\AdminExpertController::class, 'logs'])->middleware(['permission:experts,experts.view'])->name('logs');
            Route::post('/{id}/under-review',[\App\Http\Controllers\Admin\AdminExpertController::class, 'markUnderReview'])->middleware(['permission:experts,experts.moderate'])->name('under-review');
            Route::post('/{id}/approve',     [\App\Http\Controllers\Admin\AdminExpertController::class, 'approve'])->middleware(['permission:experts,experts.moderate'])->name('approve');
            Route::post('/{id}/reject',      [\App\Http\Controllers\Admin\AdminExpertController::class, 'reject'])->middleware(['permission:experts,experts.moderate'])->name('reject');
            Route::post('/{id}/suspend',     [\App\Http\Controllers\Admin\AdminExpertController::class, 'suspend'])->middleware(['permission:experts,experts.moderate'])->name('suspend');
            Route::post('/{id}/restore',     [\App\Http\Controllers\Admin\AdminExpertController::class, 'restore'])->middleware(['permission:experts,experts.moderate'])->name('restore');
            Route::post('/{id}/deactivate',  [\App\Http\Controllers\Admin\AdminExpertController::class, 'deactivate'])->middleware(['permission:experts,experts.moderate'])->name('deactivate');
            Route::post('/{id}/toggle',      [\App\Http\Controllers\Admin\AdminExpertController::class, 'toggleAvailability'])->middleware(['permission:experts,experts.edit'])->name('toggle');
        });

        // ── Returns & Refunds ─────────────────────────────────────────────────
        Route::prefix('/returns')->name('returns.')->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\AdminReturnController::class, 'index'])->middleware(['permission:returns,returns.view'])->name('index');
            Route::get('/reasons',         [\App\Http\Controllers\Admin\AdminReturnController::class, 'reasons'])->middleware(['permission:returns,returns.view'])->name('reasons');
            Route::post('/reasons',        [\App\Http\Controllers\Admin\AdminReturnController::class, 'storeReason'])->middleware(['permission:returns,returns.reject'])->name('reasons.store');
            Route::delete('/reasons/{id}', [\App\Http\Controllers\Admin\AdminReturnController::class, 'destroyReason'])->middleware(['permission:returns,returns.reject'])->whereNumber('id')->name('reasons.destroy');
            Route::get('/{id}',            [\App\Http\Controllers\Admin\AdminReturnController::class, 'show'])->middleware(['permission:returns,returns.view'])->whereNumber('id')->name('show');
            Route::post('/{id}/approve',   [\App\Http\Controllers\Admin\AdminReturnController::class, 'approve'])->middleware(['permission:returns,returns.approve'])->whereNumber('id')->name('approve');
            Route::post('/{id}/reject',    [\App\Http\Controllers\Admin\AdminReturnController::class, 'reject'])->middleware(['permission:returns,returns.reject'])->whereNumber('id')->name('reject');
            Route::post('/{id}/refund',    [\App\Http\Controllers\Admin\AdminReturnController::class, 'processRefund'])->middleware(['permission:returns,returns.refund'])->whereNumber('id')->name('refund');
        });

        // ── Product Reviews & Ratings ─────────────────────────────────────────
        Route::middleware(['permission:reviews,reviews'])->group(function () {
            Route::get('/reviews', [\App\Http\Controllers\Admin\AdminProductController::class, 'reviews'])->name('reviews');
        });
        Route::middleware(['permission:reviews,reviews.delete'])->group(function () {
            Route::delete('/reviews/{id}', [\App\Http\Controllers\Admin\AdminProductController::class, 'destroyReview'])->name('reviews.destroy');
        });

        // ── Stock Tracking ────────────────────────────────────────────────────
        Route::prefix('/stock')->name('stock.')->middleware(['permission:stock,stock.view'])->group(function () {
            Route::get('/',             [\App\Http\Controllers\Admin\AdminStockController::class, 'index'])->name('index');
            Route::get('/{id}/edit',    [\App\Http\Controllers\Admin\AdminStockController::class, 'edit'])->name('edit');
            Route::get('/{id}',         [\App\Http\Controllers\Admin\AdminStockController::class, 'show'])->name('show');
            Route::patch('/{id}/toggle',[\App\Http\Controllers\Admin\AdminStockController::class, 'toggle'])->name('toggle');
            Route::put('/{id}',         [\App\Http\Controllers\Admin\AdminStockController::class, 'update'])->name('update');
            Route::delete('/{id}',      [\App\Http\Controllers\Admin\AdminStockController::class, 'destroy'])->name('destroy');
        });

        // ── Roles & Permissions (RBAC) ────────────────────────────────────────
        Route::middleware(['permission:roles,role.index'])->group(function () {
            Route::get('/role', [\App\Http\Controllers\Admin\RbacController::class, 'index'])->name('role.index');
        });
        Route::middleware(['permission:roles,role.save'])->group(function () {
            Route::get('/role/save', [\App\Http\Controllers\Admin\RbacController::class, 'save'])->name('role.save');
        });
        Route::middleware(['permission:roles,role.store'])->group(function () {
            Route::post('/role/store', [\App\Http\Controllers\Admin\RbacController::class, 'store'])->name('role.store');
        });
        Route::middleware(['permission:roles,role.delete'])->group(function () {
            Route::get('/role/delete/{id}', [\App\Http\Controllers\Admin\RbacController::class, 'delete'])->name('role.delete');
        });
        Route::middleware(['permission:roles,role.edit'])->group(function () {
            Route::get('/role/edit/{id}', [\App\Http\Controllers\Admin\RbacController::class, 'edit'])->name('role.edit');
        });
        Route::middleware(['permission:roles,role.update'])->group(function () {
            Route::put('/role/update/{id}', [\App\Http\Controllers\Admin\RbacController::class, 'update'])->name('role.update');
        });
        Route::prefix('/permissions')->name('permissions.')->middleware(['permission:roles,permissions.manage'])->group(function () {
            Route::get('/',               [\App\Http\Controllers\Admin\RbacController::class, 'permissions'])->name('index');
            Route::post('/',              [\App\Http\Controllers\Admin\RbacController::class, 'storePermission'])->name('store');
            Route::put('/{id}',           [\App\Http\Controllers\Admin\RbacController::class, 'updatePermission'])->name('update');
            Route::delete('/{id}',        [\App\Http\Controllers\Admin\RbacController::class, 'destroyPermission'])->name('destroy');
            Route::post('/sync/{roleId}', [\App\Http\Controllers\Admin\RbacController::class, 'syncRolePermissions'])->name('sync');
        });

        // ── Settings (globals + notifications + payments only) ────────────────
        Route::prefix('/settings')->group(function () {
            Route::middleware(['permission:global-setting,settings.app.globals'])->group(function () {
                Route::get('/app/globals', [\App\Http\Controllers\SettingsController::class, 'globals'])->name('settings.app.globals');
            });
            Route::get('/app/notifications',  [\App\Http\Controllers\SettingsController::class, 'notifications'])->middleware(['permission:general-notifications,notification'])->name('settings.app.notifications');
            Route::post('/app/notifications', [\App\Http\Controllers\SettingsController::class, 'notificationsSave'])->middleware(['permission:general-notifications,notification.send'])->name('settings.app.notifications.save');

            // Stripe + COD only
            Route::middleware(['permission:payment-method,payment-method'])->group(function () {
                Route::get('/payment/stripe',  [\App\Http\Controllers\SettingsController::class, 'stripe'])->name('payment.stripe');
                Route::post('/payment/stripe', [\App\Http\Controllers\SettingsController::class, 'stripeSave'])->name('payment.stripe.save');
                Route::get('/payment/cod',     [\App\Http\Controllers\SettingsController::class, 'cod'])->name('payment.cod');
                Route::post('/payment/cod',    [\App\Http\Controllers\SettingsController::class, 'codSave'])->name('payment.cod.save');
            });
        });

        // ── Email Templates & SMTP Notifications ──────────────────────────────
        Route::middleware(['permission:email-template,email-templates.index'])->group(function () {
            Route::get('/email-templates', [\App\Http\Controllers\SettingsController::class, 'emailTemplatesIndex'])->name('email-templates.index');
        });
        Route::middleware(['permission:email-template,email-templates.edit'])->group(function () {
            Route::get('/email-templates/save/{id?}', [\App\Http\Controllers\SettingsController::class, 'emailTemplatesSave'])->name('email-templates.save');
        });
        Route::middleware(['permission:email-template,email-templates.delete'])->group(function () {
            Route::get('/email-templates/delete/{id}', [\App\Http\Controllers\SettingsController::class, 'emailTemplatesDelete'])->name('email-templates.delete');
        });
        Route::post('/send-email', [\App\Http\Controllers\SendEmailController::class, 'sendMail'])->middleware(['permission:email-template,email-templates.edit'])->name('sendMail');

        // ── Notifications (General) ───────────────────────────────────────────
        Route::middleware(['permission:general-notifications,notification'])->group(function () {
            Route::get('/notification', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notification');
        });
        Route::delete('/notification/delete/{id}', [\App\Http\Controllers\NotificationController::class, 'destroyDynamic'])->middleware(['permission:general-notifications,notification.send'])->name('notification.destroy');
        Route::middleware(['permission:general-notifications,notification.send'])->group(function () {
            Route::get('/notification/send', [\App\Http\Controllers\NotificationController::class, 'send'])->name('notification.send.form');
            Route::get('/notification/users/list', [\App\Http\Controllers\NotificationController::class, 'getUsersList'])->name('notification.users.list');
            Route::post('/notification/broadcast', [\App\Http\Controllers\NotificationController::class, 'broadcastnotification'])->name('notification.broadcast');
            Route::post('/notification/send', [\App\Http\Controllers\NotificationController::class, 'sendNotification'])->name('notification.send');
            Route::get('/email-logs', [\App\Http\Controllers\Admin\EmailLogController::class, 'index'])->name('email-logs.index');
        });

        // ── AI Agriculture Module (Admin Oversight) ───────────────────────────
        Route::prefix('ai-modules')->name('ai.')->middleware(['permission:ai-modules,ai-modules.view'])->group(function () {
            Route::get('/dashboard',                    [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'dashboard'])->name('dashboard');

            Route::get('/crop-recommendations',         [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'cropRecommendations'])->name('crop-recommendations');
            Route::get('/crop-recommendations/{id}',    [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'showCropRecommendation'])->name('crop-recommendations.show');

            Route::get('/crop-plans',                   [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'cropPlans'])->name('crop-plans');

            Route::get('/disease-reports',              [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'diseaseReports'])->name('disease-reports');
            Route::get('/disease-reports/{id}',         [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'showDiseaseReport'])->name('disease-reports.show');
            Route::post('/disease-reports/{id}/assign', [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'assignDisease'])->middleware(['permission:ai-modules,ai-modules.assign'])->name('disease-reports.assign');

            Route::get('/fertilizer',                   [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'fertilizerRecommendations'])->name('fertilizer');

            Route::get('/seasonal-data',                [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'seasonalData'])->name('seasonal-data');
            Route::post('/seasonal-data',               [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'storeSeasonalData'])->middleware(['permission:ai-modules,ai-modules.edit'])->name('seasonal-data.store');
            Route::put('/seasonal-data/{id}',           [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'updateSeasonalData'])->middleware(['permission:ai-modules,ai-modules.edit'])->name('seasonal-data.update');
            Route::delete('/seasonal-data/{id}',        [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'deleteSeasonalData'])->middleware(['permission:ai-modules,ai-modules.edit'])->name('seasonal-data.destroy');
        });

        // ── Forum Moderation ─────────────────────────────────────────────────
        Route::prefix('forum')->name('forum.')->group(function () {
            // Dashboard + list
            Route::get('/',                                  [\App\Http\Controllers\Admin\AdminForumController::class, 'index'])->middleware(['permission:forum,forum.thread.view'])->name('index');
            Route::get('/threads',                           [\App\Http\Controllers\Admin\AdminForumController::class, 'threads'])->middleware(['permission:forum,forum.thread.view'])->name('threads');
            Route::get('/threads/{id}',                      [\App\Http\Controllers\Admin\AdminForumController::class, 'showThread'])->middleware(['permission:forum,forum.thread.view'])->name('threads.show');

            // Thread lifecycle: approve / lock / unlock / resolve / archive / pin / delete / category
            Route::post('/threads/{id}/approve',             [\App\Http\Controllers\Admin\AdminForumController::class, 'approveThread'])->middleware(['permission:forum,forum.thread.create'])->name('threads.approve');
            Route::post('/threads/{id}/lock',                [\App\Http\Controllers\Admin\AdminForumController::class, 'lockThread'])->middleware(['permission:forum,forum.thread.lock'])->name('threads.lock');
            Route::post('/threads/{id}/unlock',              [\App\Http\Controllers\Admin\AdminForumController::class, 'unlockThread'])->middleware(['permission:forum,forum.thread.unlock'])->name('threads.unlock');
            Route::post('/threads/{id}/resolve',             [\App\Http\Controllers\Admin\AdminForumController::class, 'resolveThread'])->middleware(['permission:forum,forum.thread.resolve'])->name('threads.resolve');
            Route::post('/threads/{id}/archive',             [\App\Http\Controllers\Admin\AdminForumController::class, 'archiveThread'])->middleware(['permission:forum,forum.thread.archive'])->name('threads.archive');
            Route::post('/threads/{id}/unarchive',           [\App\Http\Controllers\Admin\AdminForumController::class, 'unarchiveThread'])->middleware(['permission:forum,forum.thread.unarchive'])->name('threads.unarchive');
            Route::post('/threads/{id}/pin',                 [\App\Http\Controllers\Admin\AdminForumController::class, 'pinThread'])->middleware(['permission:forum,forum.thread.pin'])->name('threads.pin');
            Route::delete('/threads/{id}',                   [\App\Http\Controllers\Admin\AdminForumController::class, 'destroyThread'])->middleware(['permission:forum,forum.thread.delete'])->name('threads.destroy');
            Route::post('/threads/{id}/category',            [\App\Http\Controllers\Admin\AdminForumController::class, 'changeCategory'])->middleware(['permission:forum,forum.thread.create'])->name('threads.category');

            // Reply management
            Route::delete('/replies/{id}',                   [\App\Http\Controllers\Admin\AdminForumController::class, 'destroyReply'])->middleware(['permission:forum,forum.reply.delete'])->name('replies.destroy');
            Route::delete('/replies/{id}/official',          [\App\Http\Controllers\Admin\AdminForumController::class, 'removeOfficialAnswer'])->middleware(['permission:forum,forum.reply.delete'])->name('replies.official.remove');

            // Flag review
            Route::get('/flags',                             [\App\Http\Controllers\Admin\AdminForumController::class, 'flags'])->middleware(['permission:forum,forum.flag.review'])->name('flags.index');
            Route::post('/flags/{id}/dismiss',               [\App\Http\Controllers\Admin\AdminForumController::class, 'dismissFlag'])->middleware(['permission:forum,forum.flag.dismiss'])->name('flags.dismiss');
            Route::post('/flags/{id}/confirm',               [\App\Http\Controllers\Admin\AdminForumController::class, 'confirmFlag'])->middleware(['permission:forum,forum.flag.review'])->name('flags.confirm');
            Route::post('/flags/{id}/delete-reply',          [\App\Http\Controllers\Admin\AdminForumController::class, 'deleteFlaggedReply'])->middleware(['permission:forum,forum.flag.delete-reply'])->name('flags.delete-reply');

            // User banning
            Route::post('/users/{userId}/ban',               [\App\Http\Controllers\Admin\AdminForumController::class, 'banUser'])->middleware(['permission:forum,forum.flag.review'])->name('users.ban');
            Route::post('/users/{userId}/unban',             [\App\Http\Controllers\Admin\AdminForumController::class, 'unbanUser'])->middleware(['permission:forum,forum.flag.review'])->name('users.unban');

            // Categories
            Route::get('/categories',                        [\App\Http\Controllers\Admin\AdminForumController::class, 'categories'])->middleware(['permission:forum,forum.thread.view'])->name('categories.index');
            Route::get('/categories/{id}/edit',              [\App\Http\Controllers\Admin\AdminForumController::class, 'editCategory'])->middleware(['permission:forum,forum.thread.create'])->name('categories.edit');
            Route::post('/categories',                       [\App\Http\Controllers\Admin\AdminForumController::class, 'storeCategory'])->middleware(['permission:forum,forum.thread.create'])->name('categories.store');
            Route::put('/categories/{id}',                   [\App\Http\Controllers\Admin\AdminForumController::class, 'updateCategory'])->middleware(['permission:forum,forum.thread.create'])->name('categories.update');
            Route::delete('/categories/{id}',                [\App\Http\Controllers\Admin\AdminForumController::class, 'destroyCategory'])->middleware(['permission:forum,forum.thread.delete'])->name('categories.destroy');

            // Audit log
            Route::get('/audit-log',                         [\App\Http\Controllers\Admin\AdminForumController::class, 'auditLog'])->middleware(['permission:forum,forum.flag.review'])->name('audit-log');
        });

        // ── Notification Broadcast (Section 5 – Admin Broadcast) ─────────────
        Route::prefix('notifications')->name('notifications.broadcast.')->middleware(['permission:general-notifications,notification.send'])->group(function () {
            Route::get('/broadcast',                     [\App\Http\Controllers\Admin\AdminNotificationBroadcastController::class, 'index'])->name('index');
            Route::post('/broadcast',                    [\App\Http\Controllers\Admin\AdminNotificationBroadcastController::class, 'send'])->name('send');
            Route::get('/broadcast/history',             [\App\Http\Controllers\Admin\AdminNotificationBroadcastController::class, 'history'])->name('history');
        });

        // ── Reporting & Analytics ─────────────────────────────────────────────
        Route::prefix('reports')->name('reports.')->middleware(['permission:reports,reports.view'])->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\AdminReportController::class, 'index'])->name('index');
            Route::get('/sales',           [\App\Http\Controllers\Admin\AdminReportController::class, 'sales'])->name('sales');
            Route::get('/top-products',    [\App\Http\Controllers\Admin\AdminReportController::class, 'topProducts'])->name('top-products');
            Route::get('/top-vendors',     [\App\Http\Controllers\Admin\AdminReportController::class, 'topVendors'])->name('top-vendors');
            Route::get('/order-statuses',  [\App\Http\Controllers\Admin\AdminReportController::class, 'orderStatuses'])->name('order-statuses');
            Route::get('/refunds',         [\App\Http\Controllers\Admin\AdminReportController::class, 'refunds'])->name('refunds');
            Route::get('/monthly-growth',  [\App\Http\Controllers\Admin\AdminReportController::class, 'monthlyGrowth'])->name('monthly-growth');
            Route::get('/export',          [\App\Http\Controllers\Admin\AdminReportController::class, 'export'])->name('export');
        });

    }); // end 'admin' middleware group
}); // end /admin prefix
