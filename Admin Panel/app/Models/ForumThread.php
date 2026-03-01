<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ForumThread extends Model
{
    use HasFactory, SoftDeletes;

    // ── Constants ─────────────────────────────────────────────────────────────

    public const STATUS_OPEN     = 'open';
    public const STATUS_LOCKED   = 'locked';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * Valid forward state transitions.
     * Admin can override any direction independently in service layer.
     */
    public const ALLOWED_TRANSITIONS = [
        self::STATUS_OPEN     => [self::STATUS_LOCKED, self::STATUS_RESOLVED],
        self::STATUS_LOCKED   => [self::STATUS_ARCHIVED],
        self::STATUS_RESOLVED => [self::STATUS_ARCHIVED],
        self::STATUS_ARCHIVED => [],
    ];

    // ── Mass-assignment whitelist (explicit, no wildcards) ────────────────────

    protected $fillable = [
        'user_id',
        'forum_category_id',
        'title',
        'slug',
        'body',
        'status',
        'is_pinned',
        'is_approved',
        'resolved_reply_id',
        'views',
        'replies_count',
    ];

    protected $casts = [
        'is_pinned'         => 'boolean',
        'is_approved'       => 'boolean',
        'views'             => 'integer',
        'replies_count'     => 'integer',
        'resolved_reply_id' => 'integer',
    ];

    // ── Slug generation ───────────────────────────────────────────────────────

    /**
     * Generate a unique slug from a title, suffixing -N on collision.
     */
    public static function generateSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 1;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($exceptId, fn (Builder $q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    /** All top-level (non-nested) visible replies, newest first. */
    public function replies(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'thread_id')
                    ->whereNull('parent_id');
    }

    /** All replies regardless of nesting, for eager-loads and counts. */
    public function allReplies(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'thread_id');
    }

    public function resolvedReply(): BelongsTo
    {
        return $this->belongsTo(ForumReply::class, 'resolved_reply_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Atomic view increment — single UPDATE, no extra SELECT. */
    public function incrementViews(): void
    {
        static::withoutTimestamps(fn () => $this->increment('views'));
    }

    /** Atomic reply counter-cache increment. */
    public function incrementRepliesCount(): void
    {
        static::withoutTimestamps(fn () => $this->increment('replies_count'));
    }

    /** Atomic reply counter-cache decrement (floor 0). */
    public function decrementRepliesCount(): void
    {
        static::withoutTimestamps(fn () =>
            $this->decrement('replies_count', 1, ['replies_count' => \DB::raw('GREATEST(0, replies_count - 1)')])
        );
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check whether a status transition is valid for non-admin actors.
     * Admins bypass this check in the service layer.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status] ?? [], true);
    }
}
