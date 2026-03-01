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
        'price', 'discount_price', 'image', 'is_active', 'is_featured',
        'status', 'sort_order', 'stock_quantity', 'track_stock',
        'rating_avg', 'rating_count',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'is_featured'    => 'boolean',
        'track_stock'    => 'boolean',
        'price'          => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock_quantity' => 'integer',
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

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
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
        return $this->hasOne(ProductStock::class);
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
        return $query->where('status', self::STATUS_ACTIVE);
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
