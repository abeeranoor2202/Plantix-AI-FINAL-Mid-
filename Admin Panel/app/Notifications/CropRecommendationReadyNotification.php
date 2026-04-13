<?php

namespace App\Notifications;

use App\Models\CropRecommendation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CropRecommendationReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly CropRecommendation $recommendation) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $topCrop = $this->recommendation->top_crop ?? 'Various Crops';
        $count   = count($this->recommendation->recommended_crops ?? []);

        return (new MailMessage())
            ->subject("Your Crop Recommendation is Ready — {$topCrop}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your personalized crop recommendation has been generated.")
            ->line("**Top Recommended Crop:** {$topCrop}")
            ->line("**Total Suggestions:** {$count} crops analyzed")
            ->line($this->recommendation->explanation ?? '')
            ->action('View Full Recommendation', url('/crop-recommendation'))
            ->line('Review the full ranking and select the best crop for your soil conditions.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'              => 'crop_recommendation_ready',
            'recommendation_id' => $this->recommendation->id,
            'top_crop'          => $this->recommendation->top_crop,
            'message'           => "Your crop recommendation is ready. Top pick: {$this->recommendation->top_crop}",
        ];
    }
}
