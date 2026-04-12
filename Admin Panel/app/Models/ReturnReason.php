<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnReason extends Model
{
    protected $fillable = ['title', 'reason', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRequest::class, 'return_reason_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTitleAttribute($value): ?string
    {
        return $value ?: $this->attributes['reason'] ?? null;
    }

    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $value;
        $this->attributes['reason'] = $value;
    }
}
