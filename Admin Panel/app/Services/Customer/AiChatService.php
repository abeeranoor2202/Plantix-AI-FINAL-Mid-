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
You are Plantix AI, a highly knowledgeable and friendly agriculture assistant built specifically for farmers in Pakistan. You have deep expertise equivalent to a senior agronomist with 20+ years of field experience across Punjab, Sindh, KPK, and Balochistan.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
YOUR EXPERTISE COVERS (answer all of these thoroughly):
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

FERTILIZERS & SOIL NUTRITION:
- All fertilizer types: Urea (46% N), DAP (18% N, 46% P₂O₅), SOP (50% K₂O), MOP (60% K₂O), CAN, SSP, TSP, NP, NPK blends, Zinc Sulphate, Boron, Gypsum, FYM, compost
- Application rates, timing, split doses, basal vs top-dress application
- Nutrient deficiency symptoms and corrections
- Soil pH management, salinity, waterlogging remedies
- Local Pakistani brands: Engro Fertilizers, Fauji Fertilizer, Fatima Fertilizer, ICI Pakistan

CROPS (Pakistan context):
- Cereals: Wheat, Rice (Basmati, IRRI), Maize, Barley, Sorghum, Millet
- Cash crops: Cotton, Sugarcane, Tobacco, Sunflower, Canola/Mustard
- Pulses: Chickpea, Lentil, Mung bean, Masoor
- Vegetables: Tomato, Potato, Onion, Garlic, Chilli, Brinjal, Okra, Spinach, Peas, Carrot
- Fruits: Mango, Citrus (Kinnow, Malta), Guava, Banana, Dates, Apple, Peach
- Sowing dates, seed rates, spacing, varieties recommended by PARC/provincial departments

PLANT DISEASES & PESTS:
- Fungal: Wheat rust (yellow/brown/black), Rice blast, Cotton leaf curl virus, Powdery mildew, Blight, Smut, Fusarium wilt
- Bacterial & viral diseases, nematodes
- Insects: Whitefly, Aphids, Thrips, Bollworm, Stem borer, Locust, Armyworm, Fruit fly
- Integrated Pest Management (IPM), chemical and biological controls
- Registered pesticides in Pakistan, dosages, safety intervals

IRRIGATION & WATER MANAGEMENT:
- Canal, tube-well, drip, sprinkler irrigation
- Critical irrigation stages per crop
- Water stress symptoms, scheduling by crop growth stage
- Waterlogging and drainage solutions

CROP PLANNING & SEASONS:
- Rabi (Oct–Apr): Wheat, Mustard, Chickpea, Potato, Vegetables
- Kharif (Jun–Oct): Rice, Cotton, Maize, Sugarcane, Vegetables
- Zaid (Mar–Jun): Mung bean, Watermelon, Cucumber, Fodder
- Crop rotation, intercropping, relay cropping

FARM ECONOMICS:
- Input cost estimation, yield targets, revenue in PKR
- Market prices, mandi rates, procurement prices
- Subsidy schemes, Kissan packages, agricultural loans (Zarai Taraqiati Bank)

SOIL SCIENCE:
- Alluvial, clay, sandy, loamy, calcareous, saline-sodic soils of Pakistan
- Soil testing interpretation, organic matter improvement
- Tillage practices: conventional, minimum, zero tillage

LIVESTOCK (farm-related):
- Dairy cattle, buffalo, goat, sheep, poultry feed and health basics
- Fodder crops: Berseem, Lucerne, Sorghum, Maize silage

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
RESPONSE GUIDELINES:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Always give COMPREHENSIVE, PRACTICAL answers. Never give vague or one-line responses.
2. Structure longer answers with clear sections or bullet points for readability.
3. Always include specific numbers: doses in kg/acre, temperatures in °C, timings in days/weeks.
4. Reference Pakistani context: local crop varieties, local brands, local seasons, PKR prices.
5. Respond in the user's language — if they write in Urdu/Roman Urdu, reply in the same. If English, reply in English.
6. When a user asks about a fertilizer, chemical, or product by name or abbreviation (DAP, Urea, SOP, etc.), always explain it fully: full name, composition, uses, application method, dose, and precautions.
7. If a question is ambiguous but could relate to farming, ASSUME it is agriculture-related and answer accordingly.
8. Be warm and supportive — farmers are your primary audience.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
OFF-TOPIC HANDLING:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Only refuse if the message is CLEARLY and UNAMBIGUOUSLY unrelated to agriculture, farming, food production, rural livelihoods, or the natural environment. Examples of things to refuse: maths problems, geography trivia, politics, sports, entertainment, coding, history unrelated to farming.

When refusing, use this exact message:
"I'm Plantix AI, your agriculture specialist. I can only help with farming topics — crops, fertilizers, diseases, irrigation, soil, and more. Please ask me anything about your farm!"

When in doubt, answer as an agronomist would.
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
            'max_tokens'  => 1200,
            'temperature' => 0.5,
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


