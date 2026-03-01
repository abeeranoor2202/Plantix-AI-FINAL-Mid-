<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Notifications\Appointment\AppointmentReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Dispatched hourly by the scheduler.
 * Sends a 24-hour reminder email to both the customer and the expert
 * for every confirmed appointment starting between 23h55m and 25h from now.
 * Uses reminder_sent_at to guarantee exactly-once delivery.
 */
class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function handle(): void
    {
        $windowStart = now()->addHours(23)->addMinutes(55);
        $windowEnd   = now()->addHours(25);

        Appointment::with(['user', 'expert.user'])
            ->whereIn('status', [Appointment::STATUS_CONFIRMED])
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->whereNull('reminder_sent_at')
            ->orderBy('id')
            ->chunk(50, function ($appointments) {
                foreach ($appointments as $appointment) {
                    // Use a DB update + check to prevent duplicate sends
                    // across multiple worker processes.
                    $affected = DB::table('appointments')
                        ->where('id', $appointment->id)
                        ->whereNull('reminder_sent_at')
                        ->update(['reminder_sent_at' => now()]);

                    if ($affected === 0) {
                        continue; // Another worker already claimed it.
                    }

                    $this->sendToCustomer($appointment);
                    $this->sendToExpert($appointment);
                }
            });
    }

    private function sendToCustomer(Appointment $appointment): void
    {
        if (! $appointment->user) {
            return;
        }
        try {
            $appointment->user->notify(
                new AppointmentReminderNotification($appointment, 'customer')
            );
        } catch (\Throwable $e) {
            Log::warning("Reminder (customer) failed for appointment #{$appointment->id}: {$e->getMessage()}");
        }
    }

    private function sendToExpert(Appointment $appointment): void
    {
        $expertUser = optional($appointment->expert)->user;
        if (! $expertUser) {
            return;
        }
        try {
            $expertUser->notify(
                new AppointmentReminderNotification($appointment, 'expert')
            );
        } catch (\Throwable $e) {
            Log::warning("Reminder (expert) failed for appointment #{$appointment->id}: {$e->getMessage()}");
        }
    }
}
