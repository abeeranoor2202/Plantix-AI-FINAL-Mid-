<?php

namespace App\Providers;

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\VendorRepositoryInterface;
use App\Models\Setting;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\VendorRepository;
// ── Admin panel services ───────────────────────────────────────────────────────
use App\Services\Admin\RbacService;
use App\Services\Admin\ZoneService;
// ── Shared (multi-panel) services ─────────────────────────────────────────────
use App\Services\Shared\AppointmentService;
use App\Services\Shared\CartCheckoutService;
use App\Services\Shared\CouponService;
use App\Services\Shared\InventoryService;
use App\Services\Shared\NotificationService;
use App\Services\Shared\OrderService;
use App\Services\Shared\RefundService;
use App\Services\Shared\ReturnService;
use App\Services\Shared\ReturnRefundService;
use App\Services\Shared\StockService;
use App\Services\Shared\WalletService;
// ── Customer panel services ────────────────────────────────────────────────────
use App\Services\Customer\AiChatService;
use App\Services\Customer\CropPlanningService;
use App\Services\Customer\CropRecommendationService;
use App\Services\Customer\DiseaseDetectionService;
use App\Services\Customer\FertilizerRecommendationService;
use App\Services\Customer\WeatherService;
// ── Vendor panel services ──────────────────────────────────────────────────────
use App\Services\Vendor\VendorCouponService;
use App\Services\Vendor\VendorInventoryService;
use App\Services\Vendor\VendorOrderService;
use App\Services\Vendor\VendorProductService;
use App\Services\Security\PermissionService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Repository bindings ────────────────────────────────────────────────
        $this->app->bind(VendorRepositoryInterface::class, VendorRepository::class);
        $this->app->bind(OrderRepositoryInterface::class,  OrderRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);

        // ── Admin panel singletons ─────────────────────────────────────────────
        $this->app->singleton(RbacService::class);
        $this->app->singleton(PermissionService::class);
        $this->app->singleton(ZoneService::class);

        // ── Shared service singletons ──────────────────────────────────────────
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(WalletService::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(StockService::class);
        $this->app->singleton(CouponService::class);
        $this->app->singleton(AppointmentService::class);
        $this->app->singleton(ReturnService::class);
        $this->app->singleton(RefundService::class);
        $this->app->singleton(ReturnRefundService::class);

        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(WalletService::class),
                $app->make(NotificationService::class),
            );
        });

        $this->app->singleton(CartCheckoutService::class, function ($app) {
            return new CartCheckoutService(
                $app->make(StockService::class),
                $app->make(CouponService::class),
            );
        });

        // ── Customer panel singletons ──────────────────────────────────────────
        $this->app->singleton(AiChatService::class);
        $this->app->singleton(CropPlanningService::class);
        $this->app->singleton(CropRecommendationService::class);
        $this->app->singleton(DiseaseDetectionService::class);
        $this->app->singleton(FertilizerRecommendationService::class);
        $this->app->singleton(WeatherService::class);

        // ── Vendor panel singletons ────────────────────────────────────────────
        $this->app->singleton(VendorOrderService::class, function ($app) {
            return new VendorOrderService(
                $app->make(CartCheckoutService::class),
                $app->make(StockService::class),
            );
        });
        $this->app->singleton(VendorProductService::class, function ($app) {
            return new VendorProductService(
                $app->make(StockService::class),
            );
        });
        $this->app->singleton(VendorCouponService::class);
        $this->app->singleton(VendorInventoryService::class, function ($app) {
            return new VendorInventoryService(
                $app->make(StockService::class),
            );
        });

        // ── Countries data shared with all views ───────────────────────────────
        $countriesData = [];
        $jsonPath = public_path('countriesdata.json');
        if (file_exists($jsonPath)) {
            $json = file_get_contents($jsonPath);
            if ($json) {
                $countriesData = json_decode($json);
            }
        }
        view()->composer('*', function ($view) use ($countriesData) {
            $view->with('countries_data', $countriesData);
        });
    }

    public function boot(): void
    {
        if (Schema::hasTable('settings')) {
            $mailDefault = Setting::get('mail_mailer');
            $mailHost = Setting::get('mail_host');
            $mailPort = Setting::get('mail_port');
            $mailUsername = Setting::get('mail_username');
            $mailPassword = Setting::get('mail_password');
            $mailEncryption = Setting::get('mail_encryption');
            $mailFromAddress = Setting::get('mail_from_address');
            $mailFromName = Setting::get('mail_from_name');

            config([
                'mail.default' => blank($mailDefault) ? env('MAIL_MAILER', config('mail.default')) : $mailDefault,
                'mail.mailers.smtp.host' => blank($mailHost) ? env('MAIL_HOST', config('mail.mailers.smtp.host')) : $mailHost,
                'mail.mailers.smtp.port' => blank($mailPort) ? env('MAIL_PORT', config('mail.mailers.smtp.port')) : $mailPort,
                'mail.mailers.smtp.username' => blank($mailUsername) ? env('MAIL_USERNAME', config('mail.mailers.smtp.username')) : $mailUsername,
                'mail.mailers.smtp.password' => blank($mailPassword) ? env('MAIL_PASSWORD', config('mail.mailers.smtp.password')) : $mailPassword,
                'mail.mailers.smtp.encryption' => blank($mailEncryption) ? env('MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')) : $mailEncryption,
                'mail.from.address' => blank($mailFromAddress) ? env('MAIL_FROM_ADDRESS', config('mail.from.address')) : $mailFromAddress,
                'mail.from.name' => blank($mailFromName) ? env('MAIL_FROM_NAME', config('mail.from.name')) : $mailFromName,
            ]);
        }
    }
}
