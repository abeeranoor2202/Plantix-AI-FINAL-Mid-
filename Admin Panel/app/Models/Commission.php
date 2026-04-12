<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'vendor_id', 'gross_amount', 'commission_rate', 'commission_amount',
        'net_amount', 'status', 'calculated_at', 'settled_at', 'metadata',
    ];

    protected $casts = [
        'gross_amount'       => 'decimal:2',
        'commission_rate'    => 'decimal:2',
        'commission_amount'  => 'decimal:2',
        'net_amount'         => 'decimal:2',
        'calculated_at'      => 'datetime',
        'settled_at'         => 'datetime',
        'metadata'           => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
