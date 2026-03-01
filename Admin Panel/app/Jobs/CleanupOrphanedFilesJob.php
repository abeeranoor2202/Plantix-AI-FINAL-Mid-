<?php

namespace App\Jobs;

use App\Models\FileRecord;
use App\Services\Security\LoggingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * CleanupOrphanedFilesJob
 *
 * Scheduled weekly. Performs two cleanup passes:
 *
 * Pass 1 — Soft-deleted file records older than 7 days:
 *   The FileRecord is soft-deleted when a file is replaced or deleted.
 *   This job physically removes the disk file then force-deletes the DB record.
 *
 * Pass 2 — Orphaned file records (no fileable, created > 7 days ago):
 *   Files uploaded but never attached to a model (e.g. upload failed mid-transaction).
 *
 * Processes in chunks of 100 to avoid memory exhaustion on large tables.
 */
class CleanupOrphanedFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300; // 5 minutes

    public function handle(LoggingService $logger): void
    {
        $deleted = 0;

        // Pass 1: Soft-deleted records past retention period
        FileRecord::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(7))
            ->chunkById(100, function ($records) use (&$deleted) {
                foreach ($records as $record) {
                    $this->physicallyDelete($record);
                    $record->forceDelete();
                    $deleted++;
                }
            });

        // Pass 2: Orphaned records (no owner, > 7 days old)
        FileRecord::orphaned()
            ->chunkById(100, function ($records) use (&$deleted) {
                foreach ($records as $record) {
                    $this->physicallyDelete($record);
                    $record->forceDelete();
                    $deleted++;
                }
            });

        if ($deleted > 0) {
            $logger->file("CleanupOrphanedFilesJob removed {$deleted} stale file(s).", [
                'count' => $deleted,
            ], 'info');
        }
    }

    private function physicallyDelete(FileRecord $record): void
    {
        try {
            if (Storage::disk($record->disk)->exists($record->stored_path)) {
                Storage::disk($record->disk)->delete($record->stored_path);
            }
        } catch (\Throwable $e) {
            logger()->warning("CleanupOrphanedFilesJob: Failed to delete file from disk", [
                'file_id' => $record->id,
                'path'    => $record->stored_path,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        logger()->error('CleanupOrphanedFilesJob permanently failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
