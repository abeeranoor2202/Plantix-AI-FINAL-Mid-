<?php

namespace App\Jobs;

use App\Services\Dashboard\DashboardService;
use App\Services\Security\LoggingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * RefreshDashboardCacheJob
 *
 * Scheduled every 5 minutes to pre-warm dashboard Redis cache.
 * Prevents 100 concurrent admins from hammering the DB simultaneously.
 *
 * Runs on the 'default' queue.
 * Only 1 instance runs at a time (withoutOverlapping in scheduler).
 */
class RefreshDashboardCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function handle(DashboardService $dashboard, LoggingService $logger): void
    {
        try {
            // Invalidate stale cache then re-warm all admin aggregates
            $dashboard->invalidateAdminCache();
            $dashboard->adminOverview();
            $dashboard->adminDailyRevenue(30);
            $dashboard->adminMonthlyRevenue();
            $dashboard->topSellingProducts(10);
            $dashboard->topVendors(10);
            $dashboard->expertPerformanceSummary(10);
        } catch (\Throwable $e) {
            $logger->queue('RefreshDashboardCacheJob failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
            ]);
            throw $e; // Let queue worker handle retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        logger()->error('RefreshDashboardCacheJob permanently failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
