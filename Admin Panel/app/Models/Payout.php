<?php

namespace App\Models;

use App\Events\Expert\ExpertPayoutStatusChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Payout $payout): void {
            if ($payout->expert_id) {
                ExpertPayoutStatusChanged::dispatch($payout, null, $payout->status);
            }
        });

        static::updated(function (Payout $payout): void {
            if ($payout->expert_id && $payout->wasChanged('status')) {
                ExpertPayoutStatusChanged::dispatch(
                    $payout,
                    $payout->getOriginal('status'),
                    $payout->status
                );
            }
        });
    }

    protected $fillable = [
        'vendor_id',
        'expert_id',
        'user_id',
        'payment_id',
        'payment_type',
        'amount',
        'commission',
        'net_amount',
        'status',
        'method',
        'stripe_transfer_id',
        'stripe_payout_id',
        'metadata',
        'paid_at',
        'failed_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'commission'  => 'decimal:2',
        'net_amount'  => 'decimal:2',
        'metadata'    => 'array',
        'paid_at'     => 'datetime',
        'failed_at'   => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}