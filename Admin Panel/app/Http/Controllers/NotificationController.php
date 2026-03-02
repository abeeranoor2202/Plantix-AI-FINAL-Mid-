<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Shared\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * NotificationController
 *
 * Admin UI for sending in-app (database-channel) notifications.
 * Firebase / FCM removed — all delivery is via the database driver.
 */
class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    // ── Admin notification index view ─────────────────────────────────────────
    public function index(string $id = '')
    {
        $notifications = \DB::table('dynamic_notifications')
                             ->orderBy('created_at', 'desc')
                             ->get();
        return view('admin.notifications.index', compact('id', 'notifications'));
    }

    // ── Delete a dynamic notification ─────────────────────────────────────────
    public function destroyDynamic(string $id)
    {
        \DB::table('dynamic_notifications')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }

    // ── Admin send notification view ──────────────────────────────────────────
    public function send(string $id = '')
    {
        return view('admin.notifications.send', compact('id'));
    }

    // ── Broadcast to all users of a given role ────────────────────────────────
    public function broadcastnotification(Request $request): JsonResponse
    {
        $request->validate([
            'role'       => 'required|in:customer,vendor,expert,admin',
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string|max:1000',
            'send_email' => 'nullable|boolean',
        ]);

        $sendEmail = (bool) ($request->send_email ?? false);
        $totalSent = $this->notifications->sendToRole(
            $request->role,
            $request->subject,
            $request->message,
            $sendEmail
        );

        if ($totalSent === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No users found for the selected role.',
            ]);
        }

        $channels = $sendEmail ? 'in-app and email' : 'in-app';
        
        return response()->json([
            'success' => true,
            'message' => "Notification ({$channels}) queued for {$totalSent} user(s). Processing in background.",
        ]);
    }

    // ── Send a notification to a single user by ID ────────────────────────────
    public function sendNotification(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'title'      => 'required|string|max:255',
            'message'    => 'required|string|max:1000',
            'send_email' => 'nullable|boolean',
        ]);

        $sendEmail = (bool) ($request->send_email ?? false);
        $user      = User::findOrFail($request->user_id);
        $result    = $this->notifications->sendToUser(
            $user,
            $request->title,
            $request->message,
            [],
            $sendEmail
        );

        $channel = $sendEmail ? 'in-app and email' : 'in-app';
        return response()->json([
            'success' => $result,
            'message' => $result ? "Notification ({$channel}) sent successfully." : 'Failed to send notification.',
        ]);
    }

    // ── Get list of users for dropdown ────────────────────────────────────────
    public function getUsersList(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        
        $query = User::select('id', 'name', 'email', 'role')
            ->where('active', true)
            ->orderBy('name', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->limit(100)->get();

        return response()->json([
            'success' => true,
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => ucfirst($user->role),
                ];
            }),
        ]);
    }
}
