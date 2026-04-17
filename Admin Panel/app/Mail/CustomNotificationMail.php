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
class CustomNotificationMail extends PlantixBaseMail
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly User $user,
        private readonly string $title,
        private readonly string $body,
        private readonly ?string $actionUrl = null,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return $this->title;
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-notification',
            with: [
                'user'           => $this->user,
                'title'          => $this->title,
                'body'           => $this->body,
                'actionUrl'      => $this->actionUrl,
                'recipientEmail' => $this->user->email,
            ],
        );
    }
}
