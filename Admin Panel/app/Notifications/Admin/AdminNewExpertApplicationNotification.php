<?php

namespace App\Notifications\Admin;

use App\Models\Expert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * AdminNewExpertApplicationNotification
 *
 * Sent to every admin user when a new expert / agency submits their
 * registration form.  Provides a quick summary and a direct link to
 * the review page in the Admin Panel.
 *
 * Queued so it never blocks the HTTP response.
 */
class AdminNewExpertApplicationNotification extends Notification implements ShouldQueue
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
        $expert   = $this->expert;
        $user     = $expert->user;
        $profile  = $expert->profile;
        $typeLabel = ucfirst($profile?->account_type ?? 'individual');

        $mail = (new MailMessage)
            ->subject("🆕 New Expert Application — {$user->name}")
            ->greeting('Hello Admin,')
            ->line("A new **{$typeLabel}** expert application has been submitted on Plantix AI.")
            ->line('---')
            ->line("**Applicant:** {$user->name}")
            ->line("**Email:** {$user->email}")
            ->line("**Phone:** {$user->phone}")
            ->line("**Specialty:** {$expert->specialty}")
            ->line("**Experience:** " . ($profile?->experience_years ?? 'N/A') . ' years')
            ->line("**Location:** " . ($profile?->city ?? '—') . ', ' . ($profile?->country ?? '—'));

        if ($profile?->account_type === 'agency' && $profile?->agency_name) {
            $mail->line("**Agency:** {$profile->agency_name}");
        }

        if ($profile?->website) {
            $mail->line("**Website:** {$profile->website}");
        }

        if ($profile?->linkedin) {
            $mail->line("**LinkedIn:** {$profile->linkedin}");
        }

        return $mail
            ->line('---')
            ->action('Review Application', url("/admin/experts/{$expert->id}"))
            ->line('Please review and approve or reject this application at your earliest convenience.')
            ->salutation('— Plantix AI System');
    }

    // ── In-app / database ─────────────────────────────────────────────────────

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'admin_new_expert_application',
            'expert_id'  => $this->expert->id,
            'applicant'  => $this->expert->user->name,
            'message'    => "New expert application from {$this->expert->user->name} ({$this->expert->user->email}).",
        ];
    }
}
