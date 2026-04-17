<?php


namespace App\Mail;

use Illuminate\Mail\Mailables\Content;

class SetEmailData extends PlantixBaseMail
{
    public string $dynamicSubject;
    public string $dynamicMessage;

    /**
     * Create a new message instance.
     *
     * @param string $subject
     * @param string $message
     * @return void
     */
    public function __construct(string $subject, string $message)
    {
        parent::__construct();
        $this->dynamicSubject = $subject;
        $this->dynamicMessage = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    protected function resolveSubject(): string
    {
        return $this->dynamicSubject;
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.manual-broadcast',
            with: [
                'emailSubject' => $this->dynamicSubject,
                'messageBody' => $this->dynamicMessage,
            ],
        );
    }
}