<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\ReturnReason;
use App\Models\ReturnRequest;
use App\Services\Shared\ReturnRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReturnController extends Controller
{
    public function __construct(
        private readonly ReturnRefundService $service,
    ) {}

    public function index(Request $request): View
    {
        $query = ReturnRequest::with(['user', 'order', 'reason'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns  = $query->paginate(20)->withQueryString();
        $statuses = ['pending', 'approved', 'rejected', 'refund_processing', 'completed'];

        return view('admin.returns.index', compact('returns', 'statuses'));
    }

    public function show(int $id): View
    {
        $return  = ReturnRequest::with(['user', 'order.items.product', 'reason', 'refund'])->findOrFail($id);
        $reasons = ReturnReason::active()->get();
        $canRefund = $this->service->orderIsRefundable($return->order);

        return view('admin.returns.show', compact('return', 'reasons', 'canRefund'));
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $request->validate(['admin_notes' => 'nullable|string|max:1000']);

        $return = ReturnRequest::findOrFail($id);
        $this->service->forceApprove($return, $request->admin_notes, auth('admin')->user());

        return back()->with('success', 'Return request approved.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate(['admin_notes' => 'required|string|max:1000']);

        $return = ReturnRequest::findOrFail($id);
        $this->service->forceReject($return, $request->admin_notes, auth('admin')->user());

        return back()->with('success', 'Return request rejected.');
    }

    public function processRefund(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'amount'          => 'nullable|numeric|min:0.01',
            'method'          => 'required|in:wallet,original,manual,original_payment,bank_transfer',
            'transaction_ref' => 'nullable|string|max:191',
            'notes'           => 'nullable|string|max:500',
        ]);

        $return = ReturnRequest::findOrFail($id);
        /** @var \App\Models\User $admin */
        $admin = auth('admin')->user();

        abort_unless($this->service->orderIsRefundable($return->order), 403, 'Refund not allowed for this product');

        $this->service->processRefund($return, $request->validated(), $admin);

        return back()->with('success', 'Refund processed successfully.');
    }

    // ── Return Reasons CRUD ────────────────────────────────────────────────────

    public function reasons(): View
    {
        $reasons = ReturnReason::latest()->get();
        return view('admin.returns.reasons', compact('reasons'));
    }

    public function storeReason(Request $request): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        ReturnReason::create($request->only('reason'));
        return back()->with('success', 'Reason added.');
    }

    public function destroyReason(int $id): RedirectResponse
    {
        ReturnReason::findOrFail($id)->delete();
        return back()->with('success', 'Reason deleted.');
    }
}

