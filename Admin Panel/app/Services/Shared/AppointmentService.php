<?php

namespace App\Services\Shared;

use App\Models\Appointment;
use App\Models\Expert;
use App\Models\User;
use App\Notifications\AppointmentConfirmedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * Book a new appointment for a customer.
     * Uses a DB transaction with pessimistic lock to prevent double-booking.
     */
    public function book(User $user, array $data): Appointment
    {
        return DB::transaction(function () use ($user, $data) {
            $expertId    = $data['expert_id'] ?? null;
            $scheduledAt = $data['scheduled_at'];

            // Lock any existing appointment at this exact slot for this expert
            if ($expertId) {
                $conflict = Appointment::where('expert_id', $expertId)
                    ->where('scheduled_at', $scheduledAt)
                    ->whereNotIn('status', ['cancelled'])
                    ->lockForUpdate()
                    ->first();

                if ($conflict) {
                    throw ValidationException::withMessages([
                        'scheduled_at' => 'This time slot is already booked. Please choose another time.',
                    ]);
                }
            }

            $appointment = Appointment::create([
                'user_id'          => $user->id,
                'expert_id'        => $expertId,
                'scheduled_at'     => $scheduledAt,
                'duration_minutes' => $data['duration_minutes'] ?? 60,
                'status'           => 'pending',
                'notes'            => $data['notes'] ?? null,
                'fee'              => $data['fee'] ?? 0.00,
            ]);

            return $appointment->fresh(['user', 'expert']);
        });
    }

    /**
     * Confirm an appointment and notify the customer.
     */
    public function confirm(Appointment $appointment, ?int $expertId = null, ?string $adminNotes = null): Appointment
    {
        $appointment->update([
            'status'      => 'confirmed',
            'expert_id'   => $expertId ?? $appointment->expert_id,
            'admin_notes' => $adminNotes,
        ]);

        try {
            $appointment->user->notify(new AppointmentConfirmedNotification($appointment));
        } catch (\Throwable $e) {
            Log::error('Appointment confirmation notification failed: ' . $e->getMessage());
        }

        return $appointment->fresh(['expert', 'user']);
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Appointment $appointment, string $reason): Appointment
    {
        $appointment->update([
            'status'      => 'cancelled',
            'admin_notes' => $reason,
        ]);

        return $appointment->fresh();
    }

    /**
     * Complete an appointment.
     */
    public function complete(Appointment $appointment): Appointment
    {
        $appointment->update(['status' => 'completed']);
        return $appointment->fresh();
    }
}


