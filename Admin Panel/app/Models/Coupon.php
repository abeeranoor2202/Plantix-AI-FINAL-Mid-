<?php

namespace App\Models;

use App\Models\CouponUserUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'vendor_id', 'code', 'type', 'value', 'min_order',
        'max_discount', 'usage_limit', 'used_count',
        'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value'       => 'decimal:2',
        'min_order'   => 'decimal:2',
        'max_discount'=> 'decimal:2',
        'is_active'   => 'boolean',
        'starts_at'   => 'datetime',
        'expires_at'  => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * 'discount' is the legacy column alias used in CartCheckoutService.
     * The DB column is 'value'. This accessor bridges the gap.
     */
    public function getDiscountAttribute(): ?string
    {
        return $this->value;
    }

    /** Per-user usage check — how many times this user has used this coupon */
    public function usageCountForUser(int $userId): int
    {
        return CouponUserUsage::where('coupon_id', $this->id)
                              ->where('user_id', $userId)
                              ->count();
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        return true;
    }

    /** Calculate discount for a given subtotal */
    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isValid() || $subtotal < (float) ($this->min_order ?? 0)) return 0;

        $discount = $this->type === 'percentage'
            ? $subtotal * ($this->value / 100)
            : (float) $this->value;

        if ($this->max_discount) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
