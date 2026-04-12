<?php

namespace App\Services\Vendor;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorApplicationService
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return VendorApplication::query()
            ->with(['user', 'vendor', 'reviewer'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $term = '%' . $search . '%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('application_number', 'like', $term)
                        ->orWhere('business_name', 'like', $term)
                        ->orWhere('owner_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function submit(User $user, array $data): VendorApplication
    {
        return DB::transaction(function () use ($user, $data) {
            $vendor = Vendor::firstOrCreate(
                ['author_id' => $user->id],
                [
                    'title'       => $data['business_name'],
                    'owner_name'  => $data['owner_name'],
                    'business_email' => $data['email'],
                    'business_phone' => $data['phone'],
                    'status'      => VendorApplication::STATUS_PENDING,
                    'is_active'   => false,
                    'is_approved' => false,
                ]
            );

            $application = VendorApplication::create([
                'user_id'     => $user->id,
                'vendor_id'   => $vendor->id,
                'application_number' => 'VAP-' . strtoupper(Str::random(10)),
                'business_name' => $data['business_name'],
                'owner_name'  => $data['owner_name'],
                'email'       => $data['email'],
                'phone'       => $data['phone'],
                'cnic_tax_id' => $data['cnic_tax_id'] ?? null,
                'business_category' => $data['business_category'] ?? null,
                'business_address'  => $data['business_address'] ?? null,
                'city'        => $data['city'] ?? null,
                'region'      => $data['region'] ?? null,
                'bank_name'   => $data['bank_name'] ?? null,
                'bank_account_name' => $data['bank_account_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'iban'        => $data['iban'] ?? null,
                'cnic_document' => Arr::get($data, 'cnic_document'),
                'business_license_document' => Arr::get($data, 'business_license_document'),
                'tax_certificate_document' => Arr::get($data, 'tax_certificate_document'),
                'status'      => VendorApplication::STATUS_PENDING,
                'submitted_at'=> now(),
                'metadata'    => $data['metadata'] ?? null,
            ]);

            $this->auditLog->record(
                actorId: $user->id,
                auditable: $application,
                action: 'vendor_application.submitted',
                afterState: $application->toArray(),
            );

            return $application;
        });
    }

    public function markUnderReview(VendorApplication $application, ?int $adminId = null, ?array $meta = null): VendorApplication
    {
        $before = $application->toArray();

        $application->update([
            'status'      => VendorApplication::STATUS_UNDER_REVIEW,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);

        $this->auditLog->record($adminId, $application, 'vendor_application.under_review', $before, $application->fresh()->toArray(), $meta ?? []);

        return $application->fresh();
    }

    public function approve(VendorApplication $application, ?int $adminId = null, ?array $meta = null): VendorApplication
    {
        $before = $application->toArray();

        $application->update([
            'status'      => VendorApplication::STATUS_APPROVED,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'approved_at' => now(),
            'rejected_at' => null,
            'suspended_at'=> null,
        ]);

        if ($application->vendor) {
            $application->vendor->update([
                'is_approved' => true,
                'is_active'   => true,
                'status'      => VendorApplication::STATUS_APPROVED,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'approved_at' => now(),
            ]);
        }

        $this->auditLog->record($adminId, $application, 'vendor_application.approved', $before, $application->fresh()->toArray(), $meta ?? []);

        return $application->fresh();
    }

    public function reject(VendorApplication $application, ?int $adminId = null, ?string $reason = null, ?array $meta = null): VendorApplication
    {
        $before = $application->toArray();

        $application->update([
            'status'      => VendorApplication::STATUS_REJECTED,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'rejected_at' => now(),
            'review_notes'=> $reason,
        ]);

        if ($application->vendor) {
            $application->vendor->update([
                'is_approved' => false,
                'is_active'   => false,
                'status'      => VendorApplication::STATUS_REJECTED,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'rejected_at' => now(),
            ]);
        }

        $this->auditLog->record($adminId, $application, 'vendor_application.rejected', $before, $application->fresh()->toArray(), $meta ?? ['reason' => $reason]);

        return $application->fresh();
    }

    public function suspend(VendorApplication $application, ?int $adminId = null, ?string $reason = null, ?array $meta = null): VendorApplication
    {
        $before = $application->toArray();

        $application->update([
            'status'      => VendorApplication::STATUS_SUSPENDED,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'suspended_at'=> now(),
            'review_notes'=> $reason,
        ]);

        if ($application->vendor) {
            $application->vendor->update([
                'is_active'   => false,
                'status'      => VendorApplication::STATUS_SUSPENDED,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'suspended_at'=> now(),
            ]);
        }

        $this->auditLog->record($adminId, $application, 'vendor_application.suspended', $before, $application->fresh()->toArray(), $meta ?? ['reason' => $reason]);

        return $application->fresh();
    }
}
