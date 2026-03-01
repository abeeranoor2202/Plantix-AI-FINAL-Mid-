<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExpertApplication
 *
 * Tracks a customer's request to become an expert.
 * Lifecycle: pending → under_review → approved | rejected
 *
 * When approved, an Expert record is created for the user by ExpertApplicationService.
 */
class ExpertApplication extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING      = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED     = 'approved';
    const STATUS_REJECTED     = 'rejected';

    protected $fillable = [
        'user_id',
        'full_name',
        'specialization',
        'experience_years',
        'qualifications',
        'bio',
        'certifications_path',
        'id_document_path',
        'contact_phone',
        'city',
        'country',
        'website',
        'linkedin',
        'account_type',
        'agency_name',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'experience_years' => 'integer',
        'reviewed_at'      => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool      { return $this->status === self::STATUS_PENDING; }
    public function isUnderReview(): bool  { return $this->status === self::STATUS_UNDER_REVIEW; }
    public function isApproved(): bool     { return $this->status === self::STATUS_APPROVED; }
    public function isRejected(): bool     { return $this->status === self::STATUS_REJECTED; }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED     => 'success',
            self::STATUS_PENDING      => 'warning',
            self::STATUS_UNDER_REVIEW => 'info',
            self::STATUS_REJECTED     => 'danger',
            default                   => 'light',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING      => 'Pending Review',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_APPROVED     => 'Approved',
            self::STATUS_REJECTED     => 'Rejected',
            default                   => ucfirst($this->status ?? 'Unknown'),
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($query)     { return $query->where('status', self::STATUS_PENDING); }
    public function scopeUnderReview($query) { return $query->where('status', self::STATUS_UNDER_REVIEW); }
    public function scopeApproved($query)    { return $query->where('status', self::STATUS_APPROVED); }
    public function scopeRejected($query)    { return $query->where('status', self::STATUS_REJECTED); }

    /**
     * Applications awaiting admin action (pending or under_review).
     */
    public function scopeNeedsReview($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_UNDER_REVIEW]);
    }
}
