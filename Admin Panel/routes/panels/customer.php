<?php

/*
|=============================================================================
| Plantix AI — Customer / User Panel Routes  (root)
|=============================================================================
|
| Auth guard : 'web'   (see config/auth.php)
| Middleware : EnsureCustomerAuth  →  alias 'customer'
|
| Structure:
|   1. Public pages (no auth)
|   2. Customer Auth (register / login / password reset)
|   3. Public shop & forum (read-only without auth)
|   4. Protected customer routes (requires 'customer' middleware)
|      - Cart & checkout
|      - Orders & returns
|      - Appointments with experts
|      - AI agriculture modules
|      - Account / profile
|
*/

use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════════════════════
// 1. PUBLIC PAGES (no authentication required)
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/',          [\App\Http\Controllers\Frontend\HomeController::class, 'index'])->name('home');
Route::get('/about-us',  fn () => view('customer.about-us'))->name('about');
Route::get('/contact',   fn () => view('customer.contact'))->name('contact');

// ══════════════════════════════════════════════════════════════════════════════
// 2. CUSTOMER AUTH
// ══════════════════════════════════════════════════════════════════════════════

Route::middleware('guest:web')->group(function () {
    Route::get('/signin',  [\App\Http\Controllers\Frontend\Auth\CustomerLoginController::class, 'showLoginForm'])->name('signin');
    Route::post('/signin', [\App\Http\Controllers\Frontend\Auth\CustomerLoginController::class, 'login'])->name('login')->middleware('throttle:5,1');

    Route::get('/signup',  [\App\Http\Controllers\Frontend\Auth\CustomerRegisterController::class, 'showRegistrationForm'])->name('signup');
    Route::post('/signup', [\App\Http\Controllers\Frontend\Auth\CustomerRegisterController::class, 'register'])->name('register');

    Route::get('/password/forgot',        [\App\Http\Controllers\Frontend\Auth\CustomerForgotPasswordController::class, 'showLinkRequestForm'])->name('password.forgot');
    Route::post('/password/email',        [\App\Http\Controllers\Frontend\Auth\CustomerForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [\App\Http\Controllers\Frontend\Auth\CustomerResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset',        [\App\Http\Controllers\Frontend\Auth\CustomerResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/signout', [\App\Http\Controllers\Frontend\Auth\CustomerLoginController::class, 'logout'])->name('logout');

// Email verification (requires customer to be logged in, but not yet verified)
Route::middleware('auth:web')->group(function () {
    Route::get('/email/verify',                   fn () => view('customer.email-verification'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}',        [\App\Http\Controllers\Frontend\Auth\CustomerVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [\App\Http\Controllers\Frontend\Auth\CustomerVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
});

// ══════════════════════════════════════════════════════════════════════════════
// 3. PUBLIC SHOP & FORUM (read-only, no auth required)
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/shop',      [\App\Http\Controllers\Frontend\ShopController::class, 'index'])->name('shop');
Route::get('/shop/{id}', [\App\Http\Controllers\Frontend\ShopController::class, 'show'])->name('shop.single');

// ── Stores (Vendors) ──────────────────────────────────────────────────────────
Route::get('/stores',      [\App\Http\Controllers\Frontend\StoreController::class, 'index'])->name('stores');
Route::get('/stores/{id}', [\App\Http\Controllers\Frontend\StoreController::class, 'show'])->name('stores.single');

Route::get('/forum',                  [\App\Http\Controllers\Frontend\ForumController::class, 'index'])->name('forum');
// Literal route MUST be registered before the {slug} wildcard to avoid being swallowed by it
Route::get('/forum/new-thread',       [\App\Http\Controllers\Frontend\ForumController::class, 'create'])->middleware(['customer', 'verified'])->name('forum.new');
Route::get('/forum/{slug}',           [\App\Http\Controllers\Frontend\ForumController::class, 'show'])->name('forum.thread');
Route::redirect('/blog',              '/forum')->name('blog');

// ── Expert browse (public — no auth required to view) ────────────────────────
Route::get('/experts',         [\App\Http\Controllers\Frontend\ExpertBrowseController::class, 'index'])->name('experts.index');
Route::get('/experts/{id}',    [\App\Http\Controllers\Frontend\ExpertBrowseController::class, 'show'])->name('experts.show');

// ── Cart state (public — guests get count=0) ──────────────────────────────────
Route::get('/cart/count', [\App\Http\Controllers\Frontend\CartController::class, 'count'])->name('cart.count');
Route::get('/cart/mini',  [\App\Http\Controllers\Frontend\CartController::class, 'mini'])->name('cart.mini');

// ══════════════════════════════════════════════════════════════════════════════
// 4. AUTHENTICATED CUSTOMER ROUTES  [EnsureCustomerAuth]
// ══════════════════════════════════════════════════════════════════════════════

Route::middleware(['customer', 'verified'])->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Frontend\CustomerDashboardController::class, 'index'])->name('customer.dashboard');

    // ── Forum (write operations) ──────────────────────────────────────────────
    // Rate limits: thread creation (5/min), replies (10/min), flags (3/min)
    Route::post('/forum',                        [\App\Http\Controllers\Frontend\ForumController::class, 'store'])->name('forum.store')
        ->middleware('throttle:5,1');
    Route::post('/forum/{thread}/reply',         [\App\Http\Controllers\Frontend\ForumController::class, 'reply'])->name('forum.reply')
        ->middleware('throttle:10,1');
    Route::post('/forum/{thread}/flag',          [\App\Http\Controllers\Frontend\ForumController::class, 'flagThread'])->name('forum.thread.flag')
        ->middleware('throttle:3,1');
    Route::patch('/forum/replies/{reply}',       [\App\Http\Controllers\Frontend\ForumController::class, 'editReply'])->name('forum.reply.edit');
    Route::delete('/forum/replies/{reply}',      [\App\Http\Controllers\Frontend\ForumController::class, 'destroyReply'])->name('forum.reply.destroy');
    Route::post('/forum/replies/{reply}/flag',   [\App\Http\Controllers\Frontend\ForumController::class, 'flagReply'])->name('forum.reply.flag')
        ->middleware('throttle:3,1');

    // ── Cart ──────────────────────────────────────────────────────────────────
    Route::get('/cart',             [\App\Http\Controllers\Frontend\CartController::class, 'index'])->name('cart');
    Route::post('/cart/add',        [\App\Http\Controllers\Frontend\CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{id}',      [\App\Http\Controllers\Frontend\CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}',     [\App\Http\Controllers\Frontend\CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart',          [\App\Http\Controllers\Frontend\CartController::class, 'clear'])->name('cart.clear');
    Route::get('/cart/coupon',      fn() => redirect()->route('cart'))->name('cart.coupon');
    Route::post('/cart/coupon',     [\App\Http\Controllers\Frontend\CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
    Route::delete('/cart/coupon',   [\App\Http\Controllers\Frontend\CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

    // ── Checkout ──────────────────────────────────────────────────────────────
    Route::get('/checkout',                    [\App\Http\Controllers\Frontend\CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout',                   [\App\Http\Controllers\Frontend\CartController::class, 'placeOrder'])->name('checkout.place');
    Route::post('/checkout/stripe/initiate',   [\App\Http\Controllers\Frontend\StripePaymentController::class, 'initiateCheckout'])->name('checkout.stripe.initiate');
    Route::get('/checkout/stripe/pay/{order}',  [\App\Http\Controllers\Frontend\StripePaymentController::class, 'createCheckoutSession'])->defaults('type', 'order')->name('checkout.stripe.pay');
    Route::post('/checkout/stripe/pay/{order}', [\App\Http\Controllers\Frontend\StripePaymentController::class, 'createCheckoutSession'])->defaults('type', 'order')->name('checkout.stripe.pay.confirm');

    Route::middleware('manual.payment.enabled')->group(function () {
        Route::get('/checkout/pay/{order}',  [\App\Http\Controllers\Frontend\StripePaymentController::class, 'showPaymentPage'])->name('checkout.pay');
        Route::post('/checkout/pay/{order}', [\App\Http\Controllers\Frontend\StripePaymentController::class, 'processOrderPayment'])->name('checkout.pay.confirm');
    });

    // ── Orders ────────────────────────────────────────────────────────────────
    Route::get('/orders',                  [\App\Http\Controllers\Frontend\CustomerOrderController::class, 'index'])->name('orders');
    Route::get('/orders/{id}',             [\App\Http\Controllers\Frontend\CustomerOrderController::class, 'show'])->name('order.details');
    Route::get('/order/success/{id}',      [\App\Http\Controllers\Frontend\CustomerOrderController::class, 'success'])->name('order.success');
    Route::post('/orders/{id}/return',     [\App\Http\Controllers\Frontend\CustomerOrderController::class, 'requestReturn'])->name('order.return');
    Route::post('/orders/{id}/cancel',     [\App\Http\Controllers\Frontend\CustomerOrderController::class, 'cancel'])->name('order.cancel');
    Route::post('/orders/{id}/dispute',    [\App\Http\Controllers\Frontend\CustomerOrderController::class, 'dispute'])->name('order.dispute');
    Route::post('/orders/{id}/dispute/escalate', [\App\Http\Controllers\Frontend\CustomerOrderController::class, 'escalateDispute'])->name('order.dispute.escalate');
    Route::get('/orders/{id}/invoice',     [\App\Http\Controllers\Frontend\InvoiceController::class, 'download'])->name('order.invoice');

    // ── Expert quick-book (auth required to submit booking) ─────────────────
    Route::post('/experts/{id}/book', [\App\Http\Controllers\Frontend\ExpertBrowseController::class, 'quickBook'])->name('experts.quick-book');

    // ── Appointments with Experts ─────────────────────────────────────────────
    Route::get('/appointments',              [\App\Http\Controllers\Frontend\CustomerAppointmentController::class, 'index'])->name('appointments');
    Route::get('/appointment/book',          [\App\Http\Controllers\Frontend\CustomerAppointmentController::class, 'create'])->name('appointment.book');
    Route::post('/appointment/book',         [\App\Http\Controllers\Frontend\CustomerAppointmentController::class, 'store'])->name('appointment.store');
    Route::get('/appointment/{id}',          [\App\Http\Controllers\Frontend\CustomerAppointmentController::class, 'show'])->name('appointment.details');
    Route::post('/appointment/{id}/cancel',  [\App\Http\Controllers\Frontend\CustomerAppointmentController::class, 'cancel'])->name('appointment.cancel');
    Route::get('/appointment/{id}/pay',      [\App\Http\Controllers\Frontend\StripePaymentController::class, 'createCheckoutSession'])->defaults('type', 'appointment')->name('appointment.pay');
    Route::post('/appointment/{id}/pay',     [\App\Http\Controllers\Frontend\StripePaymentController::class, 'createCheckoutSession'])->defaults('type', 'appointment')->name('appointment.pay.process');
    Route::post('/appointment/{id}/review',   [\App\Http\Controllers\Frontend\CustomerAppointmentController::class, 'review'])->name('appointment.review.store');
    // Section 6 – customer accepts or rejects a reschedule proposed by an expert
    Route::post('/appointment/{id}/reschedule-response', [\App\Http\Controllers\Frontend\CustomerAppointmentController::class, 'rescheduleResponse'])->name('appointment.reschedule.response');

    // ── Product Reviews ───────────────────────────────────────────────────────
    Route::post('/products/{id}/reviews', [\App\Http\Controllers\Frontend\ProductReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{id}',        [\App\Http\Controllers\Frontend\ProductReviewController::class, 'destroy'])->name('reviews.destroy');

    // ── Account / Profile ─────────────────────────────────────────────────────
    Route::get('/account/profile',    [\App\Http\Controllers\Frontend\CustomerProfileController::class, 'show'])->name('account.profile');
    Route::put('/account/profile',    [\App\Http\Controllers\Frontend\CustomerProfileController::class, 'update'])->name('account.profile.update');
    Route::post('/account/password',  [\App\Http\Controllers\Frontend\CustomerProfileController::class, 'changePassword'])->name('account.password');

    // ── AI Agriculture: Crop Recommendation ──────────────────────────────────
    Route::get('/crop-recommendation',          [\App\Http\Controllers\Frontend\CropRecommendationController::class, 'index'])->name('crop.recommendation');
    Route::post('/crop-recommendation',         [\App\Http\Controllers\Frontend\CropRecommendationController::class, 'recommend'])->name('crop.recommendation.recommend');
    Route::get('/crop-recommendation/{id}',     [\App\Http\Controllers\Frontend\CropRecommendationController::class, 'show'])->name('crop.recommendation.show');
    Route::get('/crop-recommendation/history',  [\App\Http\Controllers\Frontend\CropRecommendationController::class, 'history'])->name('crop.recommendation.history');

    // ── AI Agriculture: Crop Planning ─────────────────────────────────────────
    Route::get('/crop-planning',               [\App\Http\Controllers\Frontend\CropPlanningController::class, 'index'])->name('crop.planning');
    Route::post('/crop-planning',              [\App\Http\Controllers\Frontend\CropPlanningController::class, 'generate'])->name('crop.planning.generate');
    Route::get('/crop-planning/{id}',          [\App\Http\Controllers\Frontend\CropPlanningController::class, 'show'])->name('crop.planning.show');
    Route::patch('/crop-planning/{id}/status', [\App\Http\Controllers\Frontend\CropPlanningController::class, 'updateStatus'])->name('crop.planning.status');
    Route::delete('/crop-planning/{id}',       [\App\Http\Controllers\Frontend\CropPlanningController::class, 'destroy'])->name('crop.planning.destroy');

    // ── AI Agriculture: Disease Identification ────────────────────────────────
    Route::get('/disease-identification',   [\App\Http\Controllers\Frontend\DiseaseIdentificationController::class, 'index'])->name('disease.identification');
    Route::post('/disease-identification',  [\App\Http\Controllers\Frontend\DiseaseIdentificationController::class, 'detect'])->name('disease.detect')->middleware('throttle:disease-detect');
    Route::get('/disease/{id}',             [\App\Http\Controllers\Frontend\DiseaseIdentificationController::class, 'show'])->name('disease.show');
    Route::get('/disease/{id}/status',      [\App\Http\Controllers\Frontend\DiseaseIdentificationController::class, 'pollStatus'])->name('disease.poll');
    Route::get('/disease-history',          [\App\Http\Controllers\Frontend\DiseaseIdentificationController::class, 'history'])->name('disease.history');

    // ── AI Agriculture: Fertilizer Recommendation ─────────────────────────────
    Route::get('/fertilizer-recommendation',       [\App\Http\Controllers\Frontend\FertilizerRecommendationController::class, 'index'])->name('fertilizer.recommendation');
    Route::post('/fertilizer-recommendation',      [\App\Http\Controllers\Frontend\FertilizerRecommendationController::class, 'recommend'])->name('fertilizer.recommendation.recommend');
    Route::get('/fertilizer-recommendation/{id}',  [\App\Http\Controllers\Frontend\FertilizerRecommendationController::class, 'show'])->name('fertilizer.recommendation.show');
    Route::get('/fertilizer-history',              [\App\Http\Controllers\Frontend\FertilizerRecommendationController::class, 'history'])->name('fertilizer.recommendation.history');

    // ── Weather ───────────────────────────────────────────────────────────────
    Route::get('/weather',              [\App\Http\Controllers\Frontend\WeatherController::class, 'current'])->name('weather');
    Route::post('/weather/location',    [\App\Http\Controllers\Frontend\WeatherController::class, 'saveLocation'])->name('weather.location');
    Route::get('/weather/history',      [\App\Http\Controllers\Frontend\WeatherController::class, 'history'])->name('weather.history');
    Route::get('/weather/cities',       [\App\Http\Controllers\Frontend\WeatherController::class, 'cities'])->name('weather.cities');
    // ── Notifications ──────────────────────────────────────────────────────────
    Route::get('/notifications',               [\App\Http\Controllers\NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/feed',          [\App\Http\Controllers\NotificationCenterController::class, 'feed'])->name('notifications.feed');
    Route::get('/notifications/unread-count',  [\App\Http\Controllers\NotificationCenterController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/read-all',     [\App\Http\Controllers\NotificationCenterController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read',    [\App\Http\Controllers\NotificationCenterController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/clear-all',   [\App\Http\Controllers\NotificationCenterController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications/{notification}/open', [\App\Http\Controllers\NotificationCenterController::class, 'open'])->name('notifications.open');
    // ── AI Chat (Plantix AI Assistant) ────────────────────────────────────────
    Route::get('/plantix-ai',            [\App\Http\Controllers\Frontend\AiChatController::class, 'index'])->name('ai.chat');
    Route::post('/plantix-ai/message',   [\App\Http\Controllers\Frontend\AiChatController::class, 'message'])->name('ai.chat.message')->middleware('throttle:ai-chat');
    Route::get('/plantix-ai/history',    [\App\Http\Controllers\Frontend\AiChatController::class, 'history'])->name('ai.chat.history');
    Route::post('/plantix-ai/new',       [\App\Http\Controllers\Frontend\AiChatController::class, 'newSession'])->name('ai.chat.new');
    Route::get('/plantix-ai/sessions',   [\App\Http\Controllers\Frontend\AiChatController::class, 'sessions'])->name('ai.chat.sessions');
    Route::post('/plantix-ai/escalate',  [\App\Http\Controllers\Frontend\AiChatController::class, 'escalate'])->name('ai.chat.escalate');

    // ── Expert Application ────────────────────────────────────────────────────
    Route::prefix('/expert-application')->name('expert-application.')->group(function () {
        Route::get('/',        [\App\Http\Controllers\Customer\CustomerExpertApplicationController::class, 'create'])->name('create');
        Route::post('/',       [\App\Http\Controllers\Customer\CustomerExpertApplicationController::class, 'store'])->name('store');
        Route::get('/status',  [\App\Http\Controllers\Customer\CustomerExpertApplicationController::class, 'status'])->name('status');
    });

}); // end customer middleware

// ══════════════════════════════════════════════════════════════════════════════
// 5. STRIPE PAYMENT CALLBACKS  (verified by Stripe signature - no CSRF)
// ══════════════════════════════════════════════════════════════════════════════

Route::post('payments/stripe/intent',   [\App\Http\Controllers\Frontend\StripePaymentController::class, 'createIntent'])->middleware('auth:web')->name('stripe.intent');
Route::post('stripe/webhook',           [\App\Http\Controllers\Api\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::get('payment/success', [\App\Http\Controllers\Frontend\StripePaymentController::class, 'success'])->name('payment.success');
Route::get('payment/cancel',  [\App\Http\Controllers\Frontend\StripePaymentController::class, 'cancel'])->name('payment.cancel');
Route::get('payment/failed',  fn () => redirect()->route('payment.cancel'))->name('payment.failed');
