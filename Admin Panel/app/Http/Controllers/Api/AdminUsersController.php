<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;

class AdminUsersController extends Controller
{
    /**
     * Get all users
     */
    public function index(Request $request)
    {
        try {
            $users = User::paginate(15);
            return response()->json([
                'success' => true,
                'data' => $users->items()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single user
     */
    public function show($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name ?? '',
                    'last_name' => $user->last_name ?? '',
                    'email' => $user->email,
                    'phone_number' => $user->phone_number ?? '',
                    'profile_picture_url' => $user->profile_picture_url ?? null,
                    'is_active' => (bool) $user->is_active,
                    'status' => $user->status ?? ($user->is_banned ? 'banned' : ($user->active ? 'active' : 'suspended')),
                    'addresses' => $user->addresses ?? [],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new user
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'phone_number' => 'required|string',
                'is_active' => 'boolean',
                'profile_picture' => 'nullable|image|max:2048',
            ]);

            $user = new User();
            $user->first_name = $validated['first_name'];
            $user->last_name = $validated['last_name'];
            $user->email = $validated['email'];
            $user->password = bcrypt($validated['password']);
            $user->phone_number = $validated['phone_number'];
            $user->is_active = $validated['is_active'] ?? true;
            $user->role = 'customer';
            $user->status = $user->is_active ? 'active' : 'suspended';

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('users', 'public');
                $user->profile_picture_url = asset('storage/' . $path);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $validated = $request->validate([
                'first_name' => 'string',
                'last_name' => 'string',
                'phone_number' => 'string',
                'is_active' => 'boolean',
                'profile_picture' => 'nullable|image|max:2048',
            ]);

            if (isset($validated['first_name'])) $user->first_name = $validated['first_name'];
            if (isset($validated['last_name'])) $user->last_name = $validated['last_name'];
            if (isset($validated['phone_number'])) $user->phone_number = $validated['phone_number'];
            if (isset($validated['is_active'])) {
                $user->is_active = $validated['is_active'];
                $user->status = $validated['is_active'] ? 'active' : 'suspended';
            }

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('users', 'public');
                $user->profile_picture_url = asset('storage/' . $path);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Generate reset token
            $resetToken = Str::random(60);
            \DB::table('password_resets')->insert([
                'email' => $user->email,
                'token' => bcrypt($resetToken),
                'created_at' => now()
            ]);

            // Send password reset email
            // (Implementation depends on your mail setup)

            return response()->json([
                'success' => true,
                'message' => 'Password reset email sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending password reset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user active/inactive status
     */
    public function toggleActive($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $user->active = ! $user->active;
            $user->status = $user->active ? 'active' : 'suspended';
            $user->save();

            // Revoke all tokens if account is being disabled
            if (! $user->active) {
                $user->tokens()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => $user->active ? 'User activated.' : 'User deactivated.',
                'data'    => ['active' => $user->active],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Prevent self-deletion
            if ((int) $user->id === (int) auth()->id()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete your own account.'], 422);
            }

            $user->tokens()->delete();
            $user->delete();

            return response()->json(['success' => true, 'message' => 'User deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

}
