<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'return_id', 'order_id', 'amount', 'method',
        'status', 'transaction_ref', 'notes', 'processed_at', 'processed_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class, 'return_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isProcessed(): bool { return $this->status === 'processed'; }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'wallet'  => 'Wallet',
            'original' => 'Original Payment',
            'manual'  => 'Manual',
            default   => ucfirst((string) $this->method),
        };
    }
}
