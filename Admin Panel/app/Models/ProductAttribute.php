<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',
        'value_type',
        // legacy fields kept for backward compatibility
        'name',
        'price',
        'type',
    ];

    protected $appends = ['display_value'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function getDisplayValueAttribute(): string
    {
        $raw = (string) ($this->value ?? '');
        if ($raw === '') {
            return '';
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return implode(', ', array_map('strval', $decoded));
        }

        if ($this->attribute && $this->attribute->unit) {
            return trim($raw . ' ' . $this->attribute->unit);
        }

        return $raw;
    }
}
