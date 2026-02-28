<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'gateway',
        'gateway_transaction_id',
        'gateway_refund_id',
        'amount',
        'currency',
        'status',
        'gateway_response',
        'paid_at',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeStripe($query)
    {
        return $query->where('gateway', 'stripe');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }
    public function isRefunded(): bool  { return $this->status === 'refunded'; }
}
