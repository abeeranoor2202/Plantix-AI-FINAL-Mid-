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
            'model_used'       => 'plantix-ai-v1',
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
                $report->update([
                    'detected_disease' => $top['disease'],
                    'confidence_score' => $top['confidence'],
                    'all_predictions'  => $predictions,
                    'status'           => 'processed',
                ]);

                $this->generateSuggestion($report, $top['disease']);
            }
        } catch (\Throwable $e) {
            Log::error('DiseaseDetectionService processReport failed: ' . $e->getMessage(), [
                'report_id' => $report->id,
            ]);
            $report->update(['status' => 'manual_review']);
        }
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
     * Run inference. Tries external API first; falls back to rule-based.
     */
    private function runInference(string $imagePath, string $cropName): array
    {
        $apiUrl = config('plantix.disease_api_url');

        if ($apiUrl) {
            try {
                $fullPath = Storage::disk('local')->path($imagePath);
                $response = Http::timeout(15)
                    ->attach('image', file_get_contents($fullPath), basename($imagePath))
                    ->post($apiUrl, ['crop' => $cropName]);

                if ($response->successful()) {
                    return $response->json('predictions', []);
                }
            } catch (\Throwable $e) {
                Log::warning('Disease API unavailable, using fallback: ' . $e->getMessage());
            }
        }

        // Rule-based fallback: random selection from KB (demo behavior)
        return $this->ruleBasedFallback($cropName);
    }

    private function ruleBasedFallback(string $cropName): array
    {
        // No API configured — do NOT guess randomly
        // Return unknown status so the report goes to manual_review
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


