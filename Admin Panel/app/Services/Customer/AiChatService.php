<?php

namespace App\Services\Customer;

use App\Models\AiChatAudit;
use App\Models\AiChatEscalation;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AiChatService
 *
 * OpenRouter LLM integration for agriculture-only Q&A.
 * Off-topic queries are rejected by the model via system prompt instructions.
 * All conversations stored for audit and future fine-tuning.
 */
class AiChatService
{
    private const SYSTEM_PROMPT = <<<PROMPT
You are Plantix AI, a specialist agriculture assistant exclusively for farmers in Pakistan.

YOUR SCOPE — you ONLY answer questions related to:
- Crops: recommendations, varieties, sowing, harvesting
- Plant diseases: identification, symptoms, treatment
- Fertilizers and soil nutrition (Urea, DAP, SOP, MOP, micronutrients)
- Soil types and soil health
- Irrigation and water management
- Pest and weed control
- Crop planning and seasonal schedules (Rabi, Kharif, Zaid)
- Farm economics: yield estimates, input costs, market prices in PKR
- Weather impact on farming in Pakistan
- Livestock and poultry only when directly related to farm operations

STRICT RULES:
1. If the user's message is NOT related to agriculture, farming, or rural livelihoods, respond with EXACTLY this message and nothing else:
   "I'm Plantix AI, an agriculture-only assistant. I can't help with that topic. Please ask me about crops, diseases, fertilizers, irrigation, or any farming question — I'm here to help with your farm!"
2. Never answer general knowledge questions (e.g. maths, geography, history, politics, entertainment).
3. Never answer questions about technology, coding, or unrelated sciences.
4. Always respond in the user's language (Urdu or English — detect automatically).
5. Be practical and specific to Pakistan's agricultural context.
6. Reference Pakistani crops: wheat, rice, cotton, sugarcane, maize, vegetables, fruits.
7. Mention local fertilizer brands and products available in Pakistan.
8. Keep responses concise but complete.
9. If you genuinely don't know something within agriculture, say so honestly.
PROMPT;

    private const OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    private ?string $openRouterKey;

    public function __construct()
    {
        $this->openRouterKey = config('plantix.openrouter_api_key');
    }

    /**
     * Send a message and get AI response.
     *
     * @param  string   $message
     * @param  string   $sessionKey
     * @param  User|null $user
     * @param  string   $contextType
     * @return array    {session_key, message_id, response, tokens_used}
     */
    public function chat(string $message, string $sessionKey, ?User $user = null, string $contextType = 'general'): array
    {
        // Get or create session
        $session = $this->getOrCreateSession($sessionKey, $user, $contextType);

        // Store user's message
        AiChatMessage::create([
            'session_id' => $session->id,
            'role'       => 'user',
            'content'    => $message,
        ]);

        $lastUserMessage = $session->messages()->latest()->first();
        $this->audit($session, 'message_received', $user?->id, [
            'context_type' => $contextType,
            'length'       => mb_strlen($message),
        ], $lastUserMessage?->id);

        // Get AI response
        $start = microtime(true);
        [$response, $tokensUsed, $modelUsed, $fallbackUsed, $fallbackReason] = $this->generateResponse($message, $session);
        $latencyMs = round((microtime(true) - $start) * 1000);

        // Store assistant's response
        $assistantMsg = AiChatMessage::create([
            'session_id'  => $session->id,
            'role'        => 'assistant',
            'content'     => $response,
            'model_used'  => $modelUsed,
            'tokens_used' => $tokensUsed,
            'metadata'    => [
                'latency_ms' => $latencyMs,
                'context_type' => $contextType,
                'fallback_used' => $fallbackUsed,
                'fallback_reason' => $fallbackReason,
            ],
        ]);

        if ($fallbackUsed) {
            $this->audit($session, 'fallback_triggered', $user?->id, [
                'reason' => $fallbackReason,
                'model'  => $modelUsed,
            ], $assistantMsg->id);
        }

        $this->audit($session, 'response_generated', $user?->id, [
            'model'         => $modelUsed,
            'tokens_used'   => $tokensUsed,
            'latency_ms'    => $latencyMs,
            'fallback_used' => $fallbackUsed,
        ], $assistantMsg->id);

        // Update session
        $session->update([
            'last_active_at' => now(),
            'message_count'  => $session->message_count + 2,
        ]);

        return [
            'session_key' => $session->session_key,
            'message_id'  => $assistantMsg->id,
            'response'    => $response,
            'tokens_used' => $tokensUsed,
            'model_used'  => $modelUsed,
        ];
    }

    /**
     * Create or reuse an active expert-escalation ticket for the session.
     */
    public function escalateToExpert(string $sessionKey, ?User $user, ?string $reason = null): AiChatEscalation
    {
        $session = AiChatSession::where('session_key', $sessionKey)->firstOrFail();

        $latestMessage = $session->messages()->latest()->first();

        $open = AiChatEscalation::where('session_id', $session->id)
            ->whereIn('status', [AiChatEscalation::STATUS_PENDING, AiChatEscalation::STATUS_ASSIGNED])
            ->latest()
            ->first();

        if ($open) {
            $open->update([
                'reason' => $reason ?: $open->reason,
                'latest_message_id' => $latestMessage?->id,
            ]);

            $this->audit($session, 'escalation_requested', $user?->id, [
                'escalation_id' => $open->id,
                'status' => $open->status,
                'reopened' => true,
            ], $latestMessage?->id);

            return $open->fresh();
        }

        $ticket = AiChatEscalation::create([
            'session_id' => $session->id,
            'user_id' => $user?->id ?? $session->user_id,
            'latest_message_id' => $latestMessage?->id,
            'status' => AiChatEscalation::STATUS_PENDING,
            'reason' => $reason,
        ]);

        $this->audit($session, 'escalation_requested', $user?->id, [
            'escalation_id' => $ticket->id,
            'status' => $ticket->status,
            'reopened' => false,
        ], $latestMessage?->id);

        return $ticket;
    }

    /**
     * Get conversation history for a session.
     */
    public function getHistory(string $sessionKey): array
    {
        $session = AiChatSession::where('session_key', $sessionKey)->first();
        if (!$session) {
            return [];
        }
        return $session->messages()->where('role', '!=', 'system')->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content, 'created_at' => $m->created_at])
            ->toArray();
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function getOrCreateSession(string $sessionKey, ?User $user, string $contextType): AiChatSession
    {
        return AiChatSession::firstOrCreate(['session_key' => $sessionKey], [
            'user_id'      => $user?->id,
            'context_type' => $contextType,
        ]);
    }

    private function generateResponse(string $message, AiChatSession $session): array
    {
        if (!$this->openRouterKey) {
            Log::warning('AiChatService: OPENROUTER_API_KEY is not configured.');
            return [
                'Plantix AI is temporarily unavailable. Please try again later or contact support.',
                0,
                'unavailable',
                true,
                'no_openrouter_key',
            ];
        }

        try {
            [$content, $tokens, $model] = $this->callOpenRouter($message, $session);
            return [$content, $tokens, $model, false, null];
        } catch (\Throwable $e) {
            Log::error('OpenRouter API failed in AiChatService: ' . $e->getMessage());
            return [
                'Plantix AI is temporarily unavailable. Please try again in a moment.',
                0,
                'unavailable',
                true,
                'openrouter_error',
            ];
        }
    }

    private function audit(AiChatSession $session, string $eventType, ?int $actorUserId = null, array $meta = [], ?int $messageId = null): void
    {
        AiChatAudit::create([
            'session_id' => $session->id,
            'message_id' => $messageId,
            'event_type' => $eventType,
            'actor_user_id' => $actorUserId,
            'metadata' => $meta,
            'created_at' => now(),
        ]);
    }

    private function callOpenRouter(string $message, AiChatSession $session): array
    {
        $model   = config('plantix.openrouter_model', 'google/gemini-2.0-flash-001');
        $timeout = (int) config('plantix.openrouter_timeout', 45);
        $maxHistory = (int) config('plantix.ai_chat_max_history', 10);

        // Build message history
        $history = $session->messages()
            ->where('role', '!=', 'system')
            ->latest()
            ->take($maxHistory)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $messages = [
            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
            ...$history,
            ['role' => 'user', 'content' => $message],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openRouterKey,
            'HTTP-Referer'  => config('app.url', 'http://localhost'),
            'X-Title'       => config('app.name', 'Plantix AI'),
            'Content-Type'  => 'application/json',
        ])
        ->timeout($timeout)
        ->post(self::OPENROUTER_URL, [
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => 600,
            'temperature' => 0.7,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenRouter API error: ' . $response->body());
        }

        $data    = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? 'No response.';
        $tokens  = $data['usage']['total_tokens'] ?? 0;
        $model   = $data['model'] ?? $model;

        return [$content, $tokens, $model];
    }

}


