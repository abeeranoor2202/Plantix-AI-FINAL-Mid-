<?php

namespace App\Listeners\User;

use App\Events\User\UserRegistered;
use App\Mail\User\WelcomeMail;
use App\Services\NotificationLogService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmailListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(private readonly NotificationLogService $notifLog) {}

    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        if (! $user->email || $user->deleted_at) return;

        $this->notifLog->send(
            mailable:         new WelcomeMail($user, $event->verificationUrl),
            to:               $user->email,
            recipientName:    $user->name,
            recipientRole:    'user',
            notificationType: 'user_welcome',
            notifiable:       $user,
            userId:           $user->id,
            dedupKey:         "user_welcome:{$user->id}",
        );
    }
}
