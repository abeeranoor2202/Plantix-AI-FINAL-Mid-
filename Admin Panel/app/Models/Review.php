<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /** How many hours after creation a review can still be edited */
    public const EDIT_LOCK_HOURS = 24;

    protected $fillable = [
        'user_id', 'vendor_id', 'product_id', 'order_id',
        'rating', 'comment', 'is_active', 'status', 'edit_locked_at',
    ];

    protected $casts = [
        'rating'         => 'integer',
        'is_active'      => 'boolean',
        'edit_locked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // On creation, schedule the edit-lock timestamp
        static::creating(function (Review $review) {
            $review->edit_locked_at = now()->addHours(self::EDIT_LOCK_HOURS);
        });

        // Recalculate both vendor AND product ratings after every approved review save/delete
        $recalc = function (Review $review) {
            // vendor rating
            if ($review->vendor_id) {
                $review->vendor?->recalculateRating();
            }

            // product rating — only approved reviews count
            if ($review->product_id) {
                // Reload from DB to get fresh state
                $product = Product::find($review->product_id);
                $product?->recalculateRating();
            }
        };

        static::saved($recalc);
        static::deleted($recalc);
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** True if the customer can still edit this review */
    public function isEditable(): bool
    {
        return $this->edit_locked_at === null || now()->lt($this->edit_locked_at);
    }

    /** True if this review has been moderated/approved */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
