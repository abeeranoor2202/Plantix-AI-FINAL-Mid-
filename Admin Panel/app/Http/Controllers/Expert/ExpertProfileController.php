<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\UpdateExpertProfileRequest;
use App\Services\Expert\ExpertProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * ExpertProfileController
 *
 * Handles expert profile viewing and updating (name, specializations,
 * agency info, certifications, availability, avatar).
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
        if ($request->hasFile('profile_image')) {
            $this->service->uploadAvatar($expert, $request->file('profile_image'));
        }

        // Sync specializations if provided
        if ($request->has('specializations')) {
            $this->service->syncSpecializations($expert, $request->input('specializations', []));
        }

        return redirect()->route('expert.profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}
