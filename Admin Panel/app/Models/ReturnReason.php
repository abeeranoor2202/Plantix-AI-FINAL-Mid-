<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnReason extends Model
{
    protected $fillable = ['title', 'reason', 'description', 'is_active', 'vendor_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRequest::class, 'return_reason_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForVendorOrGlobal(Builder $query, ?int $vendorId)
    {
        return $query->where(function (Builder $inner) use ($vendorId) {
            $inner->whereNull('vendor_id');

            if ($vendorId) {
                $inner->orWhere('vendor_id', $vendorId);
            }
        });
    }

    public function getTitleAttribute($value): ?string
    {
        return $value ?: $this->attributes['reason'] ?? null;
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['reason'] ?? $this->attributes['title'] ?? 'Reason');
    }

    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $value;
        $this->attributes['reason'] = $value;
    }
}
