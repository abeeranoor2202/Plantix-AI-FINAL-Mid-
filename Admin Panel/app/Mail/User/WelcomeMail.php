<?php

namespace App\Mail\User;

use App\Mail\PlantixBaseMail;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;

class WelcomeMail extends PlantixBaseMail
{
    public function __construct(
        public readonly User $user,
        public readonly ?string $verificationUrl = null,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return 'Welcome to Plantix AI, ' . $this->user->name . '!';
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.welcome',
            with: [
                'user'            => $this->user,
                'verificationUrl' => $this->verificationUrl,
                'recipientEmail'  => $this->user->email,
            ]
        );
    }
}
