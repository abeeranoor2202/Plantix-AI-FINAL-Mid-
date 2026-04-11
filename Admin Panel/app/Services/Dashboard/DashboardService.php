<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * DashboardService
 *
 * All dashboard stats are cached in Redis with TTL.
 * A scheduled job (RefreshDashboardCacheJob) re-warms the cache every 5 min.
 *
 * Design principles:
 *   - Every stat is a single aggregate SQL query (no N+1)
 *   - Soft-deleted records excluded via DB scoping
 *   - All monetary values in cents → display layer divides by 100
 *   - Revenue uses orders.total ONLY when status NOT IN (cancelled, refunded)
 *   - Refunds tracked separately via refunds table
 *
 * Cache keys follow: dashboard:{panel}:{stat}
 * TTL: 5 minutes for near-realtime panels, 1 hour for heavy export stats
 */
class DashboardService
{
    const TTL_SHORT  = 300;    // 5 minutes
    const TTL_MEDIUM = 1800;   // 30 minutes
    const TTL_LONG   = 3600;   // 1 hour

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN DASHBOARD
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * All admin overview stats in a single cached call.
     * Batches 8 queries — avoids 8 separate cache reads per page load.
     */
    public function adminOverview(): array
    {
        return Cache::remember('dashboard:admin:overview', self::TTL_SHORT, function () {
            return [
                'total_users'            => $this->countUsers(),
                'total_vendors'          => $this->countVendors(),
                'total_experts'          => $this->countExperts(),
                'total_orders'           => $this->countOrders(),
                'revenue_total'          => $this->totalRevenue(),
                'revenue_this_month'     => $this->revenueThisMonth(),
                'total_refunds'          => $this->totalRefunds(),
                'pending_appointments'   => $this->countPendingAppointments(),
                'active_forum_threads'   => $this->countActiveForumThreads(),
                'pending_return_requests'=> $this->countPendingReturns(),
            ];
        });
    }

    /**
     * Daily revenue for last 30 days — for the revenue chart.
     * Cached separately because it's the heaviest query.
     */
    public function adminDailyRevenue(int $days = 30): array
    {
        return Cache::remember("dashboard:admin:daily_revenue:{$days}", self::TTL_MEDIUM, function () use ($days) {
            return DB::table('orders')
                ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as order_count')
                ->whereNull('deleted_at')
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->where('created_at', '>=', now()->subDays($days))
                ->groupByRaw('DATE(created_at)')
                ->orderBy('date')
                ->get()
                ->keyBy('date')
                ->toArray();
        });
    }

    /**
     * Monthly revenue for last 12 months.
     */
    public function adminMonthlyRevenue(): array
    {
        return Cache::remember('dashboard:admin:monthly_revenue', self::TTL_LONG, function () {
            return DB::table('orders')
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue, COUNT(*) as order_count")
                ->whereNull('deleted_at')
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
                ->orderBy('month')
                ->get()
                ->toArray();
        });
    }

    /**
     * Top 10 selling products by quantity ordered.
     */
    public function topSellingProducts(int $limit = 10): array
    {
        return Cache::remember("dashboard:admin:top_products:{$limit}", self::TTL_LONG, function () use ($limit) {
            return DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->selectRaw('products.id, products.name, SUM(order_items.quantity) as units_sold, SUM(order_items.total) as revenue')
                ->whereNull('products.deleted_at')
                ->whereNull('orders.deleted_at')
                ->whereNotIn('orders.status', ['cancelled', 'refunded'])
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('units_sold')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Top 10 vendors by revenue.
     */
    public function topVendors(int $limit = 10): array
    {
        return Cache::remember("dashboard:admin:top_vendors:{$limit}", self::TTL_LONG, function () use ($limit) {
            return DB::table('orders')
                ->join('vendors', 'orders.vendor_id', '=', 'vendors.id')
                ->selectRaw('vendors.id, vendors.title as name, COUNT(orders.id) as total_orders, SUM(orders.total) as total_revenue')
                ->whereNull('orders.deleted_at')
                ->whereNull('vendors.deleted_at')
                ->whereNotIn('orders.status', ['cancelled', 'refunded'])
                ->groupBy('vendors.id', 'vendors.title')
                ->orderByDesc('total_revenue')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Expert performance summary — top 10 by completed appointments.
     */
    public function expertPerformanceSummary(int $limit = 10): array
    {
        return Cache::remember("dashboard:admin:expert_perf:{$limit}", self::TTL_LONG, function () use ($limit) {
            return DB::table('experts')
                ->join('users', 'experts.user_id', '=', 'users.id')
                ->selectRaw('experts.id, users.name, experts.rating_avg, experts.total_completed, experts.total_cancelled, experts.status')
                ->whereNull('experts.deleted_at')
                ->where('experts.status', 'approved')
                ->orderByDesc('experts.total_completed')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // VENDOR DASHBOARD
    // ══════════════════════════════════════════════════════════════════════════

    public function vendorOverview(int $vendorId): array
    {
        return Cache::remember("dashboard:vendor:{$vendorId}:overview", self::TTL_SHORT, function () use ($vendorId) {
            $revenue = DB::table('orders')
                ->whereNull('deleted_at')
                ->where('vendor_id', $vendorId)
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->selectRaw('SUM(total) as total_revenue, COUNT(*) as total_orders')
                ->first();

            $statusBreakdown = DB::table('orders')
                ->whereNull('deleted_at')
                ->where('vendor_id', $vendorId)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $lowStock = DB::table('products')
                ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                ->whereNull('products.deleted_at')
                ->where('products.vendor_id', $vendorId)
                ->where('product_stocks.quantity', '<=', DB::raw('products.low_stock_threshold'))
                ->count();

            return [
                'total_revenue'        => $revenue->total_revenue ?? 0,
                'total_orders'         => $revenue->total_orders ?? 0,
                'revenue_this_month'   => $this->vendorRevenueThisMonth($vendorId),
                'pending_orders'       => $statusBreakdown['pending'] ?? 0,
                'processing_orders'    => $statusBreakdown['processing'] ?? 0,
                'low_stock_products'   => $lowStock,
                'order_status_summary' => $statusBreakdown,
            ];
        });
    }

    public function vendorRevenueThisMonth(int $vendorId): float
    {
        return (float) DB::table('orders')
            ->whereNull('deleted_at')
            ->where('vendor_id', $vendorId)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXPERT DASHBOARD
    // ══════════════════════════════════════════════════════════════════════════

    public function expertOverview(int $expertId): array
    {
        return Cache::remember("dashboard:expert:{$expertId}:overview", self::TTL_SHORT, function () use ($expertId) {
            $stats = DB::table('experts')
                ->where('id', $expertId)
                ->selectRaw('rating_avg, total_appointments, total_completed, total_cancelled')
                ->first();

            $cancellationRate = ($stats && $stats->total_appointments > 0)
                ? round(($stats->total_cancelled / $stats->total_appointments) * 100, 1)
                : 0;

            $earningsThisMonth = DB::table('appointments')
                ->whereNull('deleted_at')
                ->where('expert_id', $expertId)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('fee');

            $upcomingCount = DB::table('appointments')
                ->whereNull('deleted_at')
                ->where('expert_id', $expertId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('scheduled_at', '>=', now())
                ->count();

            return [
                'upcoming_appointments'  => $upcomingCount,
                'total_completed'        => $stats->total_completed ?? 0,
                'total_cancelled'        => $stats->total_cancelled ?? 0,
                'cancellation_rate_pct'  => $cancellationRate,
                'average_rating'         => $stats->rating_avg ?? null,
                'earnings_this_month'    => $earningsThisMonth,
            ];
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CACHE MANAGEMENT
    // ══════════════════════════════════════════════════════════════════════════

    /** Invalidate all admin overview cache keys. Called by scheduler job. */
    public function invalidateAdminCache(): void
    {
        Cache::forget('dashboard:admin:overview');
        Cache::forget('dashboard:admin:monthly_revenue');
        Cache::forget('dashboard:admin:daily_revenue:30');
        Cache::forget('dashboard:admin:top_products:10');
        Cache::forget('dashboard:admin:top_vendors:10');
        Cache::forget('dashboard:admin:expert_perf:10');
    }

    /** Invalidate vendor cache for a specific vendor. */
    public function invalidateVendorCache(int $vendorId): void
    {
        Cache::forget("dashboard:vendor:{$vendorId}:overview");
    }

    /** Invalidate expert cache for a specific expert. */
    public function invalidateExpertCache(int $expertId): void
    {
        Cache::forget("dashboard:expert:{$expertId}:overview");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PRIVATE ATOMIC QUERIES
    // ══════════════════════════════════════════════════════════════════════════

    private function countUsers(): int
    {
        return (int) DB::table('users')->whereNull('deleted_at')->where('role', 'user')->count();
    }

    private function countVendors(): int
    {
        return (int) DB::table('vendors')->whereNull('deleted_at')->count();
    }

    private function countExperts(): int
    {
        return (int) DB::table('experts')->whereNull('deleted_at')->where('status', 'approved')->count();
    }

    private function countOrders(): int
    {
        return (int) DB::table('orders')->whereNull('deleted_at')->count();
    }

    private function totalRevenue(): float
    {
        return (float) DB::table('orders')
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total');
    }

    private function revenueThisMonth(): float
    {
        return (float) DB::table('orders')
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
    }

    private function totalRefunds(): float
    {
        return (float) DB::table('refunds')
            ->whereNull('deleted_at')
            ->whereIn('status', ['approved', 'processed'])
            ->sum('amount');
    }

    private function countPendingAppointments(): int
    {
        return (int) DB::table('appointments')
            ->whereNull('deleted_at')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_at', '>=', now())
            ->count();
    }

    private function countActiveForumThreads(): int
    {
        return (int) DB::table('forum_threads')
            ->whereNull('deleted_at')
            ->where('status', 'open')
            ->count();
    }

    private function countPendingReturns(): int
    {
        return (int) DB::table('return_requests')
            ->whereNull('deleted_at')
            ->where('status', 'pending')
            ->count();
    }
}
