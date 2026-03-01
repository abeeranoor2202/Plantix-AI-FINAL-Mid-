<?php

namespace App\Listeners\Vendor;

use App\Events\Vendor\VendorStatusChanged;
use App\Mail\Admin\AdminAlertMail;
use App\Mail\Vendor\VendorStatusMail;
use App\Services\NotificationLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class SendVendorStatusEmailListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(private readonly NotificationLogService $notifLog) {}

    public function handle(VendorStatusChanged $event): void
    {
        $vendor = $event->vendor->load('user');
        $status = $event->status;
        $reason = $event->reason;

        // Email the vendor user
        if ($vendor->user?->email) {
            $this->notifLog->send(
                mailable:         new VendorStatusMail($vendor, $status, $reason),
                to:               $vendor->user->email,
                recipientName:    $vendor->user->name,
                recipientRole:    'vendor',
                notificationType: "vendor_status_{$status}",
                notifiable:       $vendor,
                userId:           $vendor->user_id,
                dedupKey:         "vendor_status_{$status}:{$vendor->id}:" . now()->format('YmdH'),
            );
        }

        // Admin alert on new vendor registration or suspension
        $adminTriggers = ['pending', 'suspended', 'rejected'];
        if (in_array($status, $adminTriggers)) {
            $alertType  = $status === 'pending' ? 'new_vendor' : 'vendor_violation';
            $adminEmail = Config::get('plantix.admin_email', config('mail.from.address'));

            $this->notifLog->send(
                mailable: new AdminAlertMail(
                    alertType:  $alertType,
                    headline:   match ($status) {
                        'pending'   => "New vendor registration: {$vendor->business_name}.",
                        'suspended' => "Vendor {$vendor->business_name} has been suspended.",
                        default     => "Vendor {$vendor->business_name} status changed to {$status}.",
                    },
                    details:    [
                        'Business' => $vendor->business_name ?? $vendor->user?->name ?? '—',
                        'Status'   => ucfirst($status),
                        'Reason'   => $reason ?? '—',
                    ],
                    actionUrl:  route('admin.vendors.show', $vendor->id),
                    adminEmail: $adminEmail,
                ),
                to:               $adminEmail,
                recipientRole:    'admin',
                notificationType: "admin_vendor_{$status}",
                notifiable:       $vendor,
                dedupKey:         "admin_vendor_{$status}:{$vendor->id}",
            );
        }
    }
}
