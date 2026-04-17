<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'real_time_notifications';
    
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'role',
        'title',
        'message',
        'status',
        'action_url',
        'metadata',
        'dedup_key',
        'read',
        'read_at',
        'sent_at',
        'recipient_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read' => true,
            'read_at' => now(),
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeForUser($query, int $userId, string $role)
    {
        return $query->where('receiver_id', $userId)->where('role', $role);
    }
}
