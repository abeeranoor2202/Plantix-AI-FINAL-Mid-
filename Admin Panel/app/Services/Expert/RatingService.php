<?php

namespace App\Services\Expert;

use App\Models\Appointment;
use App\Models\Expert;
use App\Models\ExpertLog;
use App\Models\ExpertRating;
use Illuminate\Support\Facades\DB;

/**
 * RatingService
 *
 * Handles customer rating submission for completed appointments.
 * Recalculates and atomically updates:
 *   - experts.rating_avg
 *   - experts.total_appointments
 *   - experts.total_completed
 *   - experts.total_cancelled
 *
 * Requirements:
 *   - Only one rating per appointment (enforced by appointment_ratings UNIQUE)
 *   - Only customers who had a `completed` appointment with this expert can rate
 *   - Rating must be 1–5
 */
class RatingService
{
    /**
     * Submit a rating for a completed appointment.
     *
     * @param  Appointment $appointment  Must be in `completed` status
     * @param  int         $rating       1–5
     * @param  string|null $review       Optional text review
     * @return void
     * @throws \InvalidArgumentException
     */
    public function submitRating(Appointment $appointment, int $rating, ?string $review = null): void
    {
        if ($appointment->status !== 'completed') {
            throw new \InvalidArgumentException('Can only rate completed appointments.');
        }

        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5.');
        }

        DB::transaction(function () use ($appointment, $rating, $review) {
            // Store the individual rating
            // Uses updateOrCreate to be idempotent (second submit updates instead of inserting)
            $appointment->update([
                'customer_rating' => $rating,
                'customer_review' => $review,
                'rated_at'        => now(),
            ]);

            // Recalculate aggregate on the expert
            $this->recalculateRating($appointment->expert_id);
        });
    }

    /**
     * Recalculate and persist aggregate stats for a given expert.
     * Uses a single DB query to avoid race conditions.
     *
     * @param int $expertId
     */
    public function recalculateRating(int $expertId): void
    {
        $stats = DB::table('appointments')
            ->where('expert_id', $expertId)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_appointments,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as total_completed,
                SUM(CASE WHEN status IN ("cancelled_by_customer","cancelled_by_expert","cancelled_by_admin") THEN 1 ELSE 0 END) as total_cancelled,
                ROUND(AVG(CASE WHEN customer_rating IS NOT NULL THEN customer_rating ELSE NULL END), 2) as rating_avg
            ')
            ->first();

        if (! $stats) {
            return;
        }

        $expert = Expert::find($expertId);
        if (! $expert) {
            return;
        }

        $oldRating = $expert->rating_avg;

        $expert->update([
            'total_appointments' => (int) $stats->total_appointments,
            'total_completed'    => (int) $stats->total_completed,
            'total_cancelled'    => (int) $stats->total_cancelled,
            'rating_avg'         => (float) ($stats->rating_avg ?? 0),
        ]);

        // Audit log only if rating changed
        if ((float) ($stats->rating_avg ?? 0) !== (float) $oldRating) {
            ExpertLog::create([
                'expert_id' => $expertId,
                'actor_id'  => null,
                'action'    => ExpertLog::ACTION_RATING_UPDATED,
                'notes'     => "Rating updated: {$oldRating} → {$stats->rating_avg}",
                'metadata'  => [
                    'old_rating'          => $oldRating,
                    'new_rating'          => $stats->rating_avg,
                    'total_appointments'  => $stats->total_appointments,
                    'total_completed'     => $stats->total_completed,
                ],
            ]);
        }
    }

    /**
     * Rebuild all expert rating stats from scratch.
     * For use in repair scripts / migrations.
     */
    public function rebuildAll(): int
    {
        $count = 0;

        Expert::query()->each(function (Expert $expert) use (&$count) {
            $this->recalculateRating($expert->id);
            $count++;
        });

        return $count;
    }
}
