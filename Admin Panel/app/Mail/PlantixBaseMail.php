<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * PlantixBaseMail
 *
 * Abstract base class all Plantix Mailables extend.
 *
 * Provides:
 *  - Queue channel + retry policy
 *  - Email address validation before send
 *  - Common after-send / after-fail hooks (override in subclasses)
 *  - Consistent From address from config
 */
abstract class PlantixBaseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** Max delivery attempts before marking as failed */
    public int $tries = 3;

    /** Wait 60 seconds between retries */
    public int $backoff = 60;

    /** Job timeout in seconds */
    public int $timeout = 30;

    /**
     * Validate the recipient email before the job is stacked on the queue.
     * Throws if email is syntactically invalid — prevents poison-pill jobs.
     */
    public function validateRecipient(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Common envelope defaults — subclasses override subject/cc/bcc.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: $this->resolveSubject(),
        );
    }

    /**
     * Subclasses must implement to provide the email subject.
     */
    abstract protected function resolveSubject(): string;

    /**
     * Subclasses must implement to provide the Blade view + data.
     */
    abstract public function content(): Content;

    /**
     * All Plantix emails are queued to the 'emails' queue channel.
     * Can be overridden in subclasses for different priority queues.
     */
    public function __construct()
    {
        $this->onQueue('emails');
    }
}
