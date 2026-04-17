<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformActivity extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_user_id',
        'actor_role',
        'action',
        'entity_type',
        'entity_id',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
