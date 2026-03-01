<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentFailed;
use App\Events\Payment\PaymentSucceeded;
use App\Mail\Admin\AdminAlertMail;
use App\Mail\User\PaymentMail;
use App\Services\NotificationLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class SendPaymentEmailListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(private readonly NotificationLogService $notifLog) {}

    public function handlePaymentSucceeded(PaymentSucceeded $event): void
    {
        $order = $event->order->load('user');

        if ($order->user?->email) {
            $this->notifLog->send(
                mailable:         new PaymentMail($order, 'success', $event->amount, $event->transactionId),
                to:               $order->user->email,
                recipientName:    $order->user->name,
                recipientRole:    'user',
                notificationType: 'payment_success',
                notifiable:       $event->payment,
                userId:           $order->user_id,
                dedupKey:         "payment_success:{$event->payment->id}",
            );
        }
    }

    public function handlePaymentFailed(PaymentFailed $event): void
    {
        $order = $event->order->load('user');

        // Email customer
        if ($order->user?->email) {
            $this->notifLog->send(
                mailable:         new PaymentMail($order, 'failed', $event->amount, $event->transactionId, $event->failureReason),
                to:               $order->user->email,
                recipientName:    $order->user->name,
                recipientRole:    'user',
                notificationType: 'payment_failed',
                notifiable:       $order,
                userId:           $order->user_id,
                dedupKey:         "payment_failed:{$order->id}:" . now()->format('YmdH'),
            );
        }

        // Alert admin
        $adminEmail = Config::get('plantix.admin_email', config('mail.from.address'));
        $this->notifLog->send(
            mailable: new AdminAlertMail(
                alertType:   'payment_failed',
                headline:    "Payment failed for order #{$order->order_number}.",
                details:     [
                    'Order #'       => $order->order_number,
                    'Customer'      => $order->user?->name ?? '—',
                    'Amount'        => '₨' . number_format($event->amount, 0),
                    'Reason'        => $event->failureReason,
                    'Transaction ID'=> $event->transactionId ?? '—',
                ],
                actionUrl:   route('admin.orders.show', $order->id),
                adminEmail:  $adminEmail,
            ),
            to:               $adminEmail,
            recipientRole:    'admin',
            notificationType: 'admin_payment_failed',
            notifiable:       $order,
            dedupKey:         "admin_payment_failed:{$order->id}:" . now()->format('YmdH'),
        );
    }

    public function handle(object $event): void
    {
        match (true) {
            $event instanceof PaymentSucceeded => $this->handlePaymentSucceeded($event),
            $event instanceof PaymentFailed    => $this->handlePaymentFailed($event),
            default                            => null,
        };
    }
}
