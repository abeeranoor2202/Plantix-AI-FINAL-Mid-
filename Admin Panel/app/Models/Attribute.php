<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';
    public const TYPE_SELECT = 'select';
    public const TYPE_MULTI_SELECT = 'multi-select';

    protected $fillable = [
        'name',
        'title',
        'type',
        'unit',
        'firebase_doc_id',
    ];

    protected $attributes = [
        'type' => self::TYPE_TEXT,
    ];

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

    public function getDisplayNameAttribute(): string
    {
        return (string) ($this->name ?: $this->title ?: 'Attribute');
    }
}
