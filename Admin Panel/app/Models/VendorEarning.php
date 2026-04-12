<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id', 'order_id', 'gross_amount', 'commission_amount', 'net_amount',
        'paid_amount', 'pending_amount', 'settlement_status', 'payment_status',
        'settled_at', 'metadata',
    ];

    protected $casts = [
        'gross_amount'      => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_amount'        => 'decimal:2',
        'paid_amount'       => 'decimal:2',
        'pending_amount'    => 'decimal:2',
        'settled_at'        => 'datetime',
        'metadata'          => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
