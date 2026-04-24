<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Services\Shared\MarketplacePayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPayoutRequestController extends Controller
{
    public function __construct(
        private readonly MarketplacePayoutService $payouts,
    ) {}

    public function index(Request $request): View
    {
        $query = PayoutRequest::with(['expert.user', 'appointment.user', 'reviewer'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->whereHas('expert.user', fn ($q) => $q->where('name', 'like', $term));
        }

        $requests = $query->paginate(20)->withQueryString();
        $pendingCount = PayoutRequest::where('status', PayoutRequest::STATUS_PENDING)->count();

        return view('admin.payout_requests.expert_requests', compact('requests', 'pendingCount'));
    }

    /**
     * Approve a payout request and trigger the Stripe transfer.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $payoutRequest = PayoutRequest::with(['appointment.expert.user', 'expert'])->findOrFail($id);

        if (! $payoutRequest->isPending()) {
            return back()->withErrors(['error' => 'This request has already been reviewed.']);
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        try {
            // Trigger the actual Stripe transfer via the payout service
            $payout = $this->payouts->settleAppointment(
                $payoutRequest->appointment->load(['expert.user'])
            );

            $payoutRequest->update([
                'status'      => $payout->status === 'paid' ? PayoutRequest::STATUS_PAID : PayoutRequest::STATUS_APPROVED,
                'payout_id'   => $payout->id,
                'admin_note'  => $request->input('admin_note'),
                'reviewed_by' => auth('admin')->id(),
                'reviewed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Payout failed: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Payout approved and transfer initiated.');
    }

    /**
     * Reject a payout request.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $payoutRequest = PayoutRequest::findOrFail($id);

        if (! $payoutRequest->isPending()) {
            return back()->withErrors(['error' => 'This request has already been reviewed.']);
        }

        $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $payoutRequest->update([
            'status'      => PayoutRequest::STATUS_REJECTED,
            'admin_note'  => $request->input('admin_note'),
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Payout request rejected.');
    }
}
