<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorApplication;
use App\Services\Vendor\VendorApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorApplicationController extends Controller
{
    public function __construct(private readonly VendorApplicationService $applications)
    {
    }

    public function index(Request $request): View
    {
        $applications = $this->applications->paginate($request->only(['status', 'search']), 20);

        return view('admin.vendors.applications.index', compact('applications'));
    }

    public function show(VendorApplication $application): View
    {
        $application->load(['user', 'vendor', 'reviewer']);

        return view('admin.vendors.applications.show', compact('application'));
    }

    public function underReview(Request $request, VendorApplication $application): RedirectResponse
    {
        $this->applications->markUnderReview($application, auth('admin')->id(), ['source' => 'admin_panel']);

        return back()->with('success', 'Vendor application marked under review.');
    }

    public function approve(Request $request, VendorApplication $application): RedirectResponse
    {
        $this->applications->approve($application, auth('admin')->id(), ['source' => 'admin_panel']);

        return back()->with('success', 'Vendor application approved.');
    }

    public function reject(Request $request, VendorApplication $application): RedirectResponse
    {
        $reason = $request->input('reason');
        $this->applications->reject($application, auth('admin')->id(), $reason, ['source' => 'admin_panel']);

        return back()->with('success', 'Vendor application rejected.');
    }

    public function suspend(Request $request, VendorApplication $application): RedirectResponse
    {
        $reason = $request->input('reason');
        $this->applications->suspend($application, auth('admin')->id(), $reason, ['source' => 'admin_panel']);

        return back()->with('success', 'Vendor application suspended.');
    }
}
