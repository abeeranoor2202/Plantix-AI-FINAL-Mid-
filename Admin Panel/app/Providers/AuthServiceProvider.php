<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\CropDiseaseReport;
use App\Models\CropRecommendation;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Policies\AppointmentPolicy;
use App\Policies\CropDiseaseReportPolicy;
use App\Policies\CropRecommendationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReturnRequestPolicy;
use App\Policies\UserPolicy;
use App\Policies\VendorPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Vendor::class             => VendorPolicy::class,
        Order::class              => OrderPolicy::class,
        User::class               => UserPolicy::class,
        Product::class            => ProductPolicy::class,
        Appointment::class        => AppointmentPolicy::class,
        ReturnRequest::class      => ReturnRequestPolicy::class,
        CropRecommendation::class => CropRecommendationPolicy::class,
        CropDiseaseReport::class  => CropDiseaseReportPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // ── Super-admin bypasses ALL Gate checks ──────────────────────────────
        Gate::before(function (User $user, string $ability): ?bool {
            // Super Admin: role=admin with no assigned role_id (full access)
            if ($user->isAdmin() && ! $user->role_id) {
                return true;
            }
            return null; // let normal policy/gate evaluation continue
        });

        // ── Convenience gates used in Blade templates ─────────────────────────
        Gate::define('manage-products', fn (User $user) => $user->isAdmin() || $user->isVendor());
        Gate::define('manage-orders',   fn (User $user) => $user->isAdmin() || $user->isVendor());
        Gate::define('view-reports',    fn (User $user) => $user->isAdmin());
    }
}
