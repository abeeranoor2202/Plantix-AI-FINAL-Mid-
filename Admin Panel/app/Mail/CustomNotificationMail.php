<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * CustomNotificationMail
 *
 * Professional mailable for sending custom admin notifications to users via SMTP.
 * Features:
 * - Responsive HTML email template
 * - Custom tracking headers
 * - Queue support for async delivery
 * - Proper error handling
 */
class CustomNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly User $user,
        private readonly string $title,
        private readonly string $body,
        private readonly ?string $actionUrl = null,
    ) {
        $this->queue = 'notifications';
        $this->tries = 3;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name') ?? 'Plantix AI'
            ),
            subject: $this->title,
            tags: ['admin-notification', 'custom'],
            metadata: [
                'user_id' => $this->user->id,
                'user_role' => $this->user->role,
                'sent_at' => now()->toIso8601String(),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-notification',
            with: [
                'user'      => $this->user,
                'title'     => $this->title,
                'body'      => $this->body,
                'actionUrl' => $this->actionUrl,
                'appName'   => config('app.name'),
                'appUrl'    => config('app.url'),
            ],
        );
    }
}
