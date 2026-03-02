<?php

namespace App\Mail\User;

use App\Mail\PlantixBaseMail;
use Illuminate\Mail\Mailables\Content;

/**
 * CustomerWeatherAlertMail
 * 
 * Notifies customers about critical weather conditions
 */
class CustomerWeatherAlertMail extends PlantixBaseMail
{
    public function __construct(
        public readonly string $weatherType,     // 'rainfall' | 'temperature' | 'humidity' | 'wind'
        public readonly string $severity,        // 'advisory' | 'warning' | 'alert'
        public readonly string $description,
        public readonly ?string $recommendation = null,
        public readonly ?string $affectedArea = null,
        public readonly ?string $recipientName = null,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        $icon = match ($this->weatherType) {
            'rainfall'    => '🌧️',
            'temperature' => '🌡️',
            'humidity'    => '💧',
            'wind'        => '💨',
            default       => '🌤️',
        };

        $severityLabel = ucfirst($this->severity);
        
        return "{$icon} Weather {$severityLabel} — {$this->weatherType}";
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.weather-alert',
            with: [
                'weatherType'     => $this->weatherType,
                'severity'        => $this->severity,
                'description'     => $this->description,
                'recommendation'  => $this->recommendation,
                'affectedArea'    => $this->affectedArea,
                'recipientName'   => $this->recipientName ?? 'Farmer',
            ]
        );
    }
}
