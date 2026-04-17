<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Notifications\ReturnLifecycleNotification;
use App\Services\Shared\ReturnRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * VendorReturnController
 *
 * Allows vendors to manage return requests for their own orders,
 * including notes and vendor-side approve/reject decisions.
 */
class VendorReturnController extends Controller
{
    public function __construct(
        private readonly ReturnRefundService $service,
    ) {}

    private function vendorId(): int
    {
        return auth('vendor')->user()->vendor->id;
    }

    /**
     * List return requests for orders belonging to this vendor.
     * Route: GET /vendor/returns
     */
    public function index(Request $request): View
    {
        $query = ReturnRequest::with(['user', 'order', 'reason', 'items.product'])
            ->whereHas('order', fn ($q) => $q->where('vendor_id', $this->vendorId()))
            ->latest();

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));

            $query->where(function ($inner) use ($term) {
                $inner->whereHas('order', function ($orderQuery) use ($term) {
                    $orderQuery->where('order_number', 'like', "%{$term}%");
                })->orWhereHas('user', function ($userQuery) use ($term) {
                    $userQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                })->orWhereHas('reason', function ($reasonQuery) use ($term) {
                    $reasonQuery->where('reason', 'like', "%{$term}%");
                })->orWhere('notes', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === ReturnRequest::STATUS_COMPLETED) {
                $query->whereIn('status', [ReturnRequest::STATUS_COMPLETED, 'refunded']);
            } else {
                $query->where('status', $request->status);
            }
        }

        $returns  = $query->paginate(20)->withQueryString();
        $statuses = [
            ReturnRequest::STATUS_PENDING,
            ReturnRequest::STATUS_APPROVED,
            ReturnRequest::STATUS_REJECTED,
            ReturnRequest::STATUS_COMPLETED,
        ];

        return view('vendor.returns.index', [
            'returns' => $returns,
            'statuses' => $statuses,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show a single return request.
     * Route: GET /vendor/returns/{id}
     */
    public function show(int $id): View
    {
        $return = ReturnRequest::with(['user', 'order.items.product', 'reason', 'refund', 'items.product'])
            ->whereHas('order', fn ($q) => $q->where('vendor_id', $this->vendorId()))
            ->findOrFail($id);

        return view('vendor.returns.show', compact('return'));
    }

    /**
     * Vendor adds a note to a pending return request.
     * Route: POST /vendor/returns/{id}/note
     */
    public function addNote(Request $request, int $id): RedirectResponse
    {
        $request->validate(['notes' => 'required|string|max:1000']);

        try {
            DB::transaction(function () use ($id, $request): void {
                $return = ReturnRequest::whereHas('order', fn ($q) => $q->where('vendor_id', $this->vendorId()))
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $return->isPending()) {
                    throw new \DomainException('Notes can only be added to pending return requests.');
                }

                $return->update([
                    'vendor_notes' => $request->notes,
                ]);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Note added. Admin will review the return request.');
    }

    /**
     * Vendor approves a pending return request.
     * Route: POST /vendor/returns/{id}/approve
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'resolution_type' => 'required|in:refund,replace,store_credit',
            'response_notes' => 'nullable|string|max:1000',
        ]);

        $vendor = auth('vendor')->user();

        $return = ReturnRequest::whereHas('order', fn ($q) => $q->where('vendor_id', $this->vendorId()))
            ->where('status', ReturnRequest::STATUS_PENDING)
            ->findOrFail($id);

        $resolution = (string) $request->input('resolution_type');
        $notes = trim((string) $request->input('response_notes', ''));

        try {
            DB::transaction(function () use ($return, $resolution, $notes): void {
                $lockedReturn = ReturnRequest::query()
                    ->whereKey($return->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $lockedReturn->isPending()) {
                    throw new \DomainException('This return request is no longer pending.');
                }

                $this->service->approve($lockedReturn, $notes !== '' ? $notes : null);

                $payload = [
                    'resolution_type' => $resolution,
                    'vendor_response_notes' => $notes !== '' ? $notes : null,
                    'vendor_responded_at' => now(),
                    'rejection_reason' => null,
                ];

                if ($resolution !== ReturnRequest::RESOLUTION_REFUND) {
                    $payload['status'] = ReturnRequest::STATUS_COMPLETED;
                    $payload['completed_at'] = now();
                }

                $lockedReturn->update($payload);

                if ($resolution !== ReturnRequest::RESOLUTION_REFUND && $lockedReturn->user) {
                    $message = $resolution === ReturnRequest::RESOLUTION_REPLACE
                        ? 'Your return has been approved for replacement.'
                        : 'Your return has been approved as store credit.';
                    $lockedReturn->user->notify(new ReturnLifecycleNotification($lockedReturn->fresh(), 'completed', $message));
                }
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $statusMessage = $resolution === ReturnRequest::RESOLUTION_REFUND
            ? 'Return approved. Refund processing can now be completed by admin.'
            : 'Return approved and marked completed.';

        return back()->with('success', $statusMessage);
    }

    /**
     * Vendor rejects a pending return request.
     * Route: POST /vendor/returns/{id}/reject
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'response_notes' => 'nullable|string|max:1000',
        ]);

        $reason = trim((string) $request->input('rejection_reason'));
        $notes = trim((string) $request->input('response_notes', ''));

        $return = ReturnRequest::whereHas('order', fn ($q) => $q->where('vendor_id', $this->vendorId()))
            ->where('status', ReturnRequest::STATUS_PENDING)
            ->findOrFail($id);

        try {
            DB::transaction(function () use ($return, $reason, $notes): void {
                $lockedReturn = ReturnRequest::query()
                    ->whereKey($return->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $lockedReturn->isPending()) {
                    throw new \DomainException('This return request is no longer pending.');
                }

                $composed = $notes !== ''
                    ? $reason . "\n\nAdditional Note: " . $notes
                    : $reason;

                $this->service->reject($lockedReturn, $composed);

                $lockedReturn->update([
                    'rejection_reason' => $reason,
                    'vendor_response_notes' => $notes !== '' ? $notes : null,
                    'vendor_responded_at' => now(),
                    'resolution_type' => null,
                ]);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Return rejected and customer has been notified.');
    }
}
