<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentFeedback extends Model
{
    protected $table = 'appointment_feedback';

    protected $fillable = [
        'appointment_id',
        'user_id',
        'expert_id',
        'status',
        'rating',
        'review',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
