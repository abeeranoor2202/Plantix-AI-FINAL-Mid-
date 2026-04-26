<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\UpdateExpertProfileRequest;
use App\Services\Expert\ExpertProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * ExpertProfileController
 *
 * Handles expert profile viewing and updating (name, specializations,
 * agency info, certifications, availability, avatar, password).
 */
class ExpertProfileController extends Controller
{
    public function __construct(
        private readonly ExpertProfileService $service
    ) {}

    private function currentExpert(): \App\Models\Expert
    {
        return auth('expert')->user()->expert;
    }

    public function show(): View
    {
        $data = $this->service->getProfileData($this->currentExpert());

        return view('expert.profile.show', $data);
    }

    public function edit(): View
    {
        $data = $this->service->getProfileData($this->currentExpert());

        return view('expert.profile.edit', $data);
    }

    public function update(UpdateExpertProfileRequest $request): RedirectResponse
    {
        $expert = $this->currentExpert();

        $this->service->updateProfile($expert, $request->validated());

        // Handle avatar upload if provided
        if ($request->hasFile('avatar')) {
            $this->service->uploadAvatar($expert, $request->file('avatar'));
        }

        // Sync specializations if provided
        if ($request->has('specializations')) {
            $this->service->syncSpecializations($expert, $request->input('specializations', []));
        }

        // Handle password change if provided
        if ($request->filled('current_password') && $request->filled('new_password')) {
            $user = auth('expert')->user();

            if (! Hash::check($request->input('current_password'), $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'Current password is incorrect.'])
                    ->withInput();
            }

            $user->update(['password' => Hash::make($request->input('new_password'))]);
        }

        return redirect()->route('expert.profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}
