<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'vendor_id', 'coupon_id',
        'status', 'subtotal', 'delivery_fee', 'tax_amount', 'discount_amount',
        'total', 'payment_method', 'payment_status',
        'delivery_address', 'delivery_lat', 'delivery_lng',
        'notes', 'estimated_delivery',
    ];

    protected $casts = [
        'subtotal'           => 'decimal:2',
        'delivery_fee'       => 'decimal:2',
        'tax_amount'         => 'decimal:2',
        'discount_amount'    => 'decimal:2',
        'total'              => 'decimal:2',
        'estimated_delivery' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = strtoupper('ORD-' . Str::random(8));
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // public function driver(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'driver_id');
    // }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function walletTransaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WalletTransaction::class);
    }

    /** Full status audit trail */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    /** Return request for this order, if any */
    public function returnRequest(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ReturnRequest::class);
    }

    /** Refund for this order, if any */
    public function refund(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Refund::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeForCustomer($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isDelivered(): bool  { return $this->status === 'delivered'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }
    public function isPaid(): bool       { return $this->payment_status === 'paid'; }

    public static function statusBadgeClass(string $status): string
    {
        return match($status) {
            'pending'         => 'badge-warning',
            'accepted'        => 'badge-info',
            'preparing'       => 'badge-primary',
            'ready'           => 'badge-secondary',
            'driver_assigned' => 'badge-dark',
            'picked_up'       => 'badge-light',
            'delivered'       => 'badge-success',
            'rejected',
            'cancelled'       => 'badge-danger',
            default           => 'badge-secondary',
        };
    }
}
