<?php

namespace App\Services\Customer;

use App\Models\CropDiseaseReport;
use App\Models\DiseaseSuggestion;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * DiseaseDetectionService
 *
 * Handles image upload, AI inference (external API or rule-based fallback),
 * and treatment suggestion generation.
 * Drop-in ready for PlantNet / Roboflow / custom model API.
 */
class DiseaseDetectionService
{
    /**
     * Rule-based disease knowledge base for offline/fallback mode.
     * Key = detected_disease string (lowercase).
     */
    private const DISEASE_KB = [
        'wheat_rust' => [
            'name'        => 'Wheat Rust (Yellow/Brown Rust)',
            'description' => 'Fungal disease causing yellow or brown pustules on leaves and stems. Spreads rapidly in cool moist conditions.',
            'organic'     => 'Remove infected plant material. Apply neem oil spray (5 ml/L). Promote air circulation.',
            'chemical'    => 'Propiconazole 25% EC @ 0.1% or Tebuconazole 250 EC @ 0.1%. Spray at first sign.',
            'prevention'  => 'Use certified rust-resistant varieties. Avoid overhead irrigation. Early sowing.',
            'products'    => ['Wheat', 'Neem Oil Spray', 'Fungicide (Propiconazole)'],
        ],
        'rice_blast' => [
            'name'        => 'Rice Blast',
            'description' => 'Fungal disease causing diamond-shaped greyish lesions on leaves. Can destroy up to 30% yield.',
            'organic'     => 'Silica supplementation strengthens cell walls. Trichoderma biofungicide application.',
            'chemical'    => 'Tricyclazole 75% WP @ 0.06% or Isoprothiolane 40% EC @ 1.5 ml/L.',
            'prevention'  => 'Balanced nitrogen application. Avoid excess moisture on leaves. Resistant varieties.',
            'products'    => ['Tricyclazole', 'Isoprothiolane', 'Trichoderma'],
        ],
        'cotton_bollworm' => [
            'name'        => 'Cotton American Bollworm',
            'description' => 'Heliothis armigera larvae bore into cotton bolls, causing significant yield loss.',
            'organic'     => 'Install pheromone traps @ 5/acre. Spray NPV (Nuclear Polyhedrosis Virus) @ 250 LE/acre.',
            'chemical'    => 'Chlorpyrifos 20% EC @ 2.5 ml/L or Spinosad 45% SC @ 0.3 ml/L.',
            'prevention'  => 'Early sowing. Bt-cotton varieties. Bird perches for natural predators.',
            'products'    => ['Chlorpyrifos', 'Spinosad', 'Pheromone Traps'],
        ],
        'tomato_blight' => [
            'name'        => 'Tomato Late Blight (Phytophthora infestans)',
            'description' => 'Water-soaked patches on leaves turning dark brown. White sporulation in humid conditions.',
            'organic'     => 'Copper-based bactericide (Bordeaux mixture). Remove infected plants immediately.',
            'chemical'    => 'Metalaxyl + Mancozeb @ 2 g/L or Dimethomorph 50% WG @ 1 g/L.',
            'prevention'  => 'Avoid overhead irrigation. Crop rotation. Good field drainage.',
            'products'    => ['Bordeaux Mixture', 'Metalaxyl-Mancozeb', 'Dimethomorph'],
        ],
        'maize_stalk_rot' => [
            'name'        => 'Maize Stalk Rot',
            'description' => 'Fusarium / Pythium causes rotting of the lower stalk, leading to lodging.',
            'organic'     => 'Trichoderma seed treatment. Biocontrol agents at planting.',
            'chemical'    => 'Carbendazim + Thiram 40+20 WS seed treatment @ 3g/kg seed.',
            'prevention'  => 'Balanced fertilization. Avoid waterlogging. Harvest on time.',
            'products'    => ['Trichoderma', 'Carbendazim', 'Thiram'],
        ],
        'powdery_mildew' => [
            'name'        => 'Powdery Mildew',
            'description' => 'White powdery fungi coating on leaves. Common in dry conditions with high humidity nights.',
            'organic'     => 'Potassium bicarbonate spray. Milk spray (1:9 ratio). Neem extract.',
            'chemical'    => 'Sulphur 80% WG @ 3 g/L or Hexaconazole 5% SC @ 1 ml/L.',
            'prevention'  => 'Spacing for air circulation. Avoid late evening irrigation.',
            'products'    => ['Sulphur WG', 'Hexaconazole', 'Neem Extract'],
        ],
        'leaf_curl' => [
            'name'        => 'Leaf Curl Virus (CLCU)',
            'description' => 'Viral disease spread by whitefly causing upward curling and thickening of leaves.',
            'organic'     => 'Yellow sticky traps for whitefly control. Neem oil spray.',
            'chemical'    => 'Imidacloprid 17.8 SL @ 0.3 ml/L for whitefly vector control.',
            'prevention'  => 'CLCU-resistant varieties. Reflective mulch. Rogue out infected plants.',
            'products'    => ['Imidacloprid', 'Yellow Sticky Traps', 'Neem Oil'],
        ],
        'healthy' => [
            'name'        => 'Healthy Plant',
            'description' => 'No significant disease detected. Plant appears healthy.',
            'organic'     => 'Continue good agricultural practices.',
            'chemical'    => 'No immediate treatment required.',
            'prevention'  => 'Regular monitoring. Balanced fertilization. Proper irrigation.',
            'products'    => [],
        ],
    ];

    /**
     * Step 1 — Store image + create a pending report record.
     * Returns immediately so a queued job can finish step 2.
     */
    public function createPendingReport(User $user, UploadedFile $image, array $meta = []): CropDiseaseReport
    {
        $imagePath = $this->storeImage($image, $user->id);

        return CropDiseaseReport::create([
            'user_id'          => $user->id,
            'crop_name'        => $meta['crop_name'] ?? null,
            'image_path'       => $imagePath,
            'model_used'       => 'vgg16-plant-disease-v1',
            'status'           => 'pending',
            'user_description' => $meta['user_description'] ?? null,
        ]);
    }

    /**
     * Step 2 — Run inference + create suggestion for an existing report.
     * Called by ProcessDiseaseDetection queued job.
     */
    public function processReport(CropDiseaseReport $report): void
    {
        try {
            $predictions = $this->runInference($report->image_path, $report->crop_name ?? '');
            $top         = $predictions[0] ?? ['disease' => 'unknown', 'confidence' => 0.0];

            if ($top['disease'] === 'unknown') {
                $report->update(['status' => 'manual_review']);
            } else {
                // Store the raw VGG16 label as detected_disease, display_name for UI
                $displayName = $top['display_name'] ?? $top['disease'];

                $report->update([
                    'detected_disease' => $displayName,
                    'confidence_score' => $top['confidence'],
                    'all_predictions'  => $predictions,
                    'model_used'       => 'vgg16-plant-disease-v1',
                    'status'           => 'processed',
                ]);

                // Map VGG16 label to knowledge-base key for treatment suggestions
                $kbKey = $this->mapVgg16LabelToKbKey($top['disease']);
                $this->generateSuggestion($report, $kbKey);
            }
        } catch (\Throwable $e) {
            Log::error('DiseaseDetectionService processReport failed: ' . $e->getMessage(), [
                'report_id' => $report->id,
            ]);
            $report->update(['status' => 'manual_review']);
        }
    }

    /**
     * Map a VGG16 PlantVillage label to a DISEASE_KB key.
     * Falls back to 'healthy' for healthy classes, or the raw label for unknown ones.
     */
    private function mapVgg16LabelToKbKey(string $vgg16Label): string
    {
        // Healthy classes
        if (str_contains(strtolower($vgg16Label), 'healthy')) {
            return 'healthy';
        }

        $label = strtolower($vgg16Label);

        $map = [
            'tomato___late_blight'                                    => 'tomato_blight',
            'tomato___early_blight'                                   => 'tomato_blight',
            'tomato___bacterial_spot'                                 => 'tomato_blight',
            'tomato___leaf_mold'                                      => 'powdery_mildew',
            'tomato___septoria_leaf_spot'                             => 'tomato_blight',
            'tomato___spider_mites two-spotted_spider_mite'           => 'tomato_blight',
            'tomato___target_spot'                                    => 'tomato_blight',
            'tomato___tomato_yellow_leaf_curl_virus'                  => 'leaf_curl',
            'tomato___tomato_mosaic_virus'                            => 'leaf_curl',
            'corn_(maize)___cercospora_leaf_spot gray_leaf_spot'      => 'maize_stalk_rot',
            'corn_(maize)___common_rust_'                             => 'wheat_rust',
            'corn_(maize)___northern_leaf_blight'                     => 'maize_stalk_rot',
            'apple___apple_scab'                                      => 'powdery_mildew',
            'apple___black_rot'                                       => 'tomato_blight',
            'apple___cedar_apple_rust'                                => 'wheat_rust',
            'cherry_(including_sour)___powdery_mildew'                => 'powdery_mildew',
            'grape___black_rot'                                       => 'tomato_blight',
            'grape___esca_(black_measles)'                            => 'tomato_blight',
            'grape___leaf_blight_(isariopsis_leaf_spot)'              => 'tomato_blight',
            'orange___haunglongbing_(citrus_greening)'                => 'leaf_curl',
            'peach___bacterial_spot'                                  => 'tomato_blight',
            'pepper,_bell___bacterial_spot'                           => 'tomato_blight',
            'potato___early_blight'                                   => 'tomato_blight',
            'potato___late_blight'                                    => 'tomato_blight',
            'squash___powdery_mildew'                                 => 'powdery_mildew',
            'strawberry___leaf_scorch'                                => 'tomato_blight',
        ];

        return $map[$label] ?? 'healthy';
    }

    /**
     * Process an uploaded crop image for disease detection.
     *
     * @param User         $user
     * @param UploadedFile $image
     * @param array        $meta   ['crop_name', 'user_description']
     * @return CropDiseaseReport
     */
    public function detect(User $user, UploadedFile $image, array $meta = []): CropDiseaseReport
    {
        $report = $this->createPendingReport($user, $image, $meta);
        $this->processReport($report);
        return $report->fresh('suggestion');
    }

    /**
     * Manually assign a disease to a report (admin override).
     */
    public function assignDisease(CropDiseaseReport $report, string $disease, ?int $expertUserId = null): DiseaseSuggestion
    {
        $report->update([
            'detected_disease' => $disease,
            'status'           => 'processed',
        ]);

        // Delete old suggestion if any
        $report->suggestion?->delete();

        return $this->generateSuggestion($report, strtolower(str_replace(' ', '_', $disease)), $expertUserId);
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function storeImage(UploadedFile $image, int $userId): string
    {
        // Derive extension from server-side MIME type — never trust client filename
        $mimeType = $image->getMimeType();
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (! isset($allowedMimes[$mimeType])) {
            throw new \InvalidArgumentException("Unsupported image type: {$mimeType}. Only JPEG, PNG, WebP allowed.");
        }

        $ext  = $allowedMimes[$mimeType];
        $name = 'disease/' . $userId . '/' . Str::uuid() . '.' . $ext;

        // Store on the private disk — not publicly accessible
        Storage::disk('local')->putFileAs(dirname($name), $image, basename($name));

        return $name;
    }

    /**
     * Run inference against the VGG16 Flask disease detection endpoint.
     *
     * Endpoint: POST {DISEASE_API_URL}/disease/predict
     * Auth    : X-API-Key header (DISEASE_API_KEY)
     *
     * Response shape:
     *   {
     *     "success": true,
     *     "disease": "Tomato___Late_blight",
     *     "display_name": "Tomato Late Blight",
     *     "confidence": 0.97,
     *     "predictions": [
     *       {"disease": "...", "display_name": "...", "confidence": 0.97},
     *       ...
     *     ]
     *   }
     */
    private function runInference(string $imagePath, string $cropName): array
    {
        $apiUrl = rtrim((string) config('plantix.disease_api_url'), '/');
        $apiKey = (string) config('plantix.disease_api_key');

        if ($apiUrl === '') {
            Log::warning('DiseaseDetectionService: DISEASE_API_URL is not configured. Report will go to manual_review.');
            return [['disease' => 'unknown', 'confidence' => 0.0]];
        }

        try {
            $fullPath = Storage::disk('local')->path($imagePath);

            $requestBuilder = Http::timeout(30)
                ->attach('image', file_get_contents($fullPath), basename($imagePath));

            if ($apiKey !== '') {
                $requestBuilder = $requestBuilder->withHeaders(['X-API-Key' => $apiKey]);
            }

            $response = $requestBuilder->post($apiUrl . '/disease/predict');

            if (! $response->successful()) {
                Log::warning('Disease API returned non-success status.', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [['disease' => 'unknown', 'confidence' => 0.0]];
            }

            $json = $response->json();

            // Normalise to the internal format: [['disease' => ..., 'confidence' => ...], ...]
            if (! empty($json['predictions']) && is_array($json['predictions'])) {
                return array_map(fn($p) => [
                    'disease'      => $p['disease'] ?? 'unknown',
                    'display_name' => $p['display_name'] ?? ($p['disease'] ?? 'Unknown'),
                    'confidence'   => (float) ($p['confidence'] ?? 0.0),
                ], $json['predictions']);
            }

            // Fallback: single-result response
            if (! empty($json['disease'])) {
                return [[
                    'disease'      => $json['disease'],
                    'display_name' => $json['display_name'] ?? $json['disease'],
                    'confidence'   => (float) ($json['confidence'] ?? 0.0),
                ]];
            }

            return [['disease' => 'unknown', 'confidence' => 0.0]];

        } catch (\Throwable $e) {
            Log::error('DiseaseDetectionService: inference request failed: ' . $e->getMessage(), [
                'image_path' => $imagePath,
            ]);
            return [['disease' => 'unknown', 'confidence' => 0.0]];
        }
    }

    private function ruleBasedFallback(string $cropName): array
    {
        // No API configured — return unknown so the report goes to manual_review
        return [
            ['disease' => 'unknown', 'confidence' => 0.0],
        ];
    }

    private function generateSuggestion(
        CropDiseaseReport $report,
        string $diseaseKey,
        ?int $verifiedBy = null
    ): DiseaseSuggestion {
        $key  = strtolower(str_replace(' ', '_', $diseaseKey));
        $data = self::DISEASE_KB[$key] ?? self::DISEASE_KB['healthy'];

        // Lookup recommended product IDs from the Product table
        $productIds = Product::whereIn('name', $data['products'])->pluck('id')->toArray();

        return DiseaseSuggestion::create([
            'report_id'           => $report->id,
            'disease_name'        => $data['name'],
            'description'         => $data['description'],
            'organic_treatment'   => $data['organic'],
            'chemical_treatment'  => $data['chemical'],
            'preventive_measures' => $data['prevention'],
            'recommended_products' => $productIds,
            'expert_verified'     => $verifiedBy !== null,
            'verified_by'         => $verifiedBy,
        ]);
    }
}


