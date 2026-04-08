<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\CropDiseaseReport;
use App\Models\CropRecommendation;
use App\Models\ExpertApplication;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Policies\AppointmentPolicy;
use App\Policies\CropDiseaseReportPolicy;
use App\Policies\CropRecommendationPolicy;
use App\Policies\ExpertApplicationPolicy;
use App\Policies\ForumReplyPolicy;
use App\Policies\ForumThreadPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReturnRequestPolicy;
use App\Policies\UserPolicy;
use App\Policies\VendorPolicy;
use App\Services\Admin\RbacService;
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
        // ── Forum policies (all roles, web guard) ─────────────────────────────
        ForumThread::class        => ForumThreadPolicy::class,
        ForumReply::class         => ForumReplyPolicy::class,
        // ── Expert policies ────────────────────────────────────────────────────
        ExpertApplication::class  => ExpertApplicationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // ── Super-admin bypasses ALL Gate checks ──────────────────────────────
        // Users with role=admin and no role_id assigned get full access to everything.
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->isAdmin() && ! $user->role_id) {
                return true;
            }
            return null;
        });

        // ── Admin panel RBAC gates (group-level, used in Blade with @can) ─────
        // These delegate to RbacService which checks the admin_permissions session
        // populated by CheckUserRoleMiddleware on every admin request.
        //
        // Usage in Blade:  @can('admin.users')   ...  @endcan
        // Usage in PHP:    Gate::check('admin.users')
        //
        // Each gate checks whether the admin user's role has ANY permission
        // belonging to the specified group.

        $adminGroups = [
            'users', 'admins', 'vendors', 'approve_vendors', 'pending_vendors',
            'stores', 'vendors-document', 'drivers-document', 'items', 'item-attribute',
            'category', 'orders', 'dinein-orders', 'coupons', 'payment-method',
            'payments', 'payout-request', 'driver-payments', 'driver-payouts',
            'store-payouts', 'drivers', 'approve_drivers',
            'pending_drivers', 'reports', 'banners', 'cms', 'documents',
            'email-template', 'dynamic-notifications', 'general-notifications',
            'gift-cards', 'language', 'currency', 'tax', 'zone',
            'global-setting', 'delivery-charge', 'radius', 'admin-commission',
            'dinein', 'document-verification', 'special-offer', 'privacy', 'terms',
            'god-eye', 'review-attribute', 'roles',
        ];

        foreach ($adminGroups as $group) {
            Gate::define('admin.' . $group, function (User $user) use ($group) {
                // Super-admin already handled by Gate::before above
                if (! $user->isAdmin()) {
                    return false;
                }
                /** @var RbacService $rbac */
                $rbac = app(RbacService::class);
                return $rbac->adminHasGroup($user, $group);
            });
        }

        // ── Fine-grained admin permission gates ───────────────────────────────
        // Usage in Blade: @can('admin.perm', 'users.edit')
        Gate::define('admin.perm', function (User $user, string $permissionName) {
            if (! $user->isAdmin()) {
                return false;
            }
            /** @var RbacService $rbac */
            $rbac = app(RbacService::class);
            return $rbac->userHasPermission($user, $permissionName);
        });

        // ── Shared cross-panel convenience gates ──────────────────────────────
        Gate::define('manage-products', fn (User $user) => $user->isAdmin() || $user->isVendor());
        Gate::define('manage-orders',   fn (User $user) => $user->isAdmin() || $user->isVendor());
        Gate::define('view-reports',    fn (User $user) => $user->isAdmin());

        // ── Expert panel gates ────────────────────────────────────────────────
        Gate::define('reply_forum',           fn (User $user) => in_array($user->role, ['expert', 'agency_expert']));
        Gate::define('manage_appointments',   fn (User $user) => in_array($user->role, ['expert', 'agency_expert']));
        Gate::define('update_expert_profile', fn (User $user) => in_array($user->role, ['expert', 'agency_expert']));
        Gate::define('view_expert_panel',     fn (User $user) => in_array($user->role, ['expert', 'agency_expert']));
    }
}
