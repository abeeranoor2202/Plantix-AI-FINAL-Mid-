<?php

namespace App\Mail\Admin;

use App\Mail\PlantixBaseMail;
use Illuminate\Mail\Mailables\Content;

/**
 * AdminAlertMail — single class for all admin notification types.
 *
 * Usage:
 *   new AdminAlertMail('new_vendor', 'New vendor registered', ['Store' => 'Green Agri', ...])
 *   new AdminAlertMail('critical_error', 'DB connection failed', [...], url, label, extra_html)
 */
class AdminAlertMail extends PlantixBaseMail
{
    public function __construct(
        public readonly string $alertType,
        public readonly string $headline,
        public readonly array  $details    = [],
        public readonly ?string $actionUrl  = null,
        public readonly ?string $actionLabel = null,
        public readonly ?string $extraHtml  = null,
        public string  $adminEmail = '',
    ) {
        parent::__construct();
        // Critical alerts go to a dedicated high-priority queue
        if (in_array($alertType, ['critical_error', 'payment_failed'])) {
            $this->onQueue('emails-critical');
        }
        // Default admin email fallback
        if (empty($this->adminEmail)) {
            $this->adminEmail = config('mail.from.address');
        }
    }

    protected function resolveSubject(): string
    {
        $labels = [
            'new_vendor'       => 'New Vendor Registration',
            'new_expert'       => 'New Expert Application',
            'register_expert'  => 'New Expert Signup — Pending Review',
            'new_order'        => 'New Order Placed',
            'payment_failed'   => '🚨 Payment Failure Alert',
            'refund_request'   => 'Refund Request Received',
            'flagged_content'  => '🚩 Forum Content Flagged',
            'critical_error'   => '🔴 Critical System Error',
            'expert_suspended' => 'Expert Suspension — Pending Appointments',
        ];

        return '[Admin] ' . ($labels[$this->alertType] ?? 'System Alert') . ' — ' . config('app.name');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.alert',
            with: [
                'alertType'     => $this->alertType,
                'headline'      => $this->headline,
                'details'       => $this->details,
                'actionUrl'     => $this->actionUrl,
                'actionLabel'   => $this->actionLabel,
                'extraHtml'     => $this->extraHtml,
                'adminEmail'    => $this->adminEmail,
                'recipientEmail'=> $this->adminEmail,
            ]
        );
    }
}
