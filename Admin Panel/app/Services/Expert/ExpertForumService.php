<?php

namespace App\Services\Expert;

use App\Models\Expert;
use App\Models\ForumExpertResponse;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Notifications\ForumReplyNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ExpertForumService
 *
 * Handles expert participation in the farmer discussion forum.
 * Ensures expert replies are visually distinguished and expert advice is tagged.
 */
class ExpertForumService
{
    /**
     * List all open forum threads (visible to experts).
     */
    public function listThreads(array $filters = []): LengthAwarePaginator
    {
        $query = ForumThread::with(['user', 'category'])
            ->approved()
            ->withCount('replies');

        if (! empty($filters['forum_category_id'])) {
            $query->where('forum_category_id', $filters['forum_category_id']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('body', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Post a reply to a forum thread from an expert.
     * Marks the reply as expert_reply and optionally attaches structured advice.
     */
    public function postExpertReply(
        Expert $expert,
        ForumThread $thread,
        string $body,
        ?string $recommendation = null
    ): ForumReply {
        if ($thread->is_locked) {
            throw new \DomainException('This thread is locked and cannot receive new replies.');
        }

        $reply = DB::transaction(function () use ($expert, $thread, $body, $recommendation) {
            /** @var ForumReply $reply */
            $reply = ForumReply::create([
                'thread_id'       => $thread->id,
                'user_id'         => $expert->user_id,
                'body'            => $body,
                'is_approved'     => true,   // Expert replies auto-approved
                'is_expert_reply' => true,
                'expert_id'       => $expert->id,
            ]);

            // Attach structured expert response if provided
            if ($recommendation || true) {
                ForumExpertResponse::create([
                    'forum_reply_id'   => $reply->id,
                    'expert_id'        => $expert->id,
                    'is_expert_advice' => true,
                    'recommendation'   => $recommendation,
                ]);
            }

            return $reply->load(['expert', 'expertResponse']);
        });

        // Section 14 – Trigger: Expert reply added → Thread owner → Email + In-app
        $thread->loadMissing('user');
        if ($thread->user && $thread->user_id !== $expert->user_id) {
            try {
                $thread->user->notify(new ForumReplyNotification($reply, $thread));
            } catch (\Throwable $e) {
                Log::warning('Expert forum reply notification failed: ' . $e->getMessage());
            }
        }

        return $reply;
    }

    /**
     * Get expert replies posted by the given expert.
     */
    public function getExpertReplies(Expert $expert): LengthAwarePaginator
    {
        return ForumReply::with(['thread', 'expertResponse'])
            ->where('expert_id', $expert->id)
            ->where('is_expert_reply', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Increment helpful votes on a forum expert response.
     */
    public function voteHelpful(ForumExpertResponse $response): void
    {
        $response->incrementVotes();
    }
}
