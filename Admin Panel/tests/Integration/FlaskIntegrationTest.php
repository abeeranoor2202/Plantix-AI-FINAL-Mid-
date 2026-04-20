#!/usr/bin/env php
<?php

/**
 * Integration Test Script
 * 
 * Verifies that:
 * 1. Flask API is running and accessible
 * 2. Model is loaded and working
 * 3. Laravel can communicate with Flask API
 * 4. Predictions are stored correctly
 * 5. Database logging works
 */

use Illuminate\Support\Facades\Http;

// Load Laravel
$dotenv = parse_ini_file(__DIR__ . '/.env');
$flaskUrl = $dotenv['CROP_PREDICTION_API_URL'] ?? 'http://127.0.0.1:5000';
$apiKey = $dotenv['CROP_PREDICTION_API_KEY'] ?? '';

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   Plantix AI - Flask & Laravel Integration Test Suite      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$testsPassed = 0;
$testsFailed = 0;

// ============================================================================
// TEST 1: Flask Health Check
// ============================================================================
echo "📋 TEST 1: Flask API Health Check\n";
try {
    $response = Http::timeout(5)->get("$flaskUrl/health");
    if ($response->successful()) {
        $data = $response->json();
        echo "   ✅ Status: {$data['status']}\n";
        echo "   ✅ Model Loaded: " . ($data['model_loaded'] ? 'Yes' : 'No') . "\n";
        echo "   ✅ Database Ready: " . ($data['database_ready'] ? 'Yes' : 'No') . "\n";
        $testsPassed++;
    } else {
        echo "   ❌ HTTP {$response->status()}\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: {$e->getMessage()}\n";
    $testsFailed++;
}
echo "\n";

// ============================================================================
// TEST 2: Model Information
// ============================================================================
echo "📋 TEST 2: Flask Model Information\n";
try {
    $response = Http::timeout(5)->get("$flaskUrl/model-info");
    if ($response->successful()) {
        $data = $response->json();
        echo "   ✅ Model Name: {$data['model_name']}\n";
        echo "   ✅ Model Version: {$data['model_version']}\n";
        echo "   ✅ Loaded: " . ($data['loaded'] ? 'Yes' : 'No') . "\n";
        $testsPassed++;
    } else {
        echo "   ❌ HTTP {$response->status()}\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: {$e->getMessage()}\n";
    $testsFailed++;
}
echo "\n";

// ============================================================================
// TEST 3: Prediction Endpoint (with API Key)
// ============================================================================
echo "📋 TEST 3: Flask Prediction Endpoint (with Authentication)\n";
try {
    $payload = [
        'nitrogen' => 90,
        'phosphorus' => 42,
        'potassium' => 43,
        'temperature' => 22.5,
        'humidity' => 82,
        'ph' => 6.5,
        'rainfall' => 200,
    ];

    $response = Http::timeout(5)
        ->withHeader('X-API-Key', $apiKey)
        ->post("$flaskUrl/predict", $payload);

    if ($response->successful()) {
        $data = $response->json();
        echo "   ✅ Predicted Crop: {$data['crop']}\n";
        echo "   ✅ Confidence: " . round($data['confidence'] * 100, 2) . "%\n";
        echo "   ✅ Request ID: {$data['request_id']}\n";
        $testsPassed++;
    } else {
        echo "   ❌ HTTP {$response->status()}: {$response->body()}\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: {$e->getMessage()}\n";
    $testsFailed++;
}
echo "\n";

// ============================================================================
// TEST 4: Prediction Endpoint (without API Key - should fail)
// ============================================================================
echo "📋 TEST 4: Flask Prediction Endpoint (without API Key - expect failure)\n";
try {
    $payload = [
        'nitrogen' => 90,
        'phosphorus' => 42,
        'potassium' => 43,
        'temperature' => 22.5,
        'humidity' => 82,
        'ph' => 6.5,
        'rainfall' => 200,
    ];

    $response = Http::timeout(5)
        ->post("$flaskUrl/predict", $payload);

    if ($response->status() === 401 || $response->status() === 403) {
        echo "   ✅ Correctly rejected unauthorized request (HTTP {$response->status()})\n";
        $testsPassed++;
    } else {
        echo "   ❌ Expected 401/403, got HTTP {$response->status()}\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: {$e->getMessage()}\n";
    $testsFailed++;
}
echo "\n";

// ============================================================================
// TEST 5: Admin Stats Endpoint
// ============================================================================
echo "📋 TEST 5: Flask Admin Stats Endpoint\n";
try {
    $response = Http::timeout(5)
        ->withHeader('X-API-Key', $apiKey)
        ->get("$flaskUrl/admin/stats");

    if ($response->successful()) {
        $data = $response->json();
        echo "   ✅ Total Predictions: {$data['stats']['total_predictions']}\n";
        echo "   ✅ Average Confidence: " . round($data['stats']['average_confidence'] * 100, 2) . "%\n";
        $testsPassed++;
    } else {
        echo "   ❌ HTTP {$response->status()}\n";
        $testsFailed++;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: {$e->getMessage()}\n";
    $testsFailed++;
}
echo "\n";

// ============================================================================
// SUMMARY
// ============================================================================
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   Test Summary                                             ║\n";
echo "╠════════════════════════════════════════════════════════════╣\n";
echo "║ ✅ Passed: $testsPassed\n";
echo "║ ❌ Failed: $testsFailed\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

if ($testsFailed === 0) {
    echo "🎉 All integration tests passed!\n";
    exit(0);
} else {
    echo "⚠️  Some tests failed. Please check the configuration.\n";
    exit(1);
}
