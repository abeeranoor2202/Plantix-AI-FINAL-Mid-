<?php

namespace App\Mail\Vendor;

use App\Mail\PlantixBaseMail;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;

class VendorPayoutMail extends PlantixBaseMail
{
    public function __construct(
        public readonly User $vendorUser,
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
        return 'Payout Completed — Vendor Earnings Settled';
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor.payout',
            with: [
                'vendorUser' => $this->vendorUser,
                'grossAmount' => $this->grossAmount,
                'commissionAmount' => $this->commissionAmount,
                'netAmount' => $this->netAmount,
                'metadata' => $this->metadata,
                'recipientEmail' => $this->vendorUser->email,
            ]
        );
    }
}