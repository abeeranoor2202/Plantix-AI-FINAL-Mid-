<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Services\Shared\ReturnRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $query = ReturnRequest::with(['user', 'order', 'reason'])
            ->whereHas('order', fn ($q) => $q->where('vendor_id', $this->vendorId()))
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns  = $query->paginate(20)->withQueryString();
        $statuses = ['pending', 'approved', 'rejected', 'refunded'];

        return view('vendor.returns.index', compact('returns', 'statuses'));
    }

    /**
     * Show a single return request.
     * Route: GET /vendor/returns/{id}
     */
    public function show(int $id): View
    {
        $return = ReturnRequest::with(['user', 'order.items.product', 'reason', 'refund'])
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

        $return = ReturnRequest::whereHas('order', fn ($q) => $q->where('vendor_id', $this->vendorId()))
                               ->where('status', 'pending')
                               ->findOrFail($id);

        $return->update([
            'vendor_notes' => $request->notes,
        ]);

        return back()->with('success', 'Note added. Admin will review the return request.');
    }

    /**
     * Vendor approves a pending return request.
     * Route: POST /vendor/returns/{id}/approve
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $request->validate(['admin_notes' => 'nullable|string|max:1000']);

        $return = ReturnRequest::whereHas('order', fn ($q) => $q->where('vendor_id', $this->vendorId()))
                               ->where('status', 'pending')
                               ->findOrFail($id);

        $this->service->approve($return, $request->admin_notes);

        return back()->with('success', 'Return request approved. Stock has been restored.');
    }

    /**
     * Vendor rejects a pending return request.
     * Route: POST /vendor/returns/{id}/reject
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate(['admin_notes' => 'required|string|max:1000']);

        $return = ReturnRequest::whereHas('order', fn ($q) => $q->where('vendor_id', $this->vendorId()))
                               ->where('status', 'pending')
                               ->findOrFail($id);

        $this->service->reject($return, $request->admin_notes);

        return back()->with('success', 'Return request has been rejected.');
    }
}
