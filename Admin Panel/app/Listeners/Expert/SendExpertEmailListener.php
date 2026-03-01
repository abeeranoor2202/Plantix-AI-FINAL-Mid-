<?php

namespace App\Listeners\Expert;

use App\Events\Expert\ExpertRegistered;
use App\Events\Expert\ExpertStatusChanged;
use App\Mail\Admin\AdminAlertMail;
use App\Mail\Expert\ExpertStatusMail;
use App\Services\NotificationLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class SendExpertEmailListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(private readonly NotificationLogService $notifLog) {}

    public function handleExpertRegistered(ExpertRegistered $event): void
    {
        $expert = $event->expert;
        $user   = $event->user;

        // Admin: new expert application
        $adminEmail = Config::get('plantix.admin_email', config('mail.from.address'));
        $this->notifLog->send(
            mailable: new AdminAlertMail(
                alertType:  'register_expert',
                headline:   "New expert application from {$user->name}.",
                details:    [
                    'Name'        => $user->name,
                    'Email'       => $user->email,
                    'Speciality'  => $expert->specialization ?? '—',
                    'Experience'  => ($expert->years_of_experience ?? '—') . ' years',
                ],
                actionUrl:  route('admin.experts.show', $expert->id),
                adminEmail: $adminEmail,
            ),
            to:               $adminEmail,
            recipientRole:    'admin',
            notificationType: 'admin_new_expert',
            notifiable:       $expert,
            dedupKey:         "admin_new_expert:{$expert->id}",
        );
    }

    public function handleExpertStatusChanged(ExpertStatusChanged $event): void
    {
        $expert = $event->expert->load('user');
        $status = $event->status;

        if ($expert->user?->email) {
            $this->notifLog->send(
                mailable:         new ExpertStatusMail($expert, $status, $event->reason),
                to:               $expert->user->email,
                recipientName:    $expert->user->name,
                recipientRole:    'expert',
                notificationType: "expert_status_{$status}",
                notifiable:       $expert,
                userId:           $expert->user_id,
                dedupKey:         "expert_status_{$status}:{$expert->id}:" . now()->format('YmdH'),
            );
        }

        // Admin alert on suspension
        if ($status === 'suspended') {
            $adminEmail = Config::get('plantix.admin_email', config('mail.from.address'));
            $this->notifLog->send(
                mailable: new AdminAlertMail(
                    alertType:  'expert_suspended',
                    headline:   "Expert " . ($expert->user?->name ?? "#{$expert->id}") . " has been suspended.",
                    details:    [
                        'Expert'  => $expert->user?->name ?? '—',
                        'Status'  => 'Suspended',
                        'Reason'  => $event->reason ?? '—',
                    ],
                    actionUrl:  route('admin.experts.show', $expert->id),
                    adminEmail: $adminEmail,
                ),
                to:               $adminEmail,
                recipientRole:    'admin',
                notificationType: 'admin_expert_suspended',
                notifiable:       $expert,
                dedupKey:         "admin_expert_suspended:{$expert->id}:" . now()->format('YmdH'),
            );
        }
    }

    public function handle(object $event): void
    {
        match (true) {
            $event instanceof ExpertRegistered    => $this->handleExpertRegistered($event),
            $event instanceof ExpertStatusChanged => $this->handleExpertStatusChanged($event),
            default                               => null,
        };
    }
}
