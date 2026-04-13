<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'role';

    protected $fillable = ['name', 'role_name', 'slug', 'description', 'guard', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $role): void {
            if (! $role->role_name && $role->name) {
                $role->role_name = $role->name;
            }

            if (! $role->slug && $role->role_name) {
                $role->slug = \Illuminate\Support\Str::slug($role->role_name);
            }

            if (! $role->guard) {
                $role->guard = 'admin';
            }
        });
    }

    public function getNameAttribute(): ?string
    {
        return $this->role_name;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['role_name'] = $value;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function hasPermission(string $name): bool
    {
        return $this->permissions()->where('name', $name)->exists();
    }
}
