<?php

namespace App\Mail\User;

use App\Mail\PlantixBaseMail;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;

class ForumReplyMail extends PlantixBaseMail
{
    public function __construct(
        public readonly ForumThread $thread,
        public readonly ForumReply  $reply,
        public readonly User        $recipient,
        public readonly bool        $isOfficialAnswer = false,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return $this->isOfficialAnswer
            ? "Official Expert Answer on \"{$this->thread->title}\""
            : "New Reply on Your Thread: \"{$this->thread->title}\"";
    }

    public function content(): Content
    {
        $view = $this->isOfficialAnswer
            ? 'emails.user.official-answer'
            : 'emails.user.forum-reply';

        return new Content(
            view: $view,
            with: [
                'thread'        => $this->thread,
                'reply'         => $this->reply,
                'recipient'     => $this->recipient,
                'recipientEmail'=> $this->recipient->email,
            ]
        );
    }
}
