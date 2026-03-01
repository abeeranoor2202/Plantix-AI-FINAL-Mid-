<?php

namespace App\Console;

use App\Jobs\CleanupOrphanedFilesJob;
use App\Jobs\RefreshDashboardCacheJob;
use App\Jobs\SendAppointmentReminderJob;
use App\Services\Security\RBACService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // ── Appointment reminders ─────────────────────────────────────────────
        // Race-condition-safe; job uses DB update to prevent duplicate sends.
        $schedule->job(new SendAppointmentReminderJob)
                 ->hourly()
                 ->withoutOverlapping()
                 ->name('appointment-reminders')
                 ->onOneServer();

        // ── Dashboard cache refresh ───────────────────────────────────────────
        // Invalidates and re-warms all admin dashboard Redis keys.
        // Runs every 5 minutes so stats never go stale by more than one cycle.
        $schedule->job(new RefreshDashboardCacheJob)
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->name('dashboard-cache-refresh')
                 ->onOneServer();

        // ── Orphaned / stale file cleanup ─────────────────────────────────────
        // Physically deletes disk files for soft-deleted FileRecord rows
        // older than 7 days, plus unattached uploads past the same threshold.
        $schedule->job(new CleanupOrphanedFilesJob)
                 ->weekly()
                 ->withoutOverlapping()
                 ->name('cleanup-orphaned-files')
                 ->onOneServer();

        // ── RBAC permission cache warm ────────────────────────────────────────
        // Re-populates Redis permission keys for all active roles at midnight
        // so the first user request after a cache flush is never a cold miss.
        $schedule->call(fn () => app(RBACService::class)->warmPermissionCache())
                 ->dailyAt('00:00')
                 ->name('rbac-cache-warm')
                 ->onOneServer();

        // ── Prune old system_logs ─────────────────────────────────────────────
        // Keeps system_logs lean: purges rows older than 90 days each month.
        $schedule->command('db:table:prune-system-logs')
                 ->monthly()
                 ->name('prune-system-logs')
                 ->onOneServer();

        // ── Email queue processor ─────────────────────────────────────────────
        // Drains all queued email jobs every minute then exits cleanly.
        // --stop-when-empty means the process self-terminates once the queue
        // is empty, so it never accumulates overlapping processes.
        // Priority: payment/admin alerts → regular emails → default jobs.
        $schedule->command(
                'queue:work database --stop-when-empty --tries=3 --timeout=60'
                . ' --queue=emails-critical,emails,default'
            )
            ->everyMinute()
            ->withoutOverlapping(5)      // prevent overlap; lock expires after 5 min
            ->runInBackground()
            ->name('process-email-queue')
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
