<?php

namespace App\Models;

use App\Events\Appointment\AppointmentCreated;
use App\Events\Appointment\AppointmentStatusChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use SoftDeletes;

    // ── Status constants — must match DB ENUM from migration ──────────────────
    // Spec state machine:
    //   draft → pending_payment → payment_failed
    //                           → pending_expert_approval → confirmed → completed
    //                                                    → rejected
    //                              confirmed → cancelled / reschedule_requested
    //                              reschedule_requested → confirmed
    //   ANY  → cancelled (admin only)
    public const STATUS_DRAFT                   = 'draft';
    public const STATUS_PENDING_PAYMENT         = 'pending_payment';
    public const STATUS_PAYMENT_FAILED          = 'payment_failed';
    public const STATUS_PENDING_EXPERT_APPROVAL = 'pending_expert_approval';
    public const STATUS_CONFIRMED               = 'confirmed';
    public const STATUS_REJECTED                = 'rejected';
    public const STATUS_COMPLETED               = 'completed';
    public const STATUS_CANCELLED               = 'cancelled';
    public const STATUS_RESCHEDULE_REQUESTED    = 'reschedule_requested';

    public const STATUS_PENDING     = 'pending';
    public const STATUS_RESCHEDULED = 'rescheduled';

    /**
     * Allowed status → [next statuses] map (strict state machine).
     * Admin can always force-cancel; enforced by service, not this map.
     */
    public const TRANSITIONS = [
        self::STATUS_DRAFT                   => [self::STATUS_PENDING_PAYMENT],
        self::STATUS_PENDING_PAYMENT         => [self::STATUS_PENDING_EXPERT_APPROVAL, self::STATUS_PAYMENT_FAILED],
        self::STATUS_PAYMENT_FAILED          => [self::STATUS_PENDING_PAYMENT],
        self::STATUS_PENDING_EXPERT_APPROVAL => [self::STATUS_CONFIRMED, self::STATUS_REJECTED],
        self::STATUS_CONFIRMED               => [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_RESCHEDULE_REQUESTED],
        self::STATUS_RESCHEDULE_REQUESTED    => [self::STATUS_RESCHEDULED, self::STATUS_CONFIRMED],
        self::STATUS_RESCHEDULED             => [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_RESCHEDULE_REQUESTED],
        self::STATUS_REJECTED                => [],
        self::STATUS_COMPLETED               => [],
        self::STATUS_CANCELLED               => [],
        self::STATUS_PENDING                 => [self::STATUS_PENDING_PAYMENT, self::STATUS_CANCELLED],
    ];

    protected $fillable = [
        'user_id', 'expert_id', 'admin_id',
        'type',
        'scheduled_at', 'scheduled_date', 'start_time', 'end_time', 'duration_minutes',
        'status', 'notes', 'location', 'admin_notes', 'expert_response_notes', 'cancellation_reason',
        'fee', 'payment_status',
        'platform', 'venue_name', 'address_line1', 'address_line2', 'city', 'notifications_enabled',
        'customer_rating', 'customer_review', 'rated_at',
        'stripe_payment_intent_id', 'stripe_payment_status',
        'is_refunded', 'refunded_at', 'stripe_refund_id', 'refund_amount',
        'topic', 'meeting_link',
        'reschedule_requested_at', 'accepted_at', 'rejected_at',
        'cancelled_at', 'completed_at', 'reject_reason',
        'reminder_sent_at', 'payment_idempotency_key',
    ];

    protected $casts = [
        'scheduled_at'            => 'datetime',
        'scheduled_date'          => 'date',
        'reschedule_requested_at' => 'datetime',
        'accepted_at'             => 'datetime',
        'rejected_at'             => 'datetime',
        'cancelled_at'            => 'datetime',
        'completed_at'            => 'datetime',
        'refunded_at'             => 'datetime',
        'reminder_sent_at'        => 'datetime',
        'fee'                     => 'decimal:2',
        'refund_amount'           => 'decimal:2',
        'duration_minutes'        => 'integer',
        'is_refunded'             => 'boolean',
        'notifications_enabled'   => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (Appointment $apt) {
            AppointmentCreated::dispatch($apt);
        });

        static::updated(function (Appointment $apt) {
            if ($apt->wasChanged('status')) {
                AppointmentStatusChanged::dispatch(
                    $apt,
                    $apt->getOriginal('status'),
                    $apt->status,
                    $apt->cancellation_reason ?? $apt->reject_reason ?? null,
                );
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(AppointmentStatusHistory::class)->orderBy('changed_at', 'asc');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AppointmentLog::class)->orderBy('occurred_at', 'asc');
    }

    public function reschedules(): HasMany
    {
        return $this->hasMany(AppointmentReschedule::class)->orderBy('created_at', 'desc');
    }

    public function latestReschedule(): HasOne
    {
        return $this->hasOne(AppointmentReschedule::class)->latestOfMany();
    }

    public function slot(): HasOne
    {
        return $this->hasOne(AppointmentSlot::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePendingPayment($q)    { return $q->where('status', self::STATUS_PENDING_PAYMENT); }
    public function scopePendingApproval($q)   { return $q->where('status', self::STATUS_PENDING_EXPERT_APPROVAL); }
    public function scopeConfirmed($query)     { return $query->where('status', self::STATUS_CONFIRMED); }
    public function scopeRejected($query)      { return $query->where('status', self::STATUS_REJECTED); }
    public function scopeCompleted($query)     { return $query->where('status', self::STATUS_COMPLETED); }
    public function scopeCancelled($query)     { return $query->where('status', self::STATUS_CANCELLED); }
    public function scopeUpcoming($query)      { return $query->where('scheduled_at', '>=', now()); }
    public function scopeForExpert($q, int $id){ return $q->where('expert_id', $id); }
    public function scopePending($query)       { return $query->where('status', self::STATUS_PENDING); }
    public function scopeRescheduled($query)   { return $query->where('status', self::STATUS_RESCHEDULED); }

    // ── State machine ─────────────────────────────────────────────────────────

    /**
     * Assert a status transition is valid; throw DomainException if not.
     * Pass $isAdmin = true to allow force-cancel from any status.
     *
     * @throws \DomainException
     */
    public function assertCanTransitionTo(string $targetStatus, bool $isAdmin = false): void
    {
        if ($isAdmin && $targetStatus === self::STATUS_CANCELLED) {
            return; // admin can always cancel
        }
        $allowed = self::TRANSITIONS[$this->status] ?? [];
        if (! in_array($targetStatus, $allowed, true)) {
            throw new \DomainException(
                "Cannot transition appointment #{$this->id} from '{$this->status}' to '{$targetStatus}'."
            );
        }
    }

    public function canBeAccepted(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_EXPERT_APPROVAL,
            self::STATUS_PENDING,
        ]);
    }

    public function canBeRejected(): bool   { return $this->canBeAccepted(); }

    public function canBeRescheduled(): bool
    {
        return in_array($this->status, [
            self::STATUS_CONFIRMED,
        ]);
    }

    public function canBeCompleted(): bool
    {
        return in_array($this->status, [
            self::STATUS_CONFIRMED,
            self::STATUS_RESCHEDULED,
        ]);
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_PAYMENT_FAILED,
            self::STATUS_PENDING_EXPERT_APPROVAL,
            self::STATUS_CONFIRMED,
            self::STATUS_RESCHEDULE_REQUESTED,
        ]);
    }

    public function isPaid(): bool
    {
        return $this->stripe_payment_status === 'succeeded'
            && $this->payment_status === 'paid';
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_EXPERT_APPROVAL,
            self::STATUS_CONFIRMED,
            self::STATUS_RESCHEDULE_REQUESTED,
            self::STATUS_PENDING,
            self::STATUS_RESCHEDULED,
        ]);
    }

    // ── Presentation ──────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT                   => 'secondary',
            self::STATUS_PENDING_PAYMENT         => 'warning',
            self::STATUS_PAYMENT_FAILED          => 'danger',
            self::STATUS_PENDING_EXPERT_APPROVAL => 'primary',
            self::STATUS_CONFIRMED               => 'info',
            self::STATUS_REJECTED                => 'danger',
            self::STATUS_RESCHEDULE_REQUESTED    => 'secondary',
            self::STATUS_COMPLETED               => 'success',
            self::STATUS_CANCELLED               => 'dark',
            self::STATUS_PENDING                 => 'warning',
            self::STATUS_RESCHEDULED             => 'secondary',
            default                              => 'light',
        };
    }

    public function getDisplayStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_EXPERT_APPROVAL => 'Pending',
            self::STATUS_CONFIRMED               => 'Accepted',
            self::STATUS_REJECTED                => 'Rejected',
            self::STATUS_COMPLETED               => 'Completed',
            self::STATUS_CANCELLED               => 'Cancelled',
            self::STATUS_RESCHEDULE_REQUESTED    => 'Reschedule Requested',
            self::STATUS_RESCHEDULED             => 'Rescheduled',
            self::STATUS_PENDING_PAYMENT         => 'Pending Payment',
            self::STATUS_PAYMENT_FAILED          => 'Payment Failed',
            self::STATUS_DRAFT                   => 'Draft',
            default                              => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'physical' ? 'Physical' : 'Online';
    }

    public function isPhysical(): bool
    {
        return $this->type === 'physical';
    }

    public function isOnline(): bool
    {
        return $this->type === 'online';
    }
}
