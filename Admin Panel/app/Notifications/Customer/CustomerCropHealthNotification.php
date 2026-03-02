<?php

namespace App\Notifications\Customer;

use App\Mail\User\CustomerCropHealthAlertMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * CustomerCropHealthNotification
 * 
 * Alerts customers about crop health issues: disease, pests, nutrient deficiency, weather damage
 */
class CustomerCropHealthNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $alertType,      // 'disease' | 'pest' | 'nutrient' | 'weather'
        public readonly string $cropName,
        public readonly string $severity,       // 'low' | 'medium' | 'high' | 'critical'
        public readonly string $recommendation,
        public readonly ?string $detectedIssue = null,
    ) {
        $this->onQueue('emails');
    }

    /**
     * Email only (as per requirement)
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): CustomerCropHealthAlertMail
    {
        return new CustomerCropHealthAlertMail(
            alertType:       $this->alertType,
            cropName:        $this->cropName,
            severity:        $this->severity,
            recommendation:  $this->recommendation,
            detectedIssue:   $this->detectedIssue,
            recipientName:   $notifiable->name ?? 'Farmer',
        );
    }
}
