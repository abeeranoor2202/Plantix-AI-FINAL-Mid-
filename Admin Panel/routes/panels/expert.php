<?php

/*
|=============================================================================
| Plantix AI — Expert Panel Routes  (/expert/*)
|=============================================================================
|
| Auth guard : 'expert'   (see config/auth.php)
| Middleware : EnsureExpertGuard  →  alias 'expert.auth'
|
| Experts manage appointments with farmers, respond to forum threads, and
| maintain their professional profiles.  Includes agency experts.
|
*/

use Illuminate\Support\Facades\Route;

Route::prefix('expert')->name('expert.')->group(function () {

    // ── Expert Auth (guest-only) ──────────────────────────────────────────────
    Route::middleware('guest:expert')->group(function () {
        Route::get('/login',  [\App\Http\Controllers\Expert\Auth\ExpertLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Expert\Auth\ExpertLoginController::class, 'login']);

        // Registration
        Route::get('/register',  [\App\Http\Controllers\Expert\Auth\ExpertRegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [\App\Http\Controllers\Expert\Auth\ExpertRegisterController::class, 'register']);

        Route::get('/password/email',         [\App\Http\Controllers\Expert\Auth\ExpertForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/password/email',        [\App\Http\Controllers\Expert\Auth\ExpertForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/password/reset/{token}', [\App\Http\Controllers\Expert\Auth\ExpertResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/password/reset',        [\App\Http\Controllers\Expert\Auth\ExpertResetPasswordController::class, 'reset'])->name('password.update');
    });

    // Pending-review page — accessible to anyone (shown right after sign-up)
    Route::get('/register/pending', [\App\Http\Controllers\Expert\Auth\ExpertRegisterController::class, 'pending'])->name('register.pending');

    Route::post('/logout', [\App\Http\Controllers\Expert\Auth\ExpertLoginController::class, 'logout'])->name('logout');

    // ── Protected Expert Routes  [EnsureExpertGuard] ──────────────────────────
    Route::middleware('expert.auth')->group(function () {

        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Expert\ExpertDashboardController::class, 'index'])->name('dashboard');
        Route::get('/',          [\App\Http\Controllers\Expert\ExpertDashboardController::class, 'index'])->name('home');

        // ── Appointments ──────────────────────────────────────────────────────
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/',                          [\App\Http\Controllers\Expert\ExpertAppointmentController::class, 'index'])->name('index');
            Route::get('/{appointment}',             [\App\Http\Controllers\Expert\ExpertAppointmentController::class, 'show'])->name('show');
            Route::get('/{appointment}/edit',        [\App\Http\Controllers\Expert\ExpertAppointmentController::class, 'edit'])->name('edit');
            Route::put('/{appointment}',             [\App\Http\Controllers\Expert\ExpertAppointmentController::class, 'update'])->name('update');
            Route::delete('/{appointment}',          [\App\Http\Controllers\Expert\ExpertAppointmentController::class, 'destroy'])->name('delete');
            Route::post('/{appointment}/accept',     [\App\Http\Controllers\Expert\ExpertAppointmentController::class, 'accept'])->name('accept');
            Route::post('/{appointment}/reject',     [\App\Http\Controllers\Expert\ExpertAppointmentController::class, 'reject'])->name('reject');
            Route::post('/{appointment}/complete',   [\App\Http\Controllers\Expert\ExpertAppointmentController::class, 'complete'])->name('complete');
            Route::post('/{appointment}/reschedule', [\App\Http\Controllers\Expert\ExpertAppointmentController::class, 'reschedule'])->name('reschedule');
        });

        // ── Forum ─────────────────────────────────────────────────────────────
        Route::prefix('forum')->name('forum.')->group(function () {
            Route::get('/',                            [\App\Http\Controllers\Expert\ExpertForumController::class, 'index'])->name('index');
            Route::get('/{thread}',                    [\App\Http\Controllers\Expert\ExpertForumController::class, 'show'])->name('show');
            Route::post('/{thread}/reply',             [\App\Http\Controllers\Expert\ExpertForumController::class, 'reply'])->name('reply');
            Route::patch('/replies/{reply}/official',  [\App\Http\Controllers\Expert\ExpertForumController::class, 'markOfficial'])->name('replies.official');
        });

        // ── Notifications ─────────────────────────────────────────────────────
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',                        [\App\Http\Controllers\NotificationCenterController::class, 'expertIndex'])->name('index');
            Route::get('/feed',                    [\App\Http\Controllers\NotificationCenterController::class, 'expertFeed'])->name('feed');
            Route::post('/{notification}/read',    [\App\Http\Controllers\NotificationCenterController::class, 'expertMarkRead'])->name('read');
            Route::get('/{notification}/open',     [\App\Http\Controllers\NotificationCenterController::class, 'expertOpen'])->name('open');
            Route::post('/mark-all-read',          [\App\Http\Controllers\NotificationCenterController::class, 'expertMarkAllRead'])->name('read-all');
            Route::post('/bulk-read',              [\App\Http\Controllers\NotificationCenterController::class, 'expertBulkRead'])->name('bulk-read');
            Route::post('/bulk-delete',            [\App\Http\Controllers\NotificationCenterController::class, 'expertBulkDelete'])->name('bulk-delete');
            Route::delete('/clear-all',            [\App\Http\Controllers\NotificationCenterController::class, 'expertClearAll'])->name('clear-all');
            Route::get('/unread-count',            [\App\Http\Controllers\NotificationCenterController::class, 'expertUnreadCount'])->name('unread-count');
        });

        // ── Profile ───────────────────────────────────────────────────────────
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/',     [\App\Http\Controllers\Expert\ExpertProfileController::class, 'show'])->name('show');
            Route::get('/edit', [\App\Http\Controllers\Expert\ExpertProfileController::class, 'edit'])->name('edit');
            Route::put('/',     [\App\Http\Controllers\Expert\ExpertProfileController::class, 'update'])->name('update');
        });

        // ── Stripe Connect & Payouts ─────────────────────────────────────────
        Route::get('/payouts',         [\App\Http\Controllers\Expert\ExpertPayoutController::class, 'index'])->name('payouts.index');
        Route::get('/payouts/connect', [\App\Http\Controllers\Expert\ExpertPayoutController::class, 'connect'])->name('payouts.connect');
        Route::get('/payouts/return',  [\App\Http\Controllers\Expert\ExpertPayoutController::class, 'connectReturn'])->name('payouts.return');

    }); // end expert.auth
}); // end /expert prefix
