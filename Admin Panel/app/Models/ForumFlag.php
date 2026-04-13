<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumFlag extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_REVIEWED  = 'reviewed'; // legacy
    public const STATUS_DISMISSED = 'dismissed'; // legacy
    public const STATUS_RESOLVED  = 'resolved';
    public const STATUS_IGNORED   = 'ignored';

    public $timestamps = false;

    protected $fillable = [
        'reply_id',
        'flagged_by',
        'reason',
        'status',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'created_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function reply(): BelongsTo
    {
        return $this->belongsTo(ForumReply::class, 'reply_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
