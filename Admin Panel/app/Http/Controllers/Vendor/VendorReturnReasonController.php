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
    /**
     * List all return reasons.
     * Route: GET /vendor/return-reasons
     */
    public function index(): View
    {
        $reasons = ReturnReason::orderBy('is_active', 'desc')->orderBy('reason')->get();

        return view('vendor.returns.reasons', compact('reasons'));
    }

    /**
     * Create a new return reason.
     * Route: POST /vendor/return-reasons
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reason'    => 'required|string|max:255|unique:return_reasons,reason',
            'is_active' => 'sometimes|boolean',
        ]);

        ReturnReason::create([
            'reason'    => $data['reason'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Return reason added successfully.');
    }

    /**
     * Update an existing return reason.
     * Route: PATCH /vendor/return-reasons/{id}
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $reason = ReturnReason::findOrFail($id);

        $data = $request->validate([
            'reason' => 'required|string|max:255|unique:return_reasons,reason,' . $id,
        ]);

        $reason->update(['reason' => $data['reason']]);

        return back()->with('success', 'Return reason updated.');
    }

    /**
     * Toggle active/inactive state.
     * Route: PATCH /vendor/return-reasons/{id}/toggle
     */
    public function toggle(int $id): RedirectResponse
    {
        $reason = ReturnReason::findOrFail($id);
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
        $reason = ReturnReason::findOrFail($id);

        // Soft-protect: don't delete if already used
        if ($reason->returns()->exists()) {
            $reason->update(['is_active' => false]);
            return back()->with('success', 'Reason is in use — it has been deactivated instead of deleted.');
        }

        $reason->delete();

        return back()->with('success', 'Return reason deleted.');
    }
}
