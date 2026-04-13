<?php

namespace App\Notifications;

use App\Models\CropDiseaseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiseaseReportProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly CropDiseaseReport $report) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $disease    = $this->report->detected_disease ?? 'Unknown';
        $confidence = $this->report->confidence_percent;
        $crop       = $this->report->crop_name ?? 'your crop';

        return (new MailMessage())
            ->subject("Disease Detection Result — {$crop}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your crop disease analysis has been completed.")
            ->line("**Crop:** {$crop}")
            ->line("**Detected Disease:** {$disease}")
            ->line("**Confidence:** {$confidence}")
            ->when($this->report->suggestion, fn($m) => $m
                ->line("**Treatment Summary:** " . substr($this->report->suggestion->organic_treatment ?? '', 0, 150))
            )
            ->action('View Full Report & Treatment', route('disease.show', $this->report->id))
            ->line('Act promptly on the recommended treatment plan to minimize crop loss.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'disease_report_processed',
            'report_id' => $this->report->id,
            'disease'   => $this->report->detected_disease,
            'crop'      => $this->report->crop_name,
            'message'   => "Disease detected: {$this->report->detected_disease} in {$this->report->crop_name}.",
        ];
    }
}
