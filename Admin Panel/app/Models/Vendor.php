<?php

namespace App\Models;

use App\Events\Vendor\VendorStatusChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vendor extends Model
{
    protected $fillable = [
        'author_id', 'zone_id', 'category_id', 'title', 'description',
        'address', 'latitude', 'longitude', 'phone', 'image', 'cover_photo',
        'rating', 'review_count', 'is_active', 'is_approved',
        'open_time', 'close_time', 'delivery_fee', 'min_order_amount',
        'preparation_time', 'commission_rate', 'stripe_account_id',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_approved'  => 'boolean',
        'rating'       => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'commission_rate' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::updated(function (Vendor $vendor) {
            if ($vendor->wasChanged(['is_approved', 'is_active'])) {
                $status = match (true) {
                    $vendor->is_approved && $vendor->is_active              => 'approved',
                    $vendor->is_approved && ! $vendor->is_active            => 'suspended',
                    ! $vendor->is_approved && $vendor->getOriginal('is_approved') => 'rejected',
                    default                                                 => 'pending',
                };
                VendorStatusChanged::dispatch($vendor, $status);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function bookedTables(): HasMany
    {
        return $this->hasMany(BookedTable::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function documentVerifications(): HasMany
    {
        return $this->hasMany(DocumentVerification::class);
    }

    public function withdrawMethods(): HasMany
    {
        return $this->hasMany(VendorWithdrawMethod::class);
    }

    public function storeFilters(): BelongsToMany
    {
        return $this->belongsToMany(StoreFilter::class, 'vendor_store_filters');
    }

    public function favouritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favourite_vendors');
    }

    // -------------------------------------------------------------------------
    // Computed
    // -------------------------------------------------------------------------

    public function recalculateRating(): void
    {
        $avg   = $this->reviews()->where('is_active', true)->avg('rating') ?? 0;
        $count = $this->reviews()->where('is_active', true)->count();
        $this->update(['rating' => round($avg, 2), 'review_count' => $count]);
    }
}
