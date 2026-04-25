<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'vendor_id',
        'name', 'description', 'image',
        'active', 'sort_order',
        'text_review_enabled', 'image_review_enabled',
    ];

    protected $casts = [
        'active' => 'boolean',
        'text_review_enabled' => 'boolean',
        'image_review_enabled' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** The vendor who created this category (null = admin-created / global). */
    public function createdByVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Only admin-created (global) categories. */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('vendor_id');
    }

    /** Only categories created by a specific vendor. */
    public function scopeByVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isOwnedByVendor(int $vendorId): bool
    {
        return (int) $this->vendor_id === $vendorId;
    }

    public function isGlobal(): bool
    {
        return is_null($this->vendor_id);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'category_attribute')
            ->withPivot(['is_required', 'sort_order'])
            ->withTimestamps()
            ->orderBy('category_attribute.sort_order');
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_category');
    }
}
