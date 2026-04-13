<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = ['name', 'slug', 'group', 'display_name'];

    protected static function booted(): void
    {
        static::saving(function (self $permission): void {
            if (! $permission->slug && $permission->name) {
                $permission->slug = \Illuminate\Support\Str::slug($permission->name, '.');
            }

            if (! $permission->group && $permission->name) {
                $parts = explode('.', $permission->name);
                $permission->group = count($parts) > 1 ? $parts[0] : $permission->name;
            }

            if (! $permission->display_name && $permission->name) {
                $permission->display_name = str_replace('.', ' ', $permission->name);
            }
        });
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}
