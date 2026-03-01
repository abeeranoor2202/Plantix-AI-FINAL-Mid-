<?php

namespace App\Notifications\Expert;

use App\Models\Expert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifies an expert when their account status changes.
 * Sent by ExpertApprovalService on every lifecycle transition.
 */
class ExpertStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Expert  $expert,
        public readonly string  $fromStatus,
        public readonly string  $toStatus,
        public readonly string  $notes = ''
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label  = ucwords(str_replace('_', ' ', $this->toStatus));
        $name   = $this->expert->display_name;

        $mail = (new MailMessage)
            ->subject("Your expert account status: {$label}")
            ->greeting("Hello {$name},")
            ->line("Your expert account status has been updated to: **{$label}**.");

        if ($this->notes) {
            $mail->line("Note from admin: {$this->notes}");
        }

        return match ($this->toStatus) {
            'approved'     => $mail->line('You can now accept appointment bookings and answer forum questions.')->action('View Dashboard', url('/expert/dashboard')),
            'rejected'     => $mail->line('If you believe this is an error, please contact support.'),
            'suspended'    => $mail->line('Your account has been suspended. Please contact support for assistance.'),
            'under_review' => $mail->line('Your application is currently under review. We will notify you once a decision is made.'),
            'inactive'     => $mail->line('Your account has been set to inactive. Please contact support to reactivate.'),
            default        => $mail,
        };
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'expert_status_changed',
            'expert_id'   => $this->expert->id,
            'from_status' => $this->fromStatus,
            'to_status'   => $this->toStatus,
            'notes'       => $this->notes,
        ];
    }
}
