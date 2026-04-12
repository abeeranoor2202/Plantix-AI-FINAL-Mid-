<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReturnRequest extends Model
{
    use SoftDeletes;

    protected $table = 'returns';

    protected $fillable = [
        'order_id', 'user_id', 'reason_id', 'return_reason_id',
        'notes', 'description', 'status', 'admin_notes', 'vendor_notes', 'images',
        'requested_at', 'processed_at',
    ];

    protected $casts = [
        'images'       => 'array',
        'requested_at'  => 'datetime',
        'processed_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReturnReason::class, 'reason_id');
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class, 'return_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($query) { return $query->where('status', 'pending'); }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }

    public function getDescriptionAttribute($value): ?string
    {
        return $this->notes ?? $value;
    }

    public function setDescriptionAttribute($value): void
    {
        $this->attributes['notes'] = $value;
        $this->attributes['description'] = $value;
    }

    public function setReasonIdAttribute($value): void
    {
        $this->attributes['reason_id'] = $value;
        $this->attributes['return_reason_id'] = $value;
    }
}
