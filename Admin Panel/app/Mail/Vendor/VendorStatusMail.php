<?php

namespace App\Mail\Vendor;

use App\Mail\PlantixBaseMail;
use App\Models\Vendor;
use Illuminate\Mail\Mailables\Content;

class VendorStatusMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Vendor  $vendor,
        public readonly string  $status,    // approved | rejected | suspended | active
        public readonly ?string $reason = null,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return match ($this->status) {
            'approved'  => "🎉 Your store \"{$this->vendor->title}\" has been approved!",
            'rejected'  => "Store Application Update — {$this->vendor->title}",
            'suspended' => "Account Suspended — {$this->vendor->title}",
            'active'    => "Store Reactivated — {$this->vendor->title}",
            default     => "Account Update — {$this->vendor->title}",
        };
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor.status',
            with: [
                'vendor'        => $this->vendor,
                'status'        => $this->status,
                'reason'        => $this->reason,
                'recipientEmail'=> $this->vendor->author?->email ?? '',
            ]
        );
    }
}
