<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const UPDATED_AT = null;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_RESERVED = 'reserved';
    public const TYPE_RELEASED = 'released';

    protected $table = 'stock_movements';

    protected $fillable = [
        'product_id', 'vendor_id', 'type', 'quantity', 'reference',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
