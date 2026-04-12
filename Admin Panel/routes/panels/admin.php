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
        Route::post('/users/store',              [\App\Http\Controllers\UserController::class, 'storeAdminUsers'])->name('users.store');
        Route::post('/users/update/{id}',        [\App\Http\Controllers\UserController::class, 'updateAdminUsers'])->name('users.update');
        Route::post('/users/update-profile/{id}',[\App\Http\Controllers\UserController::class, 'updateUserProfile'])->name('users.update-profile');
        Route::post('/users/send-reset/{id}',    [\App\Http\Controllers\UserController::class, 'sendPasswordReset'])->name('users.send-reset');
        Route::get('/users/delete/{id}',         [\App\Http\Controllers\UserController::class, 'deleteAdminUsers'])->name('users.delete');

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
        Route::prefix('/vendor-applications')->name('vendor-applications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'index'])->name('index');
            Route::get('/{application}', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'show'])->name('show');
            Route::post('/{application}/under-review', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'underReview'])->name('under-review');
            Route::post('/{application}/approve', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'approve'])->name('approve');
            Route::post('/{application}/reject', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'reject'])->name('reject');
            Route::post('/{application}/suspend', [\App\Http\Controllers\Admin\VendorApplicationController::class, 'suspend'])->name('suspend');
        });
        Route::middleware(['permission:vendors,vendors.view'])->group(function () {
            Route::get('/vendors/view/{id}', [\App\Http\Controllers\UserController::class, 'vendorView'])->name('vendors.view');
        });
        Route::middleware(['permission:vendors,vendors.edit'])->group(function () {
            Route::get('/vendors/edit/{id}', [\App\Http\Controllers\UserController::class, 'vendorEdit'])->name('vendors.edit');
        });
        Route::post('/vendors/store', [\App\Http\Controllers\UserController::class, 'vendorStore'])->name('vendors.store');
        Route::post('/vendors/update/{id}', [\App\Http\Controllers\UserController::class, 'vendorUpdate'])->name('vendors.update');
        Route::post('/vendors/delete/{id}', [\App\Http\Controllers\UserController::class, 'vendorDelete'])->name('vendors.delete');
        Route::middleware(['permission:vendors,vendors.toggle'])->group(function () {
            Route::post('/vendors/{id}/toggle', [\App\Http\Controllers\UserController::class, 'vendorToggle'])->name('vendors.toggle');
        });

        // ── Products ──────────────────────────────────────────────────────────
        Route::prefix('/products')->name('products.')->group(function () {
            Route::get('/',          [\App\Http\Controllers\Admin\AdminProductController::class, 'index'])->name('index');
            Route::get('/create',    [\App\Http\Controllers\Admin\AdminProductController::class, 'create'])->name('create');
            Route::post('/',         [\App\Http\Controllers\Admin\AdminProductController::class, 'store'])->name('store');
            Route::get('/{id}',      [\App\Http\Controllers\Admin\AdminProductController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AdminProductController::class, 'edit'])->name('edit');
            Route::put('/{id}',      [\App\Http\Controllers\Admin\AdminProductController::class, 'update'])->name('update');
            Route::delete('/{id}',   [\App\Http\Controllers\Admin\AdminProductController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-featured', [\App\Http\Controllers\Admin\AdminProductController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::post('/{id}/toggle-active', [\App\Http\Controllers\Admin\AdminProductController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/{id}/toggle-returnable', [\App\Http\Controllers\Admin\AdminProductController::class, 'toggleReturnable'])->name('toggle-returnable');
            Route::post('/{id}/toggle-refundable', [\App\Http\Controllers\Admin\AdminProductController::class, 'toggleRefundable'])->name('toggle-refundable');
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
        Route::post('/categories/store',         [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
        Route::post('/categories/update/{id}',   [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
        Route::post('/categories/toggle/{id}',   [\App\Http\Controllers\CategoryController::class, 'togglePublish'])->name('categories.toggle');
        Route::delete('/categories/delete/{id}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');

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
        Route::post('/attributes/store',         [\App\Http\Controllers\AttributeController::class, 'store'])->name('attributes.store');
        Route::post('/attributes/update/{id}',   [\App\Http\Controllers\AttributeController::class, 'update'])->name('attributes.update');
        Route::delete('/attributes/delete/{id}', [\App\Http\Controllers\AttributeController::class, 'destroy'])->name('attributes.destroy');

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
        Route::post('/coupons/store',         [\App\Http\Controllers\CouponController::class, 'store'])->name('coupons.store');
        Route::post('/coupons/update/{id}',   [\App\Http\Controllers\CouponController::class, 'update'])->name('coupons.update');
        Route::post('/coupons/toggle/{id}',   [\App\Http\Controllers\CouponController::class, 'toggle'])->name('coupons.toggle');
        Route::delete('/coupons/delete/{id}', [\App\Http\Controllers\CouponController::class, 'destroy'])->name('coupons.destroy');

        // ── Orders ────────────────────────────────────────────────────────────
        Route::prefix('/orders')->name('orders.')->group(function () {
            Route::get('/',             [\App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('index');
            Route::get('/{id}',         [\App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('show');
            Route::post('/{id}/status', [\App\Http\Controllers\Admin\AdminOrderController::class, 'updateStatus'])->name('status');
        });

        // ── Appointments ──────────────────────────────────────────────────────
        Route::prefix('/appointments')->name('appointments.')->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'index'])->name('index');
            Route::get('/{id}',            [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'show'])->name('show');
            Route::get('/{id}/edit',       [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'edit'])->name('edit');
            Route::put('/{id}',            [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'update'])->name('update');
            Route::delete('/{id}',         [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/status',    [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'updateStatus'])->name('status');
            Route::post('/{id}/confirm',   [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'confirm'])->name('confirm');
            Route::post('/{id}/cancel',    [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'cancel'])->name('cancel');
            Route::post('/{id}/complete',  [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'complete'])->name('complete');
            Route::post('/{id}/refund',    [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'refund'])->name('refund');
            Route::post('/{id}/reassign',  [\App\Http\Controllers\Admin\AdminAppointmentController::class, 'reassign'])->name('reassign');
        });

        // ── Expert Management ─────────────────────────────────────────────────
        Route::prefix('/experts')->name('experts.')->group(function () {
            Route::get('/',                  [\App\Http\Controllers\Admin\AdminExpertController::class, 'index'])->name('index');
            Route::get('/create',            [\App\Http\Controllers\Admin\AdminExpertController::class, 'create'])->name('create');
            Route::post('/',                 [\App\Http\Controllers\Admin\AdminExpertController::class, 'store'])->name('store');
            Route::get('/export',            [\App\Http\Controllers\Admin\AdminExpertController::class, 'export'])->name('export');

            // ── Applications sub-group ────────────────────────────────────
            Route::prefix('/applications')->name('applications.')->group(function () {
                Route::get('/',            [\App\Http\Controllers\Admin\AdminExpertController::class, 'applications'])->name('index');
                Route::post('/{id}/under-review', [\App\Http\Controllers\Admin\AdminExpertController::class, 'applicationUnderReview'])->name('under-review');
                Route::post('/{id}/approve',      [\App\Http\Controllers\Admin\AdminExpertController::class, 'applicationApprove'])->name('approve');
                Route::post('/{id}/reject',       [\App\Http\Controllers\Admin\AdminExpertController::class, 'applicationReject'])->name('reject');
            });

            // ── Individual expert actions ─────────────────────────────────
            Route::get('/{id}',              [\App\Http\Controllers\Admin\AdminExpertController::class, 'show'])->name('show');
            Route::get('/{id}/edit',         [\App\Http\Controllers\Admin\AdminExpertController::class, 'edit'])->name('edit');
            Route::put('/{id}',              [\App\Http\Controllers\Admin\AdminExpertController::class, 'update'])->name('update');
            Route::delete('/{id}',           [\App\Http\Controllers\Admin\AdminExpertController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/logs',         [\App\Http\Controllers\Admin\AdminExpertController::class, 'logs'])->name('logs');
            Route::post('/{id}/under-review',[\App\Http\Controllers\Admin\AdminExpertController::class, 'markUnderReview'])->name('under-review');
            Route::post('/{id}/approve',     [\App\Http\Controllers\Admin\AdminExpertController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject',      [\App\Http\Controllers\Admin\AdminExpertController::class, 'reject'])->name('reject');
            Route::post('/{id}/suspend',     [\App\Http\Controllers\Admin\AdminExpertController::class, 'suspend'])->name('suspend');
            Route::post('/{id}/restore',     [\App\Http\Controllers\Admin\AdminExpertController::class, 'restore'])->name('restore');
            Route::post('/{id}/deactivate',  [\App\Http\Controllers\Admin\AdminExpertController::class, 'deactivate'])->name('deactivate');
            Route::post('/{id}/toggle',      [\App\Http\Controllers\Admin\AdminExpertController::class, 'toggleAvailability'])->name('toggle');
        });

        // ── Returns & Refunds ─────────────────────────────────────────────────
        Route::prefix('/returns')->name('returns.')->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\AdminReturnController::class, 'index'])->name('index');
            Route::get('/reasons',         [\App\Http\Controllers\Admin\AdminReturnController::class, 'reasons'])->name('reasons');
            Route::post('/reasons',        [\App\Http\Controllers\Admin\AdminReturnController::class, 'storeReason'])->name('reasons.store');
            Route::delete('/reasons/{id}', [\App\Http\Controllers\Admin\AdminReturnController::class, 'destroyReason'])->whereNumber('id')->name('reasons.destroy');
            Route::get('/{id}',            [\App\Http\Controllers\Admin\AdminReturnController::class, 'show'])->whereNumber('id')->name('show');
            Route::post('/{id}/approve',   [\App\Http\Controllers\Admin\AdminReturnController::class, 'approve'])->whereNumber('id')->name('approve');
            Route::post('/{id}/reject',    [\App\Http\Controllers\Admin\AdminReturnController::class, 'reject'])->whereNumber('id')->name('reject');
            Route::post('/{id}/refund',    [\App\Http\Controllers\Admin\AdminReturnController::class, 'processRefund'])->whereNumber('id')->name('refund');
        });

        // ── Product Reviews & Ratings ─────────────────────────────────────────
        Route::middleware(['permission:reviews,reviews'])->group(function () {
            Route::get('/reviews', [\App\Http\Controllers\Admin\AdminProductController::class, 'reviews'])->name('reviews');
        });
        Route::middleware(['permission:reviews,reviews.delete'])->group(function () {
            Route::delete('/reviews/{id}', [\App\Http\Controllers\Admin\AdminProductController::class, 'destroyReview'])->name('reviews.destroy');
        });

        // ── Stock Tracking ────────────────────────────────────────────────────
        Route::prefix('/stock')->name('stock.')->group(function () {
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
            Route::get('/app/notifications',  [\App\Http\Controllers\SettingsController::class, 'notifications'])->name('settings.app.notifications');
            Route::post('/app/notifications', [\App\Http\Controllers\SettingsController::class, 'notificationsSave'])->name('settings.app.notifications.save');

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
        Route::post('/send-email', [\App\Http\Controllers\SendEmailController::class, 'sendMail'])->name('sendMail');

        // ── Notifications (General) ───────────────────────────────────────────
        Route::middleware(['permission:general-notifications,notification'])->group(function () {
            Route::get('/notification', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notification');
        });
        Route::delete('/notification/delete/{id}', [\App\Http\Controllers\NotificationController::class, 'destroyDynamic'])->name('notification.destroy');
        Route::middleware(['permission:general-notifications,notification.send'])->group(function () {
            Route::get('/notification/send', [\App\Http\Controllers\NotificationController::class, 'send'])->name('notification.send');
            Route::get('/notification/users/list', [\App\Http\Controllers\NotificationController::class, 'getUsersList'])->name('notification.users.list');
            Route::post('/notification/broadcast', [\App\Http\Controllers\NotificationController::class, 'broadcastnotification'])->name('notification.broadcast');
            Route::post('/notification/send', [\App\Http\Controllers\NotificationController::class, 'sendNotification'])->name('notification.send');
        });

        // ── AI Agriculture Module (Admin Oversight) ───────────────────────────
        Route::prefix('ai-modules')->name('ai.')->group(function () {
            Route::get('/dashboard',                    [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'dashboard'])->name('dashboard');

            Route::get('/crop-recommendations',         [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'cropRecommendations'])->name('crop-recommendations');
            Route::get('/crop-recommendations/{id}',    [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'showCropRecommendation'])->name('crop-recommendations.show');

            Route::get('/crop-plans',                   [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'cropPlans'])->name('crop-plans');

            Route::get('/disease-reports',              [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'diseaseReports'])->name('disease-reports');
            Route::get('/disease-reports/{id}',         [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'showDiseaseReport'])->name('disease-reports.show');
            Route::post('/disease-reports/{id}/assign', [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'assignDisease'])->name('disease-reports.assign');

            Route::get('/fertilizer',                   [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'fertilizerRecommendations'])->name('fertilizer');

            Route::get('/seasonal-data',                [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'seasonalData'])->name('seasonal-data');
            Route::post('/seasonal-data',               [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'storeSeasonalData'])->name('seasonal-data.store');
            Route::put('/seasonal-data/{id}',           [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'updateSeasonalData'])->name('seasonal-data.update');
            Route::delete('/seasonal-data/{id}',        [\App\Http\Controllers\Admin\AdminAiModuleController::class, 'deleteSeasonalData'])->name('seasonal-data.destroy');
        });

        // ── Forum Moderation ─────────────────────────────────────────────────
        Route::prefix('forum')->name('forum.')->group(function () {
            // Dashboard + list
            Route::get('/',                                  [\App\Http\Controllers\Admin\AdminForumController::class, 'index'])->name('index');
            Route::get('/threads',                           [\App\Http\Controllers\Admin\AdminForumController::class, 'threads'])->name('threads');
            Route::get('/threads/{id}',                      [\App\Http\Controllers\Admin\AdminForumController::class, 'showThread'])->name('threads.show');

            // Thread lifecycle: approve / lock / unlock / resolve / archive / pin / delete / category
            Route::post('/threads/{id}/approve',             [\App\Http\Controllers\Admin\AdminForumController::class, 'approveThread'])->name('threads.approve');
            Route::post('/threads/{id}/lock',                [\App\Http\Controllers\Admin\AdminForumController::class, 'lockThread'])->name('threads.lock');
            Route::post('/threads/{id}/unlock',              [\App\Http\Controllers\Admin\AdminForumController::class, 'unlockThread'])->name('threads.unlock');
            Route::post('/threads/{id}/resolve',             [\App\Http\Controllers\Admin\AdminForumController::class, 'resolveThread'])->name('threads.resolve');
            Route::post('/threads/{id}/archive',             [\App\Http\Controllers\Admin\AdminForumController::class, 'archiveThread'])->name('threads.archive');
            Route::post('/threads/{id}/pin',                 [\App\Http\Controllers\Admin\AdminForumController::class, 'pinThread'])->name('threads.pin');
            Route::delete('/threads/{id}',                   [\App\Http\Controllers\Admin\AdminForumController::class, 'destroyThread'])->name('threads.destroy');
            Route::post('/threads/{id}/category',            [\App\Http\Controllers\Admin\AdminForumController::class, 'changeCategory'])->name('threads.category');

            // Reply management
            Route::delete('/replies/{id}',                   [\App\Http\Controllers\Admin\AdminForumController::class, 'destroyReply'])->name('replies.destroy');
            Route::delete('/replies/{id}/official',          [\App\Http\Controllers\Admin\AdminForumController::class, 'removeOfficialAnswer'])->name('replies.official.remove');

            // Flag review
            Route::get('/flags',                             [\App\Http\Controllers\Admin\AdminForumController::class, 'flags'])->name('flags.index');
            Route::post('/flags/{id}/dismiss',               [\App\Http\Controllers\Admin\AdminForumController::class, 'dismissFlag'])->name('flags.dismiss');
            Route::post('/flags/{id}/confirm',               [\App\Http\Controllers\Admin\AdminForumController::class, 'confirmFlag'])->name('flags.confirm');

            // User banning
            Route::post('/users/{userId}/ban',               [\App\Http\Controllers\Admin\AdminForumController::class, 'banUser'])->name('users.ban');
            Route::post('/users/{userId}/unban',             [\App\Http\Controllers\Admin\AdminForumController::class, 'unbanUser'])->name('users.unban');

            // Categories
            Route::get('/categories',                        [\App\Http\Controllers\Admin\AdminForumController::class, 'categories'])->name('categories.index');
            Route::post('/categories',                       [\App\Http\Controllers\Admin\AdminForumController::class, 'storeCategory'])->name('categories.store');
            Route::put('/categories/{id}',                   [\App\Http\Controllers\Admin\AdminForumController::class, 'updateCategory'])->name('categories.update');
            Route::delete('/categories/{id}',                [\App\Http\Controllers\Admin\AdminForumController::class, 'destroyCategory'])->name('categories.destroy');

            // Audit log
            Route::get('/audit-log',                         [\App\Http\Controllers\Admin\AdminForumController::class, 'auditLog'])->name('audit-log');
        });

        // ── Notification Broadcast (Section 5 – Admin Broadcast) ─────────────
        Route::prefix('notifications')->name('notifications.broadcast.')->group(function () {
            Route::get('/broadcast',                     [\App\Http\Controllers\Admin\AdminNotificationBroadcastController::class, 'index'])->name('index');
            Route::post('/broadcast',                    [\App\Http\Controllers\Admin\AdminNotificationBroadcastController::class, 'send'])->name('send');
            Route::get('/broadcast/history',             [\App\Http\Controllers\Admin\AdminNotificationBroadcastController::class, 'history'])->name('history');
        });

        // ── Reporting & Analytics ─────────────────────────────────────────────
        Route::prefix('reports')->name('reports.')->group(function () {
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
