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
 * GPT-4 / OpenAI API integration with fallback to rule-based agriculture Q&A.
 * All conversations stored for audit and future fine-tuning.
 */
class AiChatService
{
    private const SYSTEM_PROMPT = <<<PROMPT
You are Plantix AI, an expert agriculture assistant for farmers in Pakistan.
You provide advice on:
- Crop recommendations based on soil & weather conditions
- Plant disease identification and treatment
- Fertilizer and nutrient management
- Crop planning and seasonal schedules
- Irrigation management
- Pest control (organic and chemical)
- Market prices and farm economics
- Weather impact on farming

Rules:
- Always respond in the user's language (Urdu or English)
- Be practical and specific to Pakistan's agricultural context
- Reference Pakistani crops: wheat, rice, cotton, sugarcane, maize, vegetables
- Mention local fertilizer brands and products available in Pakistan
- Keep responses concise but complete
- If you don't know, say so honestly
PROMPT;

    private const RULE_BASED_RESPONSES = [
        'fertilizer'    => "For optimal fertilizer use in Pakistan: Apply 1/3 Nitrogen (Urea) at sowing, 1/3 at tillering, and final 1/3 at flag-leaf stage. Full Phosphorus (DAP) and Potassium (MOP) should be applied as basal dose. Zinc Sulphate @ 5 kg/acre every 3 seasons.",
        'disease'       => "Common crop diseases in Pakistan: Wheat Rust (apply Propiconazole), Rice Blast (apply Tricyclazole), Cotton Bollworm (Chlorpyrifos), Tomato Blight (Metalaxyl+Mancozeb). Upload an image in our Disease Detection module for accurate diagnosis.",
        'weather'       => "Pakistan's farming calendar: Rabi season (Oct-Apr) for wheat, mustard; Kharif season (Jun-Oct) for rice, cotton, maize. Monitor weather forecasts and adjust irrigation accordingly.",
        'crop'          => "Best crops for Pakistan: Wheat (loamy soil, 250mm rain), Rice (clay soil, 1000mm), Cotton (well-drained, long growing season), Sugarcane (heavy soils, high water). Use our Crop Recommendation tool for personalized advice.",
        'irrigation'    => "Irrigation guidelines: Wheat needs 4-5 irrigations, Rice needs continuous flooding, Cotton needs 6-8 irrigations. Drip irrigation saves 30-40% water. Irrigate in morning or evening to reduce evaporation.",
        'market'        => "Pakistan crop prices (approximate): Wheat PKR 4,000-5,600/40kg, Rice PKR 3,000-5,000/40kg, Cotton PKR 8,000-12,000/40kg. Check local mandi rates regularly.",
        'pest'          => "Integrated Pest Management: Use pheromone traps, natural predators. Chemical control as last resort. Recommended: Imidacloprid for sucking pests, Chlorpyrifos for soil pests, NPV for bollworm.",
        'soil'          => "Pakistan soil types: Alluvial (Punjab/Sindh - fertile), Clay (waterlogging risk), Sandy (low nutrients), Calcareous (pH 8+, zinc deficient). Get a soil test from your local Agriculture Extension Office.",
        'hello'         => "Asalaamu Alaikum! I'm Plantix AI — your agriculture assistant. I can help with crop advice, disease identification, fertilizer recommendations, and farming guidance for Pakistan. What would you like help with today?",
        'default'       => "Thank you for your question. For accurate agriculture advice specific to your farm, I recommend: 1) Using our Crop Recommendation tool for personalized crop suggestions, 2) Uploading a photo to Disease Detection for plant health issues, 3) Checking the Weather module for your local forecast. How can I assist you further?",
    ];

    private ?string $openAiKey;

    public function __construct()
    {
        $this->openAiKey = config('plantix.openai_api_key');
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
        // Try OpenAI API first
        if ($this->openAiKey) {
            try {
                [$content, $tokens, $model] = $this->callOpenAI($message, $session);
                return [$content, $tokens, $model, false, null];
            } catch (\Throwable $e) {
                Log::warning('OpenAI API failed, using rule-based fallback: ' . $e->getMessage());
                return [$this->ruleBasedResponse($message), 0, 'rule-based-v1', true, 'openai_unavailable'];
            }
        }

        // Rule-based fallback
        return [$this->ruleBasedResponse($message), 0, 'rule-based-v1', true, 'no_openai_key'];
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

    private function callOpenAI(string $message, AiChatSession $session): array
    {
        // Build message history (last 10 messages)
        $history = $session->messages()
            ->where('role', '!=', 'system')
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $messages = [
            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
            ...$history,
            ['role' => 'user', 'content' => $message],
        ];

        $response = Http::withToken($this->openAiKey)
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => 'gpt-4o-mini',
                'messages'    => $messages,
                'max_tokens'  => 600,
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->body());
        }

        $data       = $response->json();
        $content    = $data['choices'][0]['message']['content'] ?? 'No response.';
        $tokens     = $data['usage']['total_tokens'] ?? 0;
        $model      = $data['model'] ?? 'gpt-4o-mini';

        return [$content, $tokens, $model];
    }

    private function ruleBasedResponse(string $message): string
    {
        $message = strtolower($message);

        $keywordMap = [
            'fertilizer|urea|dap|potassium|npk|nutrient'   => 'fertilizer',
            'disease|blight|rust|blast|worm|pest|insect'    => 'disease',
            'weather|rain|temperature|humidity|forecast'    => 'weather',
            'crop|plant|sow|harvest|yield|grow'             => 'crop',
            'irrigation|water|flood|drip|sprinkler'         => 'irrigation',
            'price|market|mandi|sell|profit|income'         => 'market',
            'aphid|whitefly|bollworm|locust|thrips|mite'    => 'pest',
            'soil|loam|clay|sandy|silt|ph|organic'          => 'soil',
            'hello|hi|salam|aoa|assalam|greetings'          => 'hello',
        ];

        foreach ($keywordMap as $pattern => $key) {
            if (preg_match('/(' . $pattern . ')/i', $message)) {
                return self::RULE_BASED_RESPONSES[$key];
            }
        }

        return self::RULE_BASED_RESPONSES['default'];
    }
}


