<?php

namespace App\Mail\User;

use App\Mail\PlantixBaseMail;
use Illuminate\Mail\Mailables\Content;

/**
 * CustomerCropHealthAlertMail
 * 
 * Notifies customers about crop health issues (disease detection, pest alerts, etc.)
 */
class CustomerCropHealthAlertMail extends PlantixBaseMail
{
    public function __construct(
        public readonly string $alertType,      // 'disease' | 'pest' | 'nutrient' | 'weather'
        public readonly string $cropName,
        public readonly string $severity,       // 'low' | 'medium' | 'high' | 'critical'
        public readonly string $recommendation,
        public readonly ?string $detectedIssue = null,
        public readonly ?string $recipientName = null,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        $icon = match ($this->alertType) {
            'disease' => '🦠',
            'pest'    => '🐛',
            'nutrient'=> '🥗',
            'weather' => '🌤️',
            default   => '⚠️',
        };

        $severityLabel = ucfirst($this->severity);
        
        return "{$icon} Crop Alert [{$severityLabel}] — {$this->cropName}";
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.crop-health-alert',
            with: [
                'alertType'       => $this->alertType,
                'cropName'        => $this->cropName,
                'severity'        => $this->severity,
                'detectedIssue'   => $this->detectedIssue,
                'recommendation'  => $this->recommendation,
                'recipientName'   => $this->recipientName ?? 'Farmer',
            ]
        );
    }
}
