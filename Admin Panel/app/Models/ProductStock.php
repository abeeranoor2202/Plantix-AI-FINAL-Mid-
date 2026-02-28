<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductStock extends Model
{
    protected $table = 'product_stocks';

    protected $fillable = [
        'product_id', 'vendor_id', 'quantity', 'low_stock_threshold', 'sku',
    ];

    protected $casts = [
        'quantity'            => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isLow(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }
}
