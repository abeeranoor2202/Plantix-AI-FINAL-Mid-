<?php

namespace App\Notifications\Customer;

use App\Mail\User\CustomerWeatherAlertMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * CustomerWeatherAlertNotification
 * 
 * Alerts customers about critical weather conditions that may impact their crops
 */
class CustomerWeatherAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $weatherType,     // 'rainfall' | 'temperature' | 'humidity' | 'wind'
        public readonly string $severity,        // 'advisory' | 'warning' | 'alert'
        public readonly string $description,
        public readonly ?string $recommendation = null,
        public readonly ?string $affectedArea = null,
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

    public function toMail(object $notifiable): CustomerWeatherAlertMail
    {
        return new CustomerWeatherAlertMail(
            weatherType:     $this->weatherType,
            severity:        $this->severity,
            description:     $this->description,
            recommendation:  $this->recommendation,
            affectedArea:    $this->affectedArea,
            recipientName:   $notifiable->name ?? 'Farmer',
        );
    }
}
