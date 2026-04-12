<?php

namespace App\Models;

use App\Models\CouponUserUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'vendor_id', 'code', 'type', 'value', 'min_order',
        'max_discount', 'usage_limit', 'used_count',
        'starts_at', 'expires_at', 'is_active', 'is_visible_to_all', 'per_user_limit',
    ];

    protected $casts = [
        'value'       => 'decimal:2',
        'min_order'   => 'decimal:2',
        'max_discount'=> 'decimal:2',
        'is_active'   => 'boolean',
        'starts_at'   => 'datetime',
        'expires_at'  => 'datetime',
        'is_visible_to_all' => 'boolean',
        'per_user_limit' => 'integer',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    public function applicableVendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'coupon_vendor');
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

    public function hasScopedEligibility(): bool
    {
        if ($this->is_visible_to_all) {
            return false;
        }

        return $this->products()->exists()
            || $this->categories()->exists()
            || $this->applicableVendors()->exists()
            || (int) ($this->vendor_id ?? 0) > 0;
    }
}
