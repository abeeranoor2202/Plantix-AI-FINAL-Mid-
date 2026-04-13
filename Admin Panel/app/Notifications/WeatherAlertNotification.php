<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeatherAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly array $alert) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type     = ucwords(str_replace('_', ' ', $this->alert['type'] ?? 'Weather Alert'));
        $severity = strtoupper($this->alert['severity'] ?? 'low');
        $message  = $this->alert['message'] ?? 'Weather conditions may affect your crops.';

        $color = match ($this->alert['severity'] ?? 'low') {
            'extreme' => '#dc2626',
            'high'    => '#ea580c',
            'moderate'=> '#d97706',
            default   => '#2563eb',
        };

        return (new MailMessage())
            ->subject("🌦 Plantix Weather Alert: {$type} [{$severity}]")
            ->greeting("Hello {$notifiable->name},")
            ->line("**Weather Alert: {$type}**")
            ->line($message)
            ->line("**Severity Level: {$severity}**")
            ->action('View Weather Forecast', url('/'))
            ->line('Stay prepared. Adjust your farming activities accordingly.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'weather_alert',
            'alert_type' => $this->alert['type'] ?? 'weather_alert',
            'severity'   => $this->alert['severity'] ?? 'low',
            'message'    => $this->alert['message'] ?? '',
        ];
    }
}
