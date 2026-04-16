<?php

namespace App\Models;

use App\Events\Forum\ForumReplyCreated;
use App\Events\Forum\OfficialAnswerMarked;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumReply extends Model
{
    use HasFactory, SoftDeletes;

    // ── Constants ─────────────────────────────────────────────────────────────

    /** Maximum reply nesting depth. Depth 0 = top-level, depth 1 = child. */
    public const MAX_DEPTH = 1;

    /** Minutes a user may edit their own reply. */
    public const EDIT_WINDOW_MINUTES = 15;

    public const STATUS_VISIBLE = 'visible';
    public const STATUS_FLAGGED = 'flagged';

    // ── Mass-assignment whitelist ─────────────────────────────────────────────
    // is_official is protected at the Form Request validation layer (not included in
    // CreateReplyRequest / UpdateReplyRequest). It is added to $fillable so that
    // service methods can use update() / forceFill() without silent failures.

    protected $fillable = [
        'thread_id',
        'user_id',
        'parent_id',
        'body',
        'status',
        'is_official',
        'is_approved',
        'is_expert_reply',
        'expert_id',
        'edited_at',
    ];

    protected $casts = [
        'is_official'    => 'boolean',
        'is_approved'    => 'boolean',
        'is_expert_reply'=> 'boolean',
        'edited_at'      => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (ForumReply $reply) {
            $thread = $reply->thread;
            if ($thread) {
                ForumReplyCreated::dispatch($thread, $reply);
            }
        });

        static::updated(function (ForumReply $reply) {
            if ($reply->wasChanged('is_official') && $reply->is_official) {
                $thread = $reply->thread;
                if ($thread) {
                    OfficialAnswerMarked::dispatch($thread, $reply);
                }
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    /** The parent reply (one level up), if this is a nested reply. */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ForumReply::class, 'parent_id');
    }

    /** Direct children (depth + 1). */
    public function children(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'parent_id');
    }

    public function flags(): HasMany
    {
        return $this->hasMany(ForumFlag::class, 'reply_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VISIBLE);
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeExpertReplies(Builder $query): Builder
    {
        return $query->where('is_expert_reply', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * True if the reply is still within the edit window.
     */
    public function isEditable(): bool
    {
        return $this->created_at->diffInMinutes(now()) <= self::EDIT_WINDOW_MINUTES;
    }

    /**
     * Compute the nesting depth (0 = top-level, 1 = child).
     * Never traverses more than MAX_DEPTH levels — anything beyond is capped.
     */
    public function depth(): int
    {
        return $this->parent_id === null ? 0 : 1;
    }
}
