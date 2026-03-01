<?php

namespace App\Mail\Expert;

use App\Mail\PlantixBaseMail;
use App\Models\Expert;
use Illuminate\Mail\Mailables\Content;

class ExpertStatusMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Expert  $expert,
        public readonly string  $status,    // approved | rejected | suspended | inactive | approved_again
        public readonly ?string $reason = null,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return match ($this->status) {
            'approved'       => '🎉 Congratulations! Your Expert Application is Approved',
            'rejected'       => 'Expert Application Update — Plantix AI',
            'suspended'      => '⚠️ Your Expert Account Has Been Suspended',
            'inactive'       => 'Expert Account Set to Inactive',
            'approved_again' => '✅ Expert Account Reactivated — Welcome Back!',
            default          => 'Expert Account Update — Plantix AI',
        };
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.expert.status',
            with: [
                'expert'        => $this->expert,
                'status'        => $this->status,
                'reason'        => $this->reason,
                'recipientEmail'=> $this->expert->user?->email ?? '',
            ]
        );
    }
}
