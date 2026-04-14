<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

class Permission extends Model
{
    protected $table = 'permissions';

    private static ?array $schemaColumns = null;

    protected $fillable = ['name', 'slug', 'group', 'display_name', 'module', 'action', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $permission): void {
            if (! $permission->slug && $permission->name) {
                $permission->slug = \Illuminate\Support\Str::slug($permission->name, '.');
            }

            if (self::hasColumn('module') && ! $permission->module && $permission->name) {
                $parts = explode('.', $permission->name);
                $permission->module = count($parts) > 2 ? $parts[1] : ($parts[0] ?? $permission->name);
            }

            if (self::hasColumn('action') && ! $permission->action && $permission->name) {
                $parts = explode('.', $permission->name);
                $permission->action = count($parts) > 1 ? $parts[count($parts) - 1] : 'manage';
            }

            if (! $permission->group && $permission->name) {
                $parts = explode('.', $permission->name);
                $permission->group = count($parts) > 1 ? $parts[0] : $permission->name;
            }

            if (! $permission->display_name && $permission->name) {
                $permission->display_name = str_replace('.', ' ', $permission->name);
            }

            if (self::hasColumn('description') && ! $permission->description && $permission->display_name) {
                $permission->description = $permission->display_name;
            }

            if (self::hasColumn('is_active') && $permission->is_active === null) {
                $permission->is_active = true;
            }
        });
    }

    private static function hasColumn(string $column): bool
    {
        if (self::$schemaColumns === null) {
            self::$schemaColumns = Schema::getColumnListing((new self())->getTable());
        }

        return in_array($column, self::$schemaColumns, true);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}
