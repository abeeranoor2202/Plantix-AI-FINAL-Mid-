<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DRAFT    = 'draft';

    protected $fillable = [
        'vendor_id', 'category_id', 'brand_id', 'name', 'sku', 'slug', 'description',
        'short_description', 'unit', 'low_stock_threshold',
        'price', 'discount_price', 'tax_rate', 'image', 'is_active', 'is_featured',
        'is_returnable', 'is_refundable', 'return_window_days',
        'status', 'sort_order', 'stock_quantity', 'track_stock',
        'rating_avg', 'rating_count',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'is_featured'    => 'boolean',
        'is_returnable'  => 'boolean',
        'is_refundable'  => 'boolean',
        'track_stock'    => 'boolean',
        'price'          => 'decimal:2',
        'discount_price' => 'decimal:2',
        'tax_rate'       => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'return_window_days' => 'integer',
        'rating_avg'     => 'decimal:2',
        'rating_count'   => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            // Sync status ↔ is_active on create
            if ($product->isDirty('status') && ! $product->isDirty('is_active')) {
                $product->is_active = ($product->status === self::STATUS_ACTIVE);
            } elseif ($product->isDirty('is_active') && ! $product->isDirty('status')) {
                $product->status = $product->is_active ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
            }
        });

        static::updating(function (self $product) {
            if ($product->isDirty('status') && ! $product->isDirty('is_active')) {
                $product->is_active = ($product->status === self::STATUS_ACTIVE);
            } elseif ($product->isDirty('is_active') && ! $product->isDirty('status')) {
                $product->status = $product->is_active ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->with('attribute.values');
    }

    public function attributeDefinitions(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot(['value', 'value_type', 'name', 'type', 'price'])
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function favouritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favourite_products');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_product');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /** Effective selling price — uses discount_price when set */
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->discount_price ?? $this->price);
    }

    /** True if product has an active discount */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->discount_price !== null && $this->discount_price < $this->price;
    }

    public function getAverageRatingAttribute(): float
    {
        return (float) $this->approvedReviews()->avg('rating') ?? 0.0;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_stock', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)->inStock();
    }

    /**
     * Recalculate and persist the denormalised rating_avg + rating_count.
     * Called by Review model after any approved review is saved/deleted.
     */
    public function recalculateRating(): void
    {
        $stats = $this->approvedReviews()
                      ->selectRaw('COUNT(*) as cnt, COALESCE(AVG(rating), 0) as avg_rating')
                      ->first();

        $this->updateQuietly([
            'rating_avg'   => round((float) ($stats->avg_rating ?? 0), 2),
            'rating_count' => (int) ($stats->cnt ?? 0),
        ]);
    }
}
