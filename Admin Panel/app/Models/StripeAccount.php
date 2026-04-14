<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'accountable_type',
        'accountable_id',
        'stripe_account_id',
        'onboarding_status',
        'charges_enabled',
        'payouts_enabled',
        'details_submitted',
        'country',
        'email',
        'metadata',
        'last_onboarded_at',
    ];

    protected $casts = [
        'charges_enabled'   => 'boolean',
        'payouts_enabled'   => 'boolean',
        'details_submitted' => 'boolean',
        'metadata'          => 'array',
        'last_onboarded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}