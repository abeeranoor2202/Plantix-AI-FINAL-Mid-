<?php

namespace App\Models;

use App\Events\Vendor\VendorStatusChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Vendor extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'author_id', 'owner_name', 'business_email', 'business_phone', 'tax_id',
        'zone_id', 'category_id', 'business_category', 'title', 'description',
        'address', 'city', 'region', 'latitude', 'longitude', 'phone',
        'bank_name', 'bank_account_name', 'bank_account_number', 'iban',
        'cnic_document', 'business_license_document', 'tax_certificate_document',
        'image', 'cover_photo', 'rating', 'review_count', 'is_active', 'is_approved',
        'reputation_score', 'reputation_level',
        'status', 'reviewed_by', 'submitted_at', 'reviewed_at', 'approved_at',
        'rejected_at', 'suspended_at',
        'open_time', 'close_time', 'delivery_fee', 'min_order_amount',
        'preparation_time', 'commission_rate', 'stripe_account_id',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_approved'  => 'boolean',
        'status'       => 'string',
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'suspended_at' => 'datetime',
        'rating'       => 'decimal:2',
        'reputation_score' => 'integer',
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

    public function orderDisputes(): HasMany
    {
        return $this->hasMany(OrderDispute::class);
    }

    public function applicableCoupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_vendor');
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

    public function applications(): HasMany
    {
        return $this->hasMany(VendorApplication::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(VendorEarning::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
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

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['title'] ?? '');
    }

    public function getBusinessNameAttribute(): string
    {
        return (string) ($this->attributes['title'] ?? '');
    }

    public function getDisplayNameAttribute(): string
    {
        return (string) (
            $this->attributes['name']
            ?? $this->attributes['title']
            ?? $this->attributes['business_name']
            ?? 'Unknown Vendor'
        );
    }

    public function getOwnerNameAttribute(): string
    {
        return (string) ($this->attributes['owner_name'] ?? $this->author?->name ?? '');
    }

    public function recalculateRating(): void
    {
        $avg   = $this->reviews()->where('is_active', true)->avg('rating') ?? 0;
        $count = $this->reviews()->where('is_active', true)->count();
        $this->update(['rating' => round($avg, 2), 'review_count' => $count]);
    }

    public function isPending(): bool
    {
        return ($this->status ?? self::STATUS_PENDING) === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return ($this->status ?? self::STATUS_PENDING) === self::STATUS_APPROVED && $this->is_active;
    }

    public function isRejected(): bool
    {
        return ($this->status ?? self::STATUS_PENDING) === self::STATUS_REJECTED;
    }

    public function isSuspended(): bool
    {
        return ($this->status ?? self::STATUS_PENDING) === self::STATUS_SUSPENDED || ! $this->is_active;
    }

    public function setLifecycleStatus(string $status): void
    {
        $this->status = $status;

        if ($status === self::STATUS_APPROVED) {
            $this->is_approved = true;
            $this->is_active = true;
        }

        if ($status === self::STATUS_REJECTED) {
            $this->is_approved = false;
            $this->is_active = false;
        }

        if ($status === self::STATUS_SUSPENDED) {
            $this->is_active = false;
        }
    }
}
