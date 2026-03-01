<?php

namespace App\Listeners\Order;

use App\Events\Order\OrderPlaced;
use App\Events\Order\OrderStatusUpdated;
use App\Mail\Admin\AdminAlertMail;
use App\Mail\User\OrderMail;
use App\Mail\Vendor\VendorOrderMail;
use App\Services\NotificationLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

/**
 * Handles both OrderPlaced and OrderStatusUpdated events.
 * Routes emails to customer + vendor + admin as appropriate.
 */
class SendOrderEmailListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(private readonly NotificationLogService $notifLog) {}

    // ── New order placed ─────────────────────────────────────────────────────

    public function handleOrderPlaced(OrderPlaced $event): void
    {
        $order = $event->order->load(['user', 'vendor.author', 'items.product']);

        // 1. Customer confirmation
        if ($order->user?->email) {
            $this->notifLog->send(
                mailable:         new OrderMail($order, 'placed'),
                to:               $order->user->email,
                recipientName:    $order->user->name,
                recipientRole:    'user',
                notificationType: 'order_placed',
                notifiable:       $order,
                userId:           $order->user_id,
                dedupKey:         "order_placed:{$order->id}",
            );
        }

        // 2. Vendor notification
        if ($order->vendor?->author?->email) {
            $this->notifLog->send(
                mailable:         new VendorOrderMail($order, $order->vendor, 'new'),
                to:               $order->vendor->author->email,
                recipientName:    $order->vendor->author->name,
                recipientRole:    'vendor',
                notificationType: 'vendor_new_order',
                notifiable:       $order,
                dedupKey:         "vendor_new_order:{$order->id}",
            );
        }

        // 3. Admin summary
        $adminEmail = Config::get('plantix.admin_email', config('mail.from.address'));
        $this->notifLog->send(
            mailable:         new AdminAlertMail(
                alertType:    'new_order',
                headline:     "A new order #{$order->order_number} has been placed.",
                details:      [
                    'Order #'   => $order->order_number,
                    'Customer'  => $order->user?->name ?? '—',
                    'Vendor'    => $order->vendor?->title ?? '—',
                    'Total'     => '₨' . number_format($order->total, 0),
                    'Payment'   => ucwords(str_replace('_', ' ', $order->payment_method ?? 'N/A')),
                ],
                actionUrl:    route('admin.orders.show', $order->id),
                adminEmail:   $adminEmail,
            ),
            to:               $adminEmail,
            recipientRole:    'admin',
            notificationType: 'admin_new_order',
            notifiable:       $order,
            dedupKey:         "admin_new_order:{$order->id}",
        );
    }

    // ── Order status changed ─────────────────────────────────────────────────

    public function handleOrderStatusUpdated(OrderStatusUpdated $event): void
    {
        $order = $event->order->load(['user', 'vendor.author', 'items.product']);

        // Customer email for meaningful status transitions
        $customerStatuses = ['confirmed', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded', 'return_requested'];
        if (in_array($event->newStatus, $customerStatuses) && $order->user?->email) {
            $this->notifLog->send(
                mailable:         new OrderMail($order, 'status_update', $event->notes),
                to:               $order->user->email,
                recipientName:    $order->user->name,
                recipientRole:    'user',
                notificationType: 'order_status_' . $event->newStatus,
                notifiable:       $order,
                userId:           $order->user_id,
                dedupKey:         "order_status:{$order->id}:{$event->newStatus}",
            );
        }

        // Vendor email for cancellations/refunds
        if (in_array($event->newStatus, ['cancelled', 'refunded']) && $order->vendor?->author?->email) {
            $this->notifLog->send(
                mailable:         new VendorOrderMail($order, $order->vendor, 'update'),
                to:               $order->vendor->author->email,
                recipientName:    $order->vendor->author->name,
                recipientRole:    'vendor',
                notificationType: 'vendor_order_' . $event->newStatus,
                notifiable:       $order,
                dedupKey:         "vendor_order:{$order->id}:{$event->newStatus}",
            );
        }
    }

    // ── Route to correct handler ──────────────────────────────────────────────

    public function handle(object $event): void
    {
        match (true) {
            $event instanceof OrderPlaced        => $this->handleOrderPlaced($event),
            $event instanceof OrderStatusUpdated => $this->handleOrderStatusUpdated($event),
            default => null,
        };
    }
}
