<?php

namespace App\Notifications\Expert;

use App\Models\Expert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ExpertApplicationReceivedNotification
 *
 * Sent to the applicant (expert/agency) immediately after they
 * submit their registration form.  Confirms receipt and explains
 * the next steps in the review process.
 *
 * Queued so it never blocks the HTTP response.
 */
class ExpertApplicationReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Expert $expert) {}

    // ── Channels ──────────────────────────────────────────────────────────────

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    // ── Mail ──────────────────────────────────────────────────────────────────

    public function toMail(object $notifiable): MailMessage
    {
        $name     = $notifiable->name;
        $profile  = $this->expert->profile;
        $typeLabel = ($profile && $profile->account_type === 'agency') ? 'Agency' : 'Individual Expert';
        $agencyLine = ($profile && $profile->account_type === 'agency' && $profile->agency_name)
            ? ' (' . $profile->agency_name . ')'
            : '';

        return (new MailMessage)
            ->subject('✅ Application Received — Plantix AI Expert Network')
            ->greeting("Hello {$name},")
            ->line("Thank you for applying to join the **Plantix AI Expert Network** as an agricultural {$typeLabel}{$agencyLine}.")
            ->line('Your application is now **pending review**. Our team will evaluate your credentials and experience within **1–3 business days**.')
            ->line('---')
            ->line('**What happens next?**')
            ->line('① **Admin Review** — Our team verifies your qualifications and background.')
            ->line('② **Decision** — You will receive an email notification once your application is approved or if we need more information.')
            ->line('③ **Access** — Once approved, you can sign in to the Expert Panel and start accepting appointment bookings from farmers.')
            ->action('View Application Status', url('/expert/register/pending'))
            ->line('If you have any questions, reply to this email or visit our support centre.')
            ->salutation('— The Plantix AI Team');
    }

    // ── In-app / database ─────────────────────────────────────────────────────

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'expert_application_received',
            'expert_id' => $this->expert->id,
            'message'   => 'Your expert application has been received and is pending admin review.',
        ];
    }
}
