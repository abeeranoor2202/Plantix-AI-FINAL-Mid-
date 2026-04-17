<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\AiChatEscalateRequest;
use App\Models\AiChatSession;
use App\Services\Customer\AiChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function __construct(private AiChatService $chatService) {}

    /**
     * Show AI Chat page (Plantix AI).
     */
    public function index(Request $request)
    {
        $sessionKey = $request->session()->get('ai_chat_session_key') ?? Str::uuid();
        $request->session()->put('ai_chat_session_key', $sessionKey);

        $history = $this->chatService->getHistory($sessionKey);

        return view('customer.plantix-ai', compact('sessionKey', 'history'));
    }

    /**
     * Handle a chat message (AJAX POST).
     * Session key is always taken from the server-side session — never trusted from client body.
     */
    public function message(Request $request)
    {
        $request->validate([
            'message'      => 'required|string|max:1000',
            'context_type' => 'nullable|string|in:general,crop_help,disease,fertilizer,order',
        ]);

        // Always use server-side session key — reject any client-provided key
        $sessionKey = $request->session()->get('ai_chat_session_key');

        if (! $sessionKey) {
            $sessionKey = (string) \Illuminate\Support\Str::uuid();
            $request->session()->put('ai_chat_session_key', $sessionKey);
        }

        // If authenticated, enforce that the session belongs to this user
        if (Auth::check()) {
            $session = \App\Models\AiChatSession::where('session_key', $sessionKey)->first();
            if ($session && $session->user_id && $session->user_id !== Auth::id()) {
                // Session belongs to another user — start fresh
                $sessionKey = (string) \Illuminate\Support\Str::uuid();
                $request->session()->put('ai_chat_session_key', $sessionKey);
            }
        }

        $result = $this->chatService->chat(
            $request->input('message'),
            $sessionKey,
            Auth::user(),
            $request->input('context_type', 'general')
        );

        return response()->json([
            'success'     => true,
            'response'    => $result['response'],
            'session_key' => $result['session_key'],
            'model_used'  => $result['model_used'],
        ]);
    }

    /**
     * Get full conversation history for current session (AJAX).
     */
    public function history(Request $request)
    {
        // Always use server-side session — never trust a client-supplied session_key.
        // Accepting it from the request body would let any user read any other user's chat
        // history by guessing/brute-forcing UUIDs.
        $sessionKey = $request->session()->get('ai_chat_session_key');

        if (! $sessionKey) {
            return response()->json(['success' => true, 'data' => []]);
        }

        // If authenticated, enforce that the session belongs to this user
        if (Auth::check()) {
            $session = AiChatSession::where('session_key', $sessionKey)->first();
            if ($session && $session->user_id && $session->user_id !== Auth::id()) {
                return response()->json(['success' => true, 'data' => []]);
            }
        }

        $history = $this->chatService->getHistory($sessionKey);
        return response()->json(['success' => true, 'data' => $history]);
    }

    /**
     * Start a new session (clear conversation).
     */
    public function newSession(Request $request)
    {
        $newKey = Str::uuid();
        $request->session()->put('ai_chat_session_key', $newKey);
        return response()->json(['success' => true, 'session_key' => $newKey]);
    }

    /**
     * List all sessions for authenticated user.
     */
    public function sessions(Request $request)
    {
        $sessions = AiChatSession::where('user_id', Auth::id())
            ->latest()
            ->take(20)
            ->get(['id', 'session_key', 'context_type', 'message_count', 'last_active_at', 'created_at']);

        return response()->json(['success' => true, 'data' => $sessions]);
    }

    /**
     * Escalate the current chat session to an expert review queue.
     */
    public function escalate(AiChatEscalateRequest $request)
    {
        $sessionKey = $request->session()->get('ai_chat_session_key');
        if (! $sessionKey) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'No active chat session found.'], 422)
                : back()->withErrors(['error' => 'No active chat session found.']);
        }

        $session = AiChatSession::where('session_key', $sessionKey)->first();
        if (! $session) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Chat session not found.'], 404)
                : back()->withErrors(['error' => 'Chat session not found.']);
        }

        if (Auth::check() && $session->user_id && $session->user_id !== Auth::id()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Session ownership mismatch.'], 403)
                : back()->withErrors(['error' => 'Session ownership mismatch.']);
        }

        $ticket = $this->chatService->escalateToExpert($sessionKey, Auth::user(), $request->input('reason'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'ticket_id' => $ticket->id,
                'status' => $ticket->status,
                'message' => 'Your request has been escalated to an expert queue.',
            ]);
        }

        return back()->with('success', 'Your AI chat has been escalated to an expert queue.');
    }
}

