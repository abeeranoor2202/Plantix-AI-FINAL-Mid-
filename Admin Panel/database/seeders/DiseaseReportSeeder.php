<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiseaseReportSeeder extends Seeder
{
    public function run(): void
    {
        $now       = Carbon::now();
        $customers = DB::table('users')->where('role', 'user')->limit(25)->pluck('id')->toArray();
        $expertId  = DB::table('users')->where('email', 'amina.malik@plantix.com')->value('id') ?? null;

        $diseases = [
            [
                'crop_name'         => 'Wheat',
                'detected_disease'  => 'Powdery Mildew',
                'confidence_score'  => 92.50,
                'all_predictions'   => [['disease' => 'Powdery Mildew', 'confidence' => 92.5], ['disease' => 'Leaf Rust', 'confidence' => 5.0]],
                'status'            => 'processed',
                'description'       => 'White powdery coating on upper leaf surface, mainly on flag leaf.',
                'suggestion' => [
                    'disease_name'       => 'Powdery Mildew',
                    'description'        => 'Fungal disease caused by Blumeria graminis f.sp. tritici.',
                    'organic_treatment'  => 'Spray potassium bicarbonate or neem oil at 1% concentration.',
                    'chemical_treatment' => 'Propiconazole 25 EC at 250ml/acre. Repeat after 14 days.',
                    'preventive_measures'=> 'Use resistant varieties. Avoid excess nitrogen. Ensure plant spacing.',
                    'expert_verified'    => true,
                ],
            ],
            [
                'crop_name'         => 'Tomato',
                'detected_disease'  => 'Early Blight',
                'confidence_score'  => 87.30,
                'all_predictions'   => [['disease' => 'Early Blight', 'confidence' => 87.3], ['disease' => 'Late Blight', 'confidence' => 9.0]],
                'status'            => 'processed',
                'description'       => 'Dark brown spots with concentric rings on lower leaves.',
                'suggestion' => [
                    'disease_name'       => 'Early Blight',
                    'description'        => 'Fungal disease caused by Alternaria solani.',
                    'organic_treatment'  => 'Copper sulphate + lime (Bordeaux mixture) spray.',
                    'chemical_treatment' => 'Mancozeb 80 WP at 2.5 g/L or Chlorothalonil 75 WP.',
                    'preventive_measures'=> 'Crop rotation, remove infected leaves, avoid overhead irrigation.',
                    'expert_verified'    => true,
                ],
            ],
            [
                'crop_name'         => 'Cotton',
                'detected_disease'  => 'Cotton Leaf Curl Virus',
                'confidence_score'  => 95.10,
                'all_predictions'   => [['disease' => 'Cotton Leaf Curl Virus', 'confidence' => 95.1], ['disease' => 'Nitrogen Deficiency', 'confidence' => 2.5]],
                'status'            => 'processed',
                'description'       => 'Leaves are curling upward with dark veins. Stunted plant growth.',
                'suggestion' => [
                    'disease_name'       => 'Cotton Leaf Curl Virus (CLCuD)',
                    'description'        => 'Viral disease spread by whitefly (Bemisia tabaci).',
                    'organic_treatment'  => 'Sticky yellow traps for whitefly. Introduce Encarsia formosa as biocontrol.',
                    'chemical_treatment' => 'Spray Imidacloprid 200 SL to control whitefly vector. Destroy infected plants.',
                    'preventive_measures'=> 'Use CLCuD-resistant varieties (FH-142, BH-167). Avoid late sowing.',
                    'expert_verified'    => true,
                ],
            ],
            [
                'crop_name'         => 'Potato',
                'detected_disease'  => 'Late Blight',
                'confidence_score'  => 89.70,
                'all_predictions'   => [['disease' => 'Late Blight', 'confidence' => 89.7], ['disease' => 'Early Blight', 'confidence' => 7.2]],
                'status'            => 'processed',
                'description'       => 'Water-soaked lesions on leaves, white fungal growth on underside.',
                'suggestion' => [
                    'disease_name'       => 'Late Blight (Phytophthora infestans)',
                    'description'        => 'Water mold causing devastating losses in humid conditions.',
                    'organic_treatment'  => 'Copper hydroxide 77 WP spray as protectant.',
                    'chemical_treatment' => 'Metalaxyl + Mancozeb (Ridomil Gold) at 2.5 g/L every 7 days.',
                    'preventive_measures'=> 'Avoid excessive irrigation, use certified disease-free seed tubers.',
                    'expert_verified'    => false,
                ],
            ],
            [
                'crop_name'         => 'Mango',
                'detected_disease'  => 'Powdery Mildew',
                'confidence_score'  => 91.20,
                'all_predictions'   => [['disease' => 'Powdery Mildew', 'confidence' => 91.2], ['disease' => 'Anthracnose', 'confidence' => 6.0]],
                'status'            => 'processed',
                'description'       => 'White powdery growth on young leaves and flower panicles.',
                'suggestion' => [
                    'disease_name'       => 'Mango Powdery Mildew (Oidium mangiferae)',
                    'description'        => 'Major pre-harvest disease reducing fruit set.',
                    'organic_treatment'  => 'Spray sulphur dust 30 kg/acre at pre-flowering stage.',
                    'chemical_treatment' => 'Hexaconazole 5 EC at 1 ml/L. Apply 2–3 weeks before flowering.',
                    'preventive_measures'=> 'Prune dense canopy for airflow. Avoid late-season nitrogen.',
                    'expert_verified'    => true,
                ],
            ],
        ];

        // Additional unprocessed/pending reports for edge coverage
        $pendingCrops = ['Rice', 'Sunflower', 'Pepper', 'Cucumber', 'Sugarcane'];

        foreach ($customers as $idx => $userId) {
            $diseaseData = $diseases[$idx % count($diseases)];
            $status      = $idx < count($diseases) * 3 ? 'processed' : 'pending';

            if ($status === 'pending') {
                $reportId = DB::table('crop_disease_reports')->insertGetId([
                    'user_id'           => $userId,
                    'crop_name'         => $pendingCrops[$idx % count($pendingCrops)],
                    'image_path'        => 'disease_reports/pending_' . $idx . '.jpg',
                    'detected_disease'  => null,
                    'confidence_score'  => null,
                    'all_predictions'   => null,
                    'model_used'        => 'plantix-ai-v1',
                    'status'            => 'pending',
                    'user_description'  => 'Yellow spots noticed on leaves. Need expert review.',
                    'created_at'        => $now->copy()->subDays(rand(1, 10)),
                    'updated_at'        => $now,
                ]);
            } else {
                $reportId = DB::table('crop_disease_reports')->insertGetId([
                    'user_id'           => $userId,
                    'crop_name'         => $diseaseData['crop_name'],
                    'image_path'        => 'disease_reports/report_' . ($idx + 1) . '.jpg',
                    'detected_disease'  => $diseaseData['detected_disease'],
                    'confidence_score'  => $diseaseData['confidence_score'],
                    'all_predictions'   => json_encode($diseaseData['all_predictions']),
                    'model_used'        => 'plantix-ai-v1',
                    'status'            => 'processed',
                    'user_description'  => $diseaseData['description'],
                    'created_at'        => $now->copy()->subDays(rand(5, 100)),
                    'updated_at'        => $now,
                ]);

                // Insert suggestion
                $sug = $diseaseData['suggestion'];
                DB::table('disease_suggestions')->insert([
                    'report_id'            => $reportId,
                    'disease_name'         => $sug['disease_name'],
                    'description'          => $sug['description'],
                    'organic_treatment'    => $sug['organic_treatment'],
                    'chemical_treatment'   => $sug['chemical_treatment'],
                    'preventive_measures'  => $sug['preventive_measures'],
                    'recommended_products' => null,
                    'expert_verified'      => $sug['expert_verified'] ? 1 : 0,
                    'verified_by'          => $sug['expert_verified'] ? $expertId : null,
                    'created_at'           => $now->copy()->subDays(rand(1, 90)),
                    'updated_at'           => $now,
                ]);
            }
        }
    }
}
