<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ReturnReason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * VendorReturnReasonController
 *
 * Allows vendors to manage the configurable list of return reasons
 * that customers see when submitting a return request.
 */
class VendorReturnReasonController extends Controller
{
    private function vendorId(): int
    {
        return auth('vendor')->user()->vendor->id;
    }

    /**
     * List all return reasons.
     * Route: GET /vendor/return-reasons
     */
    public function index(Request $request): View
    {
        $query = ReturnReason::forVendorOrGlobal($this->vendorId())
            ->withCount('returns')
            ->orderByDesc('is_active')
            ->orderBy('reason');

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($inner) use ($term) {
                $inner->where('reason', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $reasons = $query->paginate(20)->withQueryString();

        return view('vendor.returns.reasons', [
            'reasons' => $reasons,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Create a new return reason.
     * Route: POST /vendor/return-reasons
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reason'    => 'required|string|max:255|unique:return_reasons,reason,NULL,id,vendor_id,' . $this->vendorId(),
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        ReturnReason::create([
            'reason'    => $data['reason'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'vendor_id' => $this->vendorId(),
        ]);

        return back()->with('success', 'Return reason added successfully.');
    }

    /**
     * Update an existing return reason.
     * Route: PATCH /vendor/return-reasons/{id}
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $reason = ReturnReason::where('vendor_id', $this->vendorId())->findOrFail($id);

        $data = $request->validate([
            'reason' => 'required|string|max:255|unique:return_reasons,reason,' . $id . ',id,vendor_id,' . $this->vendorId(),
            'description' => 'nullable|string|max:1000',
        ]);

        $reason->update([
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('success', 'Return reason updated.');
    }

    /**
     * Toggle active/inactive state.
     * Route: PATCH /vendor/return-reasons/{id}/toggle
     */
    public function toggle(int $id): RedirectResponse
    {
        $reason = ReturnReason::where('vendor_id', $this->vendorId())->findOrFail($id);
        $reason->update(['is_active' => ! $reason->is_active]);

        $state = $reason->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Return reason {$state}.");
    }

    /**
     * Delete a return reason.
     * Route: DELETE /vendor/return-reasons/{id}
     */
    public function destroy(int $id): RedirectResponse
    {
        $reason = ReturnReason::where('vendor_id', $this->vendorId())->findOrFail($id);

        // Soft-protect: don't delete if already used
        if ($reason->returns()->exists()) {
            $reason->update(['is_active' => false]);
            return back()->with('success', 'Reason is in use — it has been deactivated instead of deleted.');
        }

        $reason->delete();

        return back()->with('success', 'Return reason deleted.');
    }
}
