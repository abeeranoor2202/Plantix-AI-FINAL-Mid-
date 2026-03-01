<?php

namespace App\Mail\Expert;

use App\Mail\PlantixBaseMail;
use App\Models\Expert;
use App\Models\ForumThread;
use Illuminate\Mail\Mailables\Content;

class ForumQuestionMail extends PlantixBaseMail
{
    public function __construct(
        public readonly ForumThread $thread,
        public readonly Expert      $expert,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return "❓ New Forum Question in Your Domain: \"" . \Str::limit($this->thread->title, 60) . '"';
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.expert.forum-question',
            with: [
                'thread'        => $this->thread,
                'expert'        => $this->expert,
                'recipientEmail'=> $this->expert->user?->email ?? '',
            ]
        );
    }
}
