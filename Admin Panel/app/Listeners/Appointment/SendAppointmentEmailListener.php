<?php

namespace App\Listeners\Appointment;

use App\Events\Appointment\AppointmentCreated;
use App\Events\Appointment\AppointmentStatusChanged;
use App\Mail\Admin\AdminAlertMail;
use App\Mail\Expert\ExpertAppointmentMail;
use App\Mail\User\AppointmentMail;
use App\Services\NotificationLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class SendAppointmentEmailListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(private readonly NotificationLogService $notifLog) {}

    public function handleAppointmentCreated(AppointmentCreated $event): void
    {
        $apt = $event->appointment->load(['user', 'expert.user']);

        // 1. Confirm booking to customer
        if ($apt->user?->email) {
            $this->notifLog->send(
                mailable:         new AppointmentMail($apt),
                to:               $apt->user->email,
                recipientName:    $apt->user->name,
                recipientRole:    'user',
                notificationType: 'appointment_created',
                notifiable:       $apt,
                userId:           $apt->user_id,
                dedupKey:         "appointment_created:{$apt->id}",
            );
        }

        // 2. Notify expert of new request
        if ($apt->expert?->user?->email) {
            $this->notifLog->send(
                mailable:         new ExpertAppointmentMail($apt),
                to:               $apt->expert->user->email,
                recipientName:    $apt->expert->user->name,
                recipientRole:    'expert',
                notificationType: 'expert_new_appointment',
                notifiable:       $apt,
                dedupKey:         "expert_new_appointment:{$apt->id}",
            );
        }

        // 3. Admin alert
        $adminEmail = Config::get('plantix.admin_email', config('mail.from.address'));
        $this->notifLog->send(
            mailable: new AdminAlertMail(
                alertType: 'new_order',
                headline:  "New appointment #" . $apt->id . " booked.",
                details:   [
                    'Booking #'  => $apt->id,
                    'Customer'   => $apt->user?->name ?? '—',
                    'Expert'     => $apt->expert?->user?->name ?? '—',
                    'Scheduled'  => optional($apt->scheduled_at ?? $apt->appointment_date)->format('d M Y h:i A') ?? '—',
                    'Fee'        => '₨' . number_format($apt->amount ?? $apt->expert?->consultation_price ?? 0, 0),
                ],
                actionUrl:  route('admin.appointments.show', $apt->id),
                adminEmail: $adminEmail,
            ),
            to:               $adminEmail,
            recipientRole:    'admin',
            notificationType: 'admin_new_appointment',
            notifiable:       $apt,
            dedupKey:         "admin_new_appointment:{$apt->id}",
        );
    }

    public function handleAppointmentStatusChanged(AppointmentStatusChanged $event): void
    {
        $apt = $event->appointment->load(['user', 'expert.user']);

        // Customer email
        $userStatuses = ['confirmed', 'rejected', 'cancelled', 'completed', 'reschedule_requested', 'payment_failed'];
        if (in_array($event->newStatus, $userStatuses) && $apt->user?->email) {
            $this->notifLog->send(
                mailable:         new AppointmentMail($apt, $event->note),
                to:               $apt->user->email,
                recipientName:    $apt->user->name,
                recipientRole:    'user',
                notificationType: 'appointment_' . $event->newStatus,
                notifiable:       $apt,
                userId:           $apt->user_id,
                dedupKey:         "appointment_{$event->newStatus}:{$apt->id}",
            );
        }

        // Expert email
        $expertStatuses = ['cancelled', 'completed', 'reschedule_requested'];
        if (in_array($event->newStatus, $expertStatuses) && $apt->expert?->user?->email) {
            $this->notifLog->send(
                mailable:         new ExpertAppointmentMail($apt),
                to:               $apt->expert->user->email,
                recipientName:    $apt->expert->user->name,
                recipientRole:    'expert',
                notificationType: 'expert_appointment_' . $event->newStatus,
                notifiable:       $apt,
                dedupKey:         "expert_appointment_{$event->newStatus}:{$apt->id}",
            );
        }

        // Admin on suspension-triggered cancellations
        if ($event->newStatus === 'cancelled' && str_contains($event->note ?? '', 'suspend')) {
            $adminEmail = Config::get('plantix.admin_email', config('mail.from.address'));
            $this->notifLog->send(
                mailable: new AdminAlertMail(
                    alertType:  'expert_suspended',
                    headline:   "Appointment #" . $apt->id . " cancelled due to expert suspension.",
                    details:    ['Booking #' => $apt->id, 'Customer' => $apt->user?->name ?? '—', 'Expert' => $apt->expert?->user?->name ?? '—'],
                    actionUrl:  route('admin.appointments.show', $apt->id),
                    adminEmail: $adminEmail,
                ),
                to:               $adminEmail,
                recipientRole:    'admin',
                notificationType: 'admin_expert_suspension_cancellation',
                notifiable:       $apt,
                dedupKey:         "admin_suspension_cancel:{$apt->id}",
            );
        }
    }

    public function handle(object $event): void
    {
        match (true) {
            $event instanceof AppointmentCreated       => $this->handleAppointmentCreated($event),
            $event instanceof AppointmentStatusChanged => $this->handleAppointmentStatusChanged($event),
            default                                    => null,
        };
    }
}
