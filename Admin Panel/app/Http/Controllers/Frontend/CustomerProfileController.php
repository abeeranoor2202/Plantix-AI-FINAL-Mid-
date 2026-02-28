<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CustomerProfileController extends Controller
{
    public function show(): View
    {
        $user = auth('web')->user()->load('addresses', 'orders');
        return view('customer.account-profile', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('web')->user();
        $data = $request->validated();

        if ($request->hasFile('profile_photo')) {
            $file     = $request->file('profile_photo');
            $mimeMap  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $ext      = $mimeMap[$file->getMimeType()] ?? null;

            if (! $ext) {
                return back()->withErrors(['profile_photo' => 'Invalid image type. Only JPEG, PNG, WebP allowed.']);
            }

            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $data['profile_photo'] = $file->storeAs(
                'avatars',
                \Illuminate\Support\Str::uuid() . '.' . $ext,
                'public'
            );
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = auth('web')->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Revoke all Sanctum API tokens so stolen tokens cannot be reused
        $user->tokens()->delete();

        return back()->with('success', 'Password changed successfully.');
    }
}
