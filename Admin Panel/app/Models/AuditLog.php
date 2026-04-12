<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'actor_id', 'auditable_type', 'auditable_id', 'action', 'before_state', 'after_state',
        'meta', 'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state'  => 'array',
        'meta'         => 'array',
        'created_at'   => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
