<?php

namespace App\Models;

use App\Events\Order\OrderPlaced;
use App\Events\Order\OrderStatusUpdated;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    // ── Valid order statuses ──────────────────────────────────────────────────

    public const STATUS_DRAFT           = 'draft';
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PAYMENT_FAILED  = 'payment_failed';
    public const STATUS_PENDING         = 'pending';          // COD placed
    public const STATUS_CONFIRMED       = 'confirmed';
    public const STATUS_PROCESSING      = 'processing';
    public const STATUS_SHIPPED         = 'shipped';
    public const STATUS_DELIVERED       = 'delivered';
    public const STATUS_COMPLETED       = 'completed';        // review window closed
    public const STATUS_CANCELLED       = 'cancelled';
    public const STATUS_REJECTED        = 'rejected';
    public const STATUS_RETURN_REQUESTED = 'return_requested';
    public const STATUS_RETURNED        = 'returned';
    public const STATUS_REFUNDED        = 'refunded';
    public const DISPUTE_NONE           = 'none';
    public const DISPUTE_PENDING        = 'pending';
    public const DISPUTE_VENDOR_RESPONDED = 'vendor_responded';
    public const DISPUTE_ESCALATED      = 'escalated';
    public const DISPUTE_RESOLVED       = 'resolved';
    public const DISPUTE_REJECTED       = 'rejected';
    public const DISPUTE_CANCELLED      = 'cancelled';

    protected $fillable = [
        'order_number', 'user_id', 'vendor_id', 'coupon_id',
        'status', 'subtotal', 'delivery_fee', 'tax_amount', 'discount_amount',
        'total', 'payment_method', 'payment_status', 'payment_intent_id',
        'delivery_address', 'delivery_lat', 'delivery_lng',
        'notes', 'estimated_delivery', 'delivered_at',
        'dispute_status', 'disputed_at', 'dispute_reason', 'vendor_dispute_response',
        'dispute_resolved_by', 'dispute_resolved_at', 'dispute_admin_notes',
    ];

    protected $casts = [
        'subtotal'           => 'decimal:2',
        'delivery_fee'       => 'decimal:2',
        'tax_amount'         => 'decimal:2',
        'discount_amount'    => 'decimal:2',
        'total'              => 'decimal:2',
        'estimated_delivery' => 'datetime',
        'delivered_at'       => 'datetime',
        'disputed_at'        => 'datetime',
        'dispute_resolved_at'=> 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                // Collision-resistant: timestamp prefix + 6 random hex chars
                $order->order_number = 'ORD-' . strtoupper(
                    dechex(time()) . '-' . Str::random(6)
                );
            }
        });

        static::created(function (Order $order) {
            OrderPlaced::dispatch($order);
        });

        static::updated(function (Order $order) {
            if ($order->wasChanged('status')) {
                OrderStatusUpdated::dispatch(
                    $order,
                    $order->getOriginal('status'),
                    $order->status,
                    $order->notes ?? null,
                );
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function walletTransaction(): HasOne
    {
        return $this->hasOne(WalletTransaction::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function returnRequest(): HasOne
    {
        return $this->hasOne(ReturnRequest::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(OrderDispute::class);
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

    /** Orders that are active (not terminal, not draft) */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_CANCELLED,
            self::STATUS_REJECTED,
            self::STATUS_REFUNDED,
        ]);
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    public function isPendingPayment(): bool { return $this->status === self::STATUS_PENDING_PAYMENT; }
    public function isPaymentFailed(): bool  { return $this->status === self::STATUS_PAYMENT_FAILED; }
    public function isPending(): bool        { return $this->status === self::STATUS_PENDING; }
    public function isDelivered(): bool      { return $this->status === self::STATUS_DELIVERED; }
    public function isCompleted(): bool      { return $this->status === self::STATUS_COMPLETED; }
    public function isCancelled(): bool      { return $this->status === self::STATUS_CANCELLED; }
    public function isRefunded(): bool       { return $this->status === self::STATUS_REFUNDED; }
    public function isPaid(): bool           { return $this->payment_status === 'paid'; }

    public function hasOpenDispute(): bool
    {
        return in_array($this->dispute_status ?? self::DISPUTE_NONE, [
            self::DISPUTE_PENDING,
            self::DISPUTE_VENDOR_RESPONDED,
            self::DISPUTE_ESCALATED,
        ], true);
    }

    public function canOpenDispute(): bool
    {
        return ! $this->isTerminal() && ! in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PAYMENT_FAILED], true);
    }

    public function canCancel(): bool
    {
        return $this->canTransitionTo(self::STATUS_CANCELLED);
    }

    public function canResolveDisputeTo(string $status): bool
    {
        return in_array($status, [self::DISPUTE_RESOLVED, self::DISPUTE_REJECTED, self::DISPUTE_CANCELLED], true)
            || ($status === self::DISPUTE_RESOLVED && $this->canTransitionTo(self::STATUS_COMPLETED));
    }

    /** True if the order is in a terminal state (no further transitions) */
    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_REJECTED,
            self::STATUS_REFUNDED,
        ], true);
    }

    /** True if a return can still be requested (within the return window) */
    public function isWithinReturnWindow(): bool
    {
        if ($this->status !== self::STATUS_DELIVERED) {
            return false;
        }
        $days = config('plantix.return_window_days', 7);
        $deliveredAt = $this->delivered_at ?? $this->updated_at;
        return $deliveredAt->diffInDays(now()) <= $days;
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT            => 'badge-secondary',
            self::STATUS_PENDING_PAYMENT  => 'badge-warning',
            self::STATUS_PAYMENT_FAILED   => 'badge-danger',
            self::STATUS_PENDING          => 'badge-light text-dark border',
            self::STATUS_CONFIRMED        => 'badge-info',
            self::STATUS_PROCESSING       => 'badge-primary',
            self::STATUS_SHIPPED          => 'badge-indigo',
            self::STATUS_DELIVERED        => 'badge-success',
            self::STATUS_COMPLETED        => 'badge-teal',
            self::STATUS_CANCELLED        => 'badge-danger',
            self::STATUS_REJECTED         => 'badge-danger',
            self::STATUS_RETURN_REQUESTED => 'badge-orange',
            self::STATUS_RETURNED         => 'badge-dark',
            self::STATUS_REFUNDED         => 'badge-purple',
            default                       => 'badge-secondary',
        };
    }

    /**
     * Strict forward-only transitions for non-admin actors.
     * Admin override (ANY → cancelled) is enforced at policy level.
     */
    public static function allowedTransitions(): array
    {
        return [
            self::STATUS_DRAFT            => [self::STATUS_PENDING_PAYMENT, self::STATUS_PENDING, self::STATUS_CANCELLED],
            self::STATUS_PENDING_PAYMENT  => [self::STATUS_PAYMENT_FAILED, self::STATUS_PENDING, self::STATUS_CANCELLED],
            self::STATUS_PAYMENT_FAILED   => [self::STATUS_CANCELLED],
            self::STATUS_PENDING          => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_REJECTED],
            self::STATUS_CONFIRMED        => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING       => [self::STATUS_SHIPPED, self::STATUS_CANCELLED, self::STATUS_REFUNDED],
            self::STATUS_SHIPPED          => [self::STATUS_DELIVERED],
            self::STATUS_DELIVERED        => [self::STATUS_COMPLETED, self::STATUS_RETURN_REQUESTED],
            self::STATUS_COMPLETED        => [],
            self::STATUS_CANCELLED        => [],
            self::STATUS_REJECTED         => [],
            self::STATUS_RETURN_REQUESTED => [self::STATUS_RETURNED, self::STATUS_DELIVERED],
            self::STATUS_RETURNED         => [self::STATUS_REFUNDED],
            self::STATUS_REFUNDED         => [],
        ];
    }

    /**
     * Validate a normal (non-admin) status transition.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::allowedTransitions()[$this->status] ?? [], true);
    }

    /**
     * Admin can force-cancel from ANY non-terminal status.
     */
    public function adminCanForceTo(string $newStatus): bool
    {
        if ($this->isTerminal()) {
            return false;
        }
        return in_array($newStatus, [self::STATUS_CANCELLED, self::STATUS_REFUNDED], true);
    }

    // ── Compatibility accessor ────────────────────────────────────────────────

    public function getTotalAmountAttribute(): string
    {
        return $this->total;
    }
}
