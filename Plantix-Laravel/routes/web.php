<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Static Blade view routes — no controllers, no models, no auth scaffolding.
| All pages are served directly via Route::view().
*/

// ── Home ─────────────────────────────────────────────────────────────────────
Route::view('/', 'pages.index')->name('home');

// ── Static Information Pages ──────────────────────────────────────────────────
Route::view('/about-us', 'pages.about-us')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');

// ── Plantix-AI Features ───────────────────────────────────────────────────────
Route::view('/plantix-ai', 'pages.plantix-ai')->name('plantix-ai');
Route::view('/crop-recommendation', 'pages.crop-recommendation')->name('crop-recommendation');
Route::view('/crop-planning', 'pages.crop-planning')->name('crop-planning');
Route::view('/disease-identification', 'pages.disease-identification')->name('disease-identification');
Route::view('/fertilizer-recommendation', 'pages.fertilizer-recommendation')->name('fertilizer-recommendation');

// ── Shop ──────────────────────────────────────────────────────────────────────
Route::view('/shop', 'pages.shop')->name('shop');
Route::view('/shop/single', 'pages.shop-single')->name('shop.single');
Route::view('/cart', 'pages.cart')->name('cart');
Route::view('/checkout', 'pages.checkout')->name('checkout');
Route::view('/stripe-sim', 'pages.stripe-sim')->name('stripe-sim');

// ── Orders ────────────────────────────────────────────────────────────────────
Route::view('/orders', 'pages.orders')->name('orders');
Route::view('/order/details', 'pages.order-details')->name('order.details');
Route::view('/order/success', 'pages.order-success')->name('order.success');

// ── Blog / Forum ──────────────────────────────────────────────────────────────
Route::view('/blog', 'pages.blog-with-sidebar')->name('blog');
Route::view('/blog/single', 'pages.blog-single-with-sidebar')->name('blog.single');
Route::view('/forum', 'pages.forum')->name('forum');
Route::view('/forum/new', 'pages.forum-new')->name('forum.new');
Route::view('/forum/thread', 'pages.forum-thread')->name('forum.thread');

// ── Appointments ──────────────────────────────────────────────────────────────
Route::view('/appointments', 'pages.appointments')->name('appointments');
Route::view('/appointment/book', 'pages.appointment-book')->name('appointment.book');
Route::view('/appointment/details', 'pages.appointment-details')->name('appointment.details');
Route::view('/expert-dashboard', 'pages.expert-dashboard')->name('expert.dashboard');

// ── Account ───────────────────────────────────────────────────────────────────
Route::view('/account/profile', 'pages.account-profile')->name('account.profile');

// ── Auth Pages (static views only, no backend logic) ─────────────────────────
Route::view('/signin', 'pages.signin')->name('signin');
Route::view('/signup', 'pages.signup')->name('signup');
Route::view('/password/forgot', 'pages.password-forgot')->name('password.forgot');
Route::view('/password/reset', 'pages.password-reset')->name('password.reset');

// ── 404 Page ──────────────────────────────────────────────────────────────────
Route::view('/404', 'errors.404')->name('not-found');
