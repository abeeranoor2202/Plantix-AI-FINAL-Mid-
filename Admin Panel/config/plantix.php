<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plantix AI — Application Constants
    |--------------------------------------------------------------------------
    */

    // Tax rate applied to every order (0.0 = 0%, 0.1 = 10%)
    'tax_rate' => (float) env('PLANTIX_TAX_RATE', 0.0),

    // Platform commission deducted from vendor/expert payouts (0.10 = 10%)
    'admin_commission_rate' => (float) env('PLANTIX_ADMIN_COMMISSION_RATE', 0.10),
    'platform_commission_rate' => (float) env('PLANTIX_PLATFORM_COMMISSION_RATE', env('PLANTIX_ADMIN_COMMISSION_RATE', 0.10)),

    // Maximum items allowed in a single cart
    'cart_max_items' => (int) env('PLANTIX_CART_MAX_ITEMS', 50),

    // Low-stock alert threshold (units)
    'low_stock_threshold' => (int) env('PLANTIX_LOW_STOCK_THRESHOLD', 10),

    // Number of days after delivery within which a return request is allowed
    'return_window_days' => (int) env('PLANTIX_RETURN_WINDOW_DAYS', 7),

    // Per-page pagination defaults
    'paginate' => [
        'products'     => 20,
        'orders'       => 15,
        'appointments' => 15,
        'forum'        => 20,
    ],

    // Currency
    'currency_code'   => env('PLANTIX_CURRENCY_CODE', 'PKR'),
    'currency_symbol' => env('PLANTIX_CURRENCY_SYMBOL', 'Rs'),

    // Featured products shown on homepage
    'homepage_featured_count' => (int) env('PLANTIX_FEATURED_COUNT', 8),

    // Appointment slots per day per expert (used for availability check)
    'appointments_per_day' => (int) env('PLANTIX_APPOINTMENTS_PER_DAY', 10),

    // ── AI / External API Keys ──────────────────────────────────────────────
    // OpenWeatherMap (https://openweathermap.org/api)
    'openweather_api_key' => env('OPENWEATHER_API_KEY', ''),
    'weather_city'        => env('WEATHER_CITY', 'Lahore,PK'),

    // OpenAI GPT (https://platform.openai.com)
    'openai_api_key' => env('OPENAI_API_KEY', ''),

    // Plant Disease Detection API — points to the Flask crop_prediction_api
    // which serves POST /disease/predict using vgg16Mymodel.h5
    // Set DISEASE_API_URL=http://127.0.0.1:5000 (same Flask service)
    'disease_api_url' => env('DISEASE_API_URL', ''),
    'disease_api_key' => env('DISEASE_API_KEY', ''),

    // AI Chat settings
    'ai_chat_model'       => env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'),
    'ai_chat_max_history' => (int) env('AI_CHAT_MAX_HISTORY', 10),

    // ── Security ─────────────────────────────────────────────────────────────
    // Admin IP whitelist: comma-separated IPs / CIDR ranges.
    // Empty = no restriction (all IPs allowed).
    // Example: ADMIN_IP_WHITELIST=192.168.1.100,10.0.0.0/24
    'admin_ip_whitelist' => array_filter(
        explode(',', env('ADMIN_IP_WHITELIST', ''))
    ),

    // Auth hardening
    'auth_max_attempts'    => (int) env('AUTH_MAX_ATTEMPTS', 5),
    'auth_lockout_minutes' => (int) env('AUTH_LOCKOUT_MINUTES', 30),

];
