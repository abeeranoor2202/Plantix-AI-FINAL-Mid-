<?php

namespace App\Mail\Expert;

use App\Mail\PlantixBaseMail;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;

class ExpertPayoutMail extends PlantixBaseMail
{
    public function __construct(
        public readonly User $expertUser,
        public readonly float $grossAmount,
        public readonly float $commissionAmount,
        public readonly float $netAmount,
        public readonly array $metadata = [],
    ) {
        parent::__construct();
        $this->onQueue('emails-critical');
    }

    protected function resolveSubject(): string
    {
        return 'Payout Completed — Expert Earnings Settled';
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.expert.payout',
            with: [
                'expertUser' => $this->expertUser,
                'grossAmount' => $this->grossAmount,
                'commissionAmount' => $this->commissionAmount,
                'netAmount' => $this->netAmount,
                'metadata' => $this->metadata,
                'recipientEmail' => $this->expertUser->email,
            ]
        );
    }
}