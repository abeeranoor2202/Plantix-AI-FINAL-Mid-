<?php

namespace App\Models;

use App\Events\Expert\ExpertStatusChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use InvalidArgumentException;

/**
 * Expert Model — single source of truth for lifecycle status.
 *
 * State machine (from → [allowed tos]):
 *   pending      → under_review
 *   under_review → approved | rejected
 *   approved     → suspended | inactive
 *   suspended    → approved
 *   inactive     → approved
 *   rejected     → (terminal; new application required)
 */
class Expert extends Model
{
    use HasFactory, SoftDeletes;

    // ── Status constants ─────────────────────────────────────────────────────

    const STATUS_PENDING      = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED     = 'approved';
    const STATUS_REJECTED     = 'rejected';
    const STATUS_SUSPENDED    = 'suspended';
    const STATUS_INACTIVE     = 'inactive';

    const TRANSITIONS = [
        self::STATUS_PENDING      => [self::STATUS_UNDER_REVIEW],
        self::STATUS_UNDER_REVIEW => [self::STATUS_APPROVED, self::STATUS_REJECTED],
        self::STATUS_APPROVED     => [self::STATUS_SUSPENDED, self::STATUS_INACTIVE],
        self::STATUS_SUSPENDED    => [self::STATUS_APPROVED],
        self::STATUS_INACTIVE     => [self::STATUS_APPROVED],
        self::STATUS_REJECTED     => [],
    ];

    protected $fillable = [
        'user_id',
        'status',
        'specialty',
        'bio',
        'profile_image',
        'is_available',
        'hourly_rate',
        'consultation_price',
        'consultation_duration_minutes',
        'rating_avg',
        'total_appointments',
        'total_completed',
        'total_cancelled',
        'stripe_account_id',
        'stripe_account_status',
        'verified_at',
        'suspended_at',
        'rejection_reason',
    ];

    protected $casts = [
        'is_available'                  => 'boolean',
        'hourly_rate'                   => 'decimal:2',
        'consultation_price'            => 'decimal:2',
        'rating_avg'                    => 'decimal:2',
        'total_appointments'            => 'integer',
        'total_completed'               => 'integer',
        'total_cancelled'               => 'integer',
        'consultation_duration_minutes' => 'integer',
        'stripe_account_status'         => 'string',
        'verified_at'                   => 'datetime',
        'suspended_at'                  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updated(function (Expert $expert) {
            if ($expert->wasChanged('status')) {
                ExpertStatusChanged::dispatch(
                    $expert,
                    $expert->status,
                    $expert->rejection_reason ?? null,
                );
            }
        });
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(ExpertProfile::class);
    }

    public function specializations(): HasMany
    {
        return $this->hasMany(ExpertSpecialization::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function forumResponses(): HasMany
    {
        return $this->hasMany(ForumReply::class)->where('is_expert_reply', true);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(ExpertNotificationLog::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ExpertLog::class)->latest();
    }

    public function availability(): HasMany
    {
        return $this->hasMany(ExpertAvailability::class);
    }

    public function stripeAccount(): HasOne
    {
        return $this->hasOne(StripeAccount::class, 'user_id', 'user_id');
    }

    public function unavailableDates(): HasMany
    {
        return $this->hasMany(ExpertUnavailableDate::class);
    }

    // ── State machine ────────────────────────────────────────────────────────

    /**
     * Validate and apply a status transition.
     * Does NOT persist — call save() or use ExpertApprovalService.
     *
     * @throws InvalidArgumentException
     */
    public function transitionTo(string $newStatus): self
    {
        $current = $this->status ?? self::STATUS_PENDING;
        $allowed = self::TRANSITIONS[$current] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw new InvalidArgumentException(
                "Invalid status transition from [{$current}] to [{$newStatus}]. "
                . 'Allowed: [' . implode(', ', $allowed) . ']'
            );
        }

        $this->status = $newStatus;

        if ($newStatus === self::STATUS_APPROVED) {
            $this->verified_at  = now();
            $this->suspended_at = null;
        }

        if ($newStatus === self::STATUS_SUSPENDED) {
            $this->suspended_at = now();
        }

        if ($newStatus === self::STATUS_INACTIVE) {
            $this->is_available = false;
        }

        return $this;
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status ?? self::STATUS_PENDING] ?? [], true);
    }

    // ── Status helpers ───────────────────────────────────────────────────────

    public function isPending(): bool      { return $this->status === self::STATUS_PENDING; }
    public function isUnderReview(): bool  { return $this->status === self::STATUS_UNDER_REVIEW; }
    public function isApproved(): bool     { return $this->status === self::STATUS_APPROVED; }
    public function isRejected(): bool     { return $this->status === self::STATUS_REJECTED; }
    public function isSuspended(): bool    { return $this->status === self::STATUS_SUSPENDED; }
    public function isInactive(): bool     { return $this->status === self::STATUS_INACTIVE; }

    public function canAcceptBookings(): bool
    {
        return $this->isApproved() && $this->is_available;
    }

    public function canPostOfficialAnswer(): bool
    {
        return $this->isApproved();
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name ?? 'Expert';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED     => 'success',
            self::STATUS_PENDING      => 'warning',
            self::STATUS_UNDER_REVIEW => 'info',
            self::STATUS_REJECTED     => 'danger',
            self::STATUS_SUSPENDED    => 'secondary',
            self::STATUS_INACTIVE     => 'dark',
            default                   => 'light',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING      => 'Pending',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_APPROVED     => 'Approved',
            self::STATUS_REJECTED     => 'Rejected',
            self::STATUS_SUSPENDED    => 'Suspended',
            self::STATUS_INACTIVE     => 'Inactive',
            default                   => ucfirst($this->status ?? 'Unknown'),
        };
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)      { return $query->where('status', self::STATUS_PENDING); }
    public function scopeUnderReview($query)  { return $query->where('status', self::STATUS_UNDER_REVIEW); }
    public function scopeApproved($query)     { return $query->where('status', self::STATUS_APPROVED); }
    public function scopeRejected($query)     { return $query->where('status', self::STATUS_REJECTED); }
    public function scopeSuspended($query)    { return $query->where('status', self::STATUS_SUSPENDED); }
    public function scopeInactive($query)     { return $query->where('status', self::STATUS_INACTIVE); }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeAgencies($query)
    {
        return $query->whereHas('profile', fn ($q) => $q->where('account_type', 'agency'));
    }
}
