<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDispute extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'vendor_id',
        'status',
        'reason',
        'escalation_reason',
        'vendor_response',
        'resolved_by',
        'resolved_at',
        'admin_notes',
        'escalated_at',
        'responded_at',
        'refund_escalated_at',
        'refund_reference',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'escalated_at' => 'datetime',
        'responded_at' => 'datetime',
        'refund_escalated_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
