<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChatAudit;
use App\Models\AiChatEscalation;
use App\Models\AiChatSession;
use App\Models\Expert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAiChatController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status');

        $sessions = AiChatSession::with(['user:id,name,email'])
            ->withCount('messages')
            ->withCount([
                'escalations as open_escalations_count' => function ($q) {
                    $q->whereIn('status', [
                        AiChatEscalation::STATUS_PENDING,
                        AiChatEscalation::STATUS_ASSIGNED,
                    ]);
                },
            ])
            ->latest('last_active_at')
            ->paginate(20)
            ->withQueryString();

        $escalationsQuery = AiChatEscalation::with([
            'session.user:id,name,email',
            'assignedExpert.user:id,name,email',
            'latestMessage:id,session_id,content,created_at',
        ])->latest();

        if (! empty($statusFilter)) {
            $escalationsQuery->where('status', $statusFilter);
        }

        $escalations = $escalationsQuery->paginate(20)->withQueryString();

        $stats = [
            'total_sessions' => AiChatSession::count(),
            'messages_today' => AiChatAudit::whereDate('created_at', now()->toDateString())
                ->where('event_type', 'message_received')
                ->count(),
            'fallbacks_today' => AiChatAudit::whereDate('created_at', now()->toDateString())
                ->where('event_type', 'fallback_triggered')
                ->count(),
            'pending_escalations' => AiChatEscalation::where('status', AiChatEscalation::STATUS_PENDING)->count(),
            'assigned_escalations' => AiChatEscalation::where('status', AiChatEscalation::STATUS_ASSIGNED)->count(),
        ];

        $experts = Expert::with('user:id,name,email')->approved()->orderByDesc('rating_avg')->take(100)->get();

        return view('admin.ai-modules.chat-monitor', compact('sessions', 'escalations', 'stats', 'experts'));
    }

    public function assignEscalation(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'expert_id' => 'required|exists:experts,id',
        ]);

        $ticket = AiChatEscalation::with('session')->findOrFail($id);

        $ticket->update([
            'status' => AiChatEscalation::STATUS_ASSIGNED,
            'assigned_expert_id' => (int) $data['expert_id'],
            'assigned_by' => auth('admin')->id(),
            'assigned_at' => now(),
        ]);

        AiChatAudit::create([
            'session_id' => $ticket->session_id,
            'message_id' => $ticket->latest_message_id,
            'event_type' => 'escalation_assigned',
            'actor_user_id' => auth('admin')->id(),
            'metadata' => [
                'escalation_id' => $ticket->id,
                'expert_id' => (int) $data['expert_id'],
            ],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Escalation assigned to expert.');
    }

    public function resolveEscalation(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'resolution_notes' => 'required|string|max:1000',
            'final_status' => 'nullable|in:resolved,closed',
        ]);

        $ticket = AiChatEscalation::with('session')->findOrFail($id);

        $finalStatus = $data['final_status'] ?? AiChatEscalation::STATUS_RESOLVED;

        $ticket->update([
            'status' => $finalStatus,
            'resolved_by' => auth('admin')->id(),
            'resolved_at' => now(),
            'resolution_notes' => $data['resolution_notes'],
        ]);

        AiChatAudit::create([
            'session_id' => $ticket->session_id,
            'message_id' => $ticket->latest_message_id,
            'event_type' => 'escalation_resolved',
            'actor_user_id' => auth('admin')->id(),
            'metadata' => [
                'escalation_id' => $ticket->id,
                'status' => $finalStatus,
            ],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Escalation updated successfully.');
    }
}
