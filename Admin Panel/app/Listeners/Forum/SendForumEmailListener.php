<?php

namespace App\Listeners\Forum;

use App\Events\Forum\ContentFlagged;
use App\Events\Forum\ForumReplyCreated;
use App\Events\Forum\OfficialAnswerMarked;
use App\Mail\Admin\AdminAlertMail;
use App\Mail\User\ForumReplyMail;
use App\Services\NotificationLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class SendForumEmailListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(private readonly NotificationLogService $notifLog) {}

    public function handleForumReplyCreated(ForumReplyCreated $event): void
    {
        $thread = $event->thread->load('user');
        $reply  = $event->reply->load('user');

        // Notify thread author (not if they replied themselves)
        if ($thread->user?->email && $thread->user_id !== $reply->user_id) {
            $this->notifLog->send(
                mailable:         new ForumReplyMail($thread, $reply, $thread->user, false),
                to:               $thread->user->email,
                recipientName:    $thread->user->name,
                recipientRole:    'user',
                notificationType: 'forum_reply',
                notifiable:       $reply,
                userId:           $thread->user_id,
                dedupKey:         "forum_reply:{$thread->id}:{$reply->id}",
            );
        }
    }

    public function handleOfficialAnswerMarked(OfficialAnswerMarked $event): void
    {
        $thread = $event->thread->load('user');
        $reply  = $event->reply->load('user');

        if ($thread->user?->email) {
            $this->notifLog->send(
                mailable:         new ForumReplyMail($thread, $reply, $thread->user, true),
                to:               $thread->user->email,
                recipientName:    $thread->user->name,
                recipientRole:    'user',
                notificationType: 'forum_official_answer',
                notifiable:       $reply,
                userId:           $thread->user_id,
                dedupKey:         "forum_official_answer:{$thread->id}:{$reply->id}",
            );
        }
    }

    public function handleContentFlagged(ContentFlagged $event): void
    {
        $thread = $event->thread?->load('user');
        $flag   = $event->flag;

        $adminEmail = Config::get('plantix.admin_email', config('mail.from.address'));
        $this->notifLog->send(
            mailable: new AdminAlertMail(
                alertType: 'flagged_content',
                headline:  "Content flagged on forum: \"" . \Str::limit($thread?->title ?? 'Unknown thread', 60) . "\"",
                details:   [
                    'Thread'      => $thread?->title ?? '—',
                    'Reported by' => $flag->reporter?->name ?? '—',
                    'Reason'      => $flag->reason ?? '—',
                    'Content type'=> $flag->flaggable_type ?? '—',
                ],
                actionUrl:  route('admin.forum.flags.show', $flag->id),
                adminEmail: $adminEmail,
            ),
            to:               $adminEmail,
            recipientRole:    'admin',
            notificationType: 'admin_flagged_content',
            notifiable:       $flag,
            dedupKey:         "admin_flagged:{$flag->id}",
        );
    }

    public function handle(object $event): void
    {
        match (true) {
            $event instanceof ForumReplyCreated    => $this->handleForumReplyCreated($event),
            $event instanceof OfficialAnswerMarked => $this->handleOfficialAnswerMarked($event),
            $event instanceof ContentFlagged       => $this->handleContentFlagged($event),
            default                                => null,
        };
    }
}
