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

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';

    public const RESOLUTION_REFUND = 'refund';
    public const RESOLUTION_REPLACE = 'replace';
    public const RESOLUTION_STORE_CREDIT = 'store_credit';

    protected $table = 'returns';

    protected $fillable = [
        'order_id', 'user_id', 'reason_id', 'return_reason_id',
        'notes', 'description', 'status', 'admin_notes', 'vendor_notes', 'images',
        'resolution_type', 'vendor_response_notes', 'rejection_reason',
        'requested_at', 'vendor_responded_at', 'processed_at', 'completed_at',
    ];

    protected $casts = [
        'images'       => 'array',
        'requested_at'  => 'datetime',
        'vendor_responded_at' => 'datetime',
        'processed_at'  => 'datetime',
        'completed_at'  => 'datetime',
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

    public function isPending(): bool { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isRejected(): bool { return $this->status === self::STATUS_REJECTED; }
    public function isCompleted(): bool { return $this->status === self::STATUS_COMPLETED; }

    public function getStatusBadgeVariantAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_COMPLETED => 'secondary',
            default => 'secondary',
        };
    }

    public function getResolutionLabelAttribute(): ?string
    {
        return match ($this->resolution_type) {
            self::RESOLUTION_REFUND => 'Refund',
            self::RESOLUTION_REPLACE => 'Replace',
            self::RESOLUTION_STORE_CREDIT => 'Store Credit',
            default => null,
        };
    }

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
