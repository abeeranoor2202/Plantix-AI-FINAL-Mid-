<?php

namespace App\Providers;

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\VendorRepositoryInterface;
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
use Illuminate\Support\ServiceProvider;

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
        //
    }
}
