<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExpertProfile — Extended profile fields only.
 *
 * NOTE: approval_status here is SECONDARY — it mirrors Expert.status for
 * historical reasons and admin notes. The Expert model is the authoritative
 * source of truth for lifecycle state.
 */
class ExpertProfile extends Model
{
    protected $fillable = [
        'expert_id',
        'agency_name',
        'specialization',
        'experience_years',
        'certifications',
        'availability_schedule',
        'website',
        'linkedin',
        'contact_phone',
        'city',
        'country',
        'account_type',
        'approval_status',
        'admin_notes',
        'approved_at',
    ];

    protected $casts = [
        'experience_years'      => 'integer',
        'approved_at'           => 'datetime',
        'availability_schedule' => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isSuspended(): bool
    {
        return $this->approval_status === 'suspended';
    }

    public function isUnderReview(): bool
    {
        return $this->approval_status === 'under_review';
    }

    public function isInactive(): bool
    {
        return $this->approval_status === 'inactive';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->approval_status) {
            'approved'     => 'success',
            'pending'      => 'warning',
            'under_review' => 'info',
            'rejected'     => 'danger',
            'suspended'    => 'secondary',
            'inactive'     => 'dark',
            default        => 'light',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('approval_status', 'under_review');
    }
}
