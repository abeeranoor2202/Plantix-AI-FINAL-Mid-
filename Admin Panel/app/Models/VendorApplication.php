<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorApplication extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING      = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED     = 'approved';
    public const STATUS_REJECTED     = 'rejected';
    public const STATUS_SUSPENDED    = 'suspended';

    protected $fillable = [
        'user_id', 'vendor_id', 'application_number', 'business_name', 'owner_name',
        'email', 'phone', 'cnic_tax_id', 'business_category', 'business_address',
        'city', 'region', 'bank_name', 'bank_account_name', 'bank_account_number', 'iban',
        'cnic_document', 'business_license_document', 'tax_certificate_document',
        'status', 'reviewed_by', 'submitted_at', 'reviewed_at', 'approved_at', 'rejected_at',
        'suspended_at', 'review_notes', 'metadata',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'suspended_at' => 'datetime',
        'metadata'     => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
