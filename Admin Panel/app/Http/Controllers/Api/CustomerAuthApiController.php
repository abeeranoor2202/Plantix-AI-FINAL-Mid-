<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthApiController extends Controller
{
    // ── Register ──────────────────────────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:25',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'phone'    => $data['phone'] ?? null,
            'role'     => 'user',
            'active'   => true,
        ]);

        $token = $user->createToken('plantix-customer')->plainTextToken;

        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($user),
            'token'   => $token,
        ], 201);
    }

    // ── Login ─────────────────────────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        if (! in_array($user->role, ['user'])) {
            throw ValidationException::withMessages([
                'email' => ['This login is for customers only.'],
            ]);
        }

        if (! $user->active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended.'],
            ]);
        }

        // Revoke old tokens and issue a fresh one
        $user->tokens()->where('name', 'plantix-customer')->delete();
        $token = $user->createToken('plantix-customer')->plainTextToken;

        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($user),
            'token'   => $token,
        ]);
    }

    // ── Logout ────────────────────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    // ── Current user ──────────────────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($request->user()),
        ]);
    }

    // ── Update profile ────────────────────────────────────────────────────────
    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'sometimes|string|max:100',
            'phone' => 'sometimes|nullable|string|max:25',
        ]);

        $user = $request->user();
        $user->update($data);

        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($user->fresh()),
        ]);
    }

    // ── Change password ───────────────────────────────────────────────────────
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Revoke all tokens so stolen tokens cannot be reused after a password change
        $user->tokens()->delete();
        $newToken = $user->createToken('plantix-customer')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
            'token'   => $newToken,
        ]);
    }

    // ── Forgot password ───────────────────────────────────────────────────────
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $email = strtolower(trim($request->email));

        // If the user is inactive, silently skip sending without revealing account status
        $user = User::where('email', $email)->first();
        if (! $user || $user->active) {
            // Only send the link if user exists AND is active
            if ($user) {
                \Illuminate\Support\Facades\Password::broker()
                    ->sendResetLink(['email' => $email]);
            }
        }

        // Always return success to prevent email enumeration
        return response()->json([
            'success' => true,
            'message' => 'If that email is registered you will receive a reset link shortly.',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function userPayload(User $user): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'phone'    => $user->phone,
            'avatar'   => $user->profile_photo
                            ? asset('storage/' . $user->profile_photo)
                            : null,
            'role'     => $user->role,
            'active'   => $user->active,
        ];
    }
}
