<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    protected $table = 'stocks';

    protected $fillable = [
        'product_id', 'vendor_id', 'quantity', 'reserved_quantity', 'low_stock_threshold', 'status', 'is_available',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_available' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id', 'product_id')
            ->whereColumn('stock_movements.vendor_id', 'stocks.vendor_id');
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - (int) $this->reserved_quantity);
    }

    public function isLow(): bool
    {
        return $this->quantity > 0 && $this->available_quantity <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0 || $this->status === 'out_of_stock';
    }

    public function isUnavailable(): bool
    {
        return ! $this->is_available;
    }

    public function getDisplayStatusAttribute(): string
    {
        if (! $this->is_available) {
            return 'Unavailable';
        }

        if ($this->quantity <= 0) {
            return 'Out of Stock';
        }

        return 'In Stock';
    }

    public function getDisplayStatusBadgeClassAttribute(): string
    {
        if (! $this->is_available) {
            return 'bg-secondary';
        }

        if ($this->quantity <= 0) {
            return 'bg-danger';
        }

        return 'bg-success';
    }

    public function getSkuAttribute(): ?string
    {
        return $this->product?->sku;
    }
}
