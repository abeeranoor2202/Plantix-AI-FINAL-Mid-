<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable audit log for all forum actions.
 * Never update or delete rows from this table.
 */
class ForumLog extends Model
{
    public $timestamps = false;

    // Actions constants — exhaustive, used as the only valid values for `action`
    public const ACTION_THREAD_CREATE   = 'thread.create';
    public const ACTION_THREAD_DELETE   = 'thread.delete';
    public const ACTION_THREAD_LOCK     = 'thread.lock';
    public const ACTION_THREAD_UNLOCK   = 'thread.unlock';
    public const ACTION_THREAD_PIN      = 'thread.pin';
    public const ACTION_THREAD_UNPIN    = 'thread.unpin';
    public const ACTION_THREAD_RESOLVE  = 'thread.resolve';
    public const ACTION_THREAD_ARCHIVE  = 'thread.archive';
    public const ACTION_THREAD_UNARCHIVE = 'thread.unarchive';
    public const ACTION_THREAD_APPROVE  = 'thread.approve';
    public const ACTION_THREAD_FLAG     = 'thread.flag';
    public const ACTION_REPLY_CREATE    = 'reply.create';
    public const ACTION_REPLY_EDIT      = 'reply.edit';
    public const ACTION_REPLY_DELETE    = 'reply.delete';
    public const ACTION_REPLY_FLAG      = 'reply.flag';
    public const ACTION_REPLY_OFFICIAL  = 'reply.official';
    public const ACTION_REPLY_UNOFFICIA = 'reply.unofficial';
    public const ACTION_FLAG_RESOLVE    = 'flag.resolve';
    public const ACTION_FLAG_IGNORE     = 'flag.ignore';
    public const ACTION_USER_BAN        = 'user.ban';
    public const ACTION_USER_UNBAN      = 'user.unban';

    protected $fillable = [
        'user_id',
        'action',
        'thread_id',
        'reply_id',
        'meta',
    ];

    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'thread_id');
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(ForumReply::class, 'reply_id');
    }

    // ── Static factory ────────────────────────────────────────────────────────

    public static function record(
        int    $userId,
        string $action,
        ?int   $threadId = null,
        ?int   $replyId  = null,
        array  $meta     = []
    ): static {
        return static::create([
            'user_id'   => $userId,
            'action'    => $action,
            'thread_id' => $threadId,
            'reply_id'  => $replyId,
            'meta'      => $meta ?: null,
        ]);
    }
}
