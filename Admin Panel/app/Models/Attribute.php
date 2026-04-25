<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';
    public const TYPE_SELECT = 'select';
    public const TYPE_MULTI_SELECT = 'multi-select';
    public const TYPE_BOOLEAN = 'boolean';

    protected $fillable = [
        'vendor_id',
        'name',
        'title',
        'type',
        'unit',
        'firebase_doc_id',
    ];

    protected $attributes = [
        'type' => self::TYPE_TEXT,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** The vendor who created this attribute (null = admin-created / global). */
    public function createdByVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_attribute')
            ->withPivot(['is_required', 'sort_order'])
            ->withTimestamps();
    }

    public function productValues(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attributes')
            ->withPivot(['value', 'value_type', 'name', 'type', 'price'])
            ->withTimestamps();
    }

    public function getDisplayNameAttribute(): string
    {
        return (string) ($this->name ?: $this->title ?: 'Attribute');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Only admin-created (global) attributes. */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('vendor_id');
    }

    /** Only attributes created by a specific vendor. */
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
}
