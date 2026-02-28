<?php

namespace App\Jobs;

use App\Models\CropDiseaseReport;
use App\Services\Customer\DiseaseDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProcessDiseaseDetection
 *
 * Runs ML inference + treatment suggestion generation asynchronously so that
 * the HTTP response returns immediately to the user while the heavier work
 * happens in a queue worker.
 *
 * Dispatch example:
 *   ProcessDiseaseDetection::dispatch($report);
 */
class ProcessDiseaseDetection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying after failure.
     */
    public array $backoff = [30, 120, 300];

    /**
     * Maximum seconds the job can run before timing out.
     */
    public int $timeout = 60;

    public function __construct(private readonly CropDiseaseReport $report)
    {
        $this->onQueue('disease-detection');
    }

    public function handle(DiseaseDetectionService $service): void
    {
        // Guard against re-processing already completed reports
        $this->report->refresh();

        if (in_array($this->report->status, ['processed', 'manual_review'], true)) {
            Log::info('ProcessDiseaseDetection: report already processed, skipping.', [
                'report_id' => $this->report->id,
                'status'    => $this->report->status,
            ]);
            return;
        }

        $service->processReport($this->report);
    }

    /**
     * Handle a job failure — mark the report for manual review so it
     * doesn't stay stuck in 'pending' forever.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessDiseaseDetection job permanently failed', [
            'report_id' => $this->report->id,
            'error'     => $exception->getMessage(),
        ]);

        $this->report->update(['status' => 'manual_review']);
    }
}
