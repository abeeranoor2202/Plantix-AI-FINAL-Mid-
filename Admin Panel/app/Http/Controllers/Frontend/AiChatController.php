<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AiChatSession;
use App\Services\AiChatService;
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

        return view('pages.plantix-ai', compact('sessionKey', 'history'));
    }

    /**
     * Handle a chat message (AJAX POST).
     */
    public function message(Request $request)
    {
        $request->validate([
            'message'      => 'required|string|max:1000',
            'session_key'  => 'nullable|string|max:100',
            'context_type' => 'nullable|string|in:general,crop_help,disease,fertilizer,order',
        ]);

        $sessionKey  = $request->input('session_key')
            ?? $request->session()->get('ai_chat_session_key')
            ?? Str::uuid();

        // Store session key in session
        $request->session()->put('ai_chat_session_key', $sessionKey);

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
        $sessionKey = $request->input('session_key')
            ?? $request->session()->get('ai_chat_session_key');

        if (!$sessionKey) {
            return response()->json(['success' => true, 'data' => []]);
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
}
