<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\ExpertNotificationLog;
use App\Models\PayoutRequest;
use App\Services\Shared\MarketplacePayoutService;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPayoutRequestController extends Controller
{
    public function __construct(
        private readonly MarketplacePayoutService  $payouts,
        private readonly NotificationCenterService $notifications,
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

        $requests     = $query->paginate(20)->withQueryString();
        $pendingCount = PayoutRequest::where('status', PayoutRequest::STATUS_PENDING)->count();

        return view('admin.payout_requests.expert_requests', compact('requests', 'pendingCount'));
    }

    /**
     * Approve a payout request and trigger the Stripe transfer.
     * Notification is sent ONLY to the specific expert who made the request.
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

        $expert = $payoutRequest->expert;

        if (! $expert instanceof Expert) {
            return back()->withErrors(['error' => 'Expert profile not found for this request.']);
        }

        try {
            // Trigger the Stripe transfer — scoped to this expert only
            $payout = $this->payouts->settleAppointment(
                $payoutRequest->appointment->load(['expert.user'])
            );

            $newStatus = $payout->status === 'paid'
                ? PayoutRequest::STATUS_PAID
                : PayoutRequest::STATUS_APPROVED;

            $payoutRequest->update([
                'status'      => $newStatus,
                'payout_id'   => $payout->id,
                'admin_note'  => $request->input('admin_note'),
                'reviewed_by' => auth('admin')->id(),
                'reviewed_at' => now(),
            ]);

            // Send in-app notification ONLY to this specific expert
            $netAmount  = number_format((float) $payout->net_amount, 2);
            $grossAmount = number_format((float) $payout->amount, 2);

            $this->notifications->notifyExpert(
                expert:    $expert,
                type:      'payout.processed',
                title:     'Payment Approved — PKR ' . $netAmount,
                message:   "Your payout request for appointment #{$payoutRequest->appointment_id} has been approved. "
                         . "PKR {$grossAmount} gross, PKR {$netAmount} net (after platform fee) has been transferred to your Stripe account.",
                data:      [
                    'payout_request_id' => $payoutRequest->id,
                    'payout_id'         => $payout->id,
                    'appointment_id'    => $payoutRequest->appointment_id,
                    'gross_amount'      => $payout->amount,
                    'net_amount'        => $payout->net_amount,
                    'action_url'        => route('expert.payouts.index'),
                ],
                relatedId:  $payoutRequest->id,
                actionUrl:  route('expert.payouts.index'),
            );

        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Payout failed: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Payout approved and transfer initiated.');
    }

    /**
     * Reject a payout request.
     * Notification is sent ONLY to the specific expert who made the request.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $payoutRequest = PayoutRequest::with('expert')->findOrFail($id);

        if (! $payoutRequest->isPending()) {
            return back()->withErrors(['error' => 'This request has already been reviewed.']);
        }

        $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $expert = $payoutRequest->expert;

        $payoutRequest->update([
            'status'      => PayoutRequest::STATUS_REJECTED,
            'admin_note'  => $request->input('admin_note'),
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
        ]);

        // Send in-app notification ONLY to this specific expert
        if ($expert instanceof Expert) {
            $this->notifications->notifyExpert(
                expert:    $expert,
                type:      'payout.rejected',
                title:     'Payment Request Rejected',
                message:   "Your payout request for appointment #{$payoutRequest->appointment_id} was not approved. "
                         . 'Reason: ' . $request->input('admin_note'),
                data:      [
                    'payout_request_id' => $payoutRequest->id,
                    'appointment_id'    => $payoutRequest->appointment_id,
                    'admin_note'        => $request->input('admin_note'),
                    'action_url'        => route('expert.payouts.index'),
                ],
                relatedId:  $payoutRequest->id,
                actionUrl:  route('expert.payouts.index'),
            );
        }

        return back()->with('success', 'Payout request rejected.');
    }
}
