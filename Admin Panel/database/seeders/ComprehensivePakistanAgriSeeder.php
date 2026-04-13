<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ComprehensivePakistanAgriSeeder extends Seeder
{
    private Carbon $now;

    /** @var array<string, array<int, string>> */
    private array $columnCache = [];

    public function run(): void
    {
        $this->now = Carbon::now();

        $adminId = $this->resolveAdminId();
        $users = $this->seedPakistaniUsers();

        $this->seedAddresses($users);
        $this->seedLocations($users);
        $this->seedSeasonalData();
        $this->seedFarmProfilesAndSoilTests($users);
        $this->seedWeatherSignals($users);

        if ($this->command) {
            $this->command->info('ComprehensivePakistanAgriSeeder completed.');
            $this->command->info('Users seeded/updated: ' . count($users));
            $this->command->info('Reference admin ID: ' . ($adminId ?? 'N/A'));
        }
    }

    private function resolveAdminId(): ?int
    {
        if (! $this->tableExists('users')) {
            return null;
        }

        $adminId = DB::table('users')->where('email', 'admin@gmail.com')->value('id');

        return $adminId ? (int) $adminId : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function seedPakistaniUsers(): array
    {
        if (! $this->tableExists('users')) {
            return [];
        }

        $profiles = [
            ['name' => 'Muhammad Bilal', 'email' => 'bilal.okara@kissan.pk', 'phone' => '+923001110001', 'city' => 'Okara', 'state' => 'Punjab', 'lat' => 30.8081, 'lng' => 73.4458, 'farm' => 'Green Belt Farm', 'crop' => 'Wheat'],
            ['name' => 'Ayesha Nawaz', 'email' => 'ayesha.sahiwal@kissan.pk', 'phone' => '+923001110002', 'city' => 'Sahiwal', 'state' => 'Punjab', 'lat' => 30.6682, 'lng' => 73.1114, 'farm' => 'Rural Harvest Fields', 'crop' => 'Rice'],
            ['name' => 'Ahmed Raza', 'email' => 'ahmed.faisalabad@kissan.pk', 'phone' => '+923001110003', 'city' => 'Faisalabad', 'state' => 'Punjab', 'lat' => 31.4504, 'lng' => 73.1350, 'farm' => 'Canal View Agro', 'crop' => 'Sugarcane'],
            ['name' => 'Sana Tariq', 'email' => 'sana.multan@kissan.pk', 'phone' => '+923001110004', 'city' => 'Multan', 'state' => 'Punjab', 'lat' => 30.1575, 'lng' => 71.5249, 'farm' => 'Mango Crest Orchard', 'crop' => 'Mango'],
            ['name' => 'Usman Khalid', 'email' => 'usman.rahimyarkhan@kissan.pk', 'phone' => '+923001110005', 'city' => 'Rahim Yar Khan', 'state' => 'Punjab', 'lat' => 28.4202, 'lng' => 70.2952, 'farm' => 'Desert Edge Farm', 'crop' => 'Cotton'],
            ['name' => 'Hina Javed', 'email' => 'hina.bahawalpur@kissan.pk', 'phone' => '+923001110006', 'city' => 'Bahawalpur', 'state' => 'Punjab', 'lat' => 29.3956, 'lng' => 71.6836, 'farm' => 'Cholistan Agro Hub', 'crop' => 'Sunflower'],
            ['name' => 'Farhan Ali', 'email' => 'farhan.gujranwala@kissan.pk', 'phone' => '+923001110007', 'city' => 'Gujranwala', 'state' => 'Punjab', 'lat' => 32.1877, 'lng' => 74.1945, 'farm' => 'Khadar Plains Farm', 'crop' => 'Maize'],
            ['name' => 'Maria Iqbal', 'email' => 'maria.sukkur@kissan.pk', 'phone' => '+923001110008', 'city' => 'Sukkur', 'state' => 'Sindh', 'lat' => 27.7060, 'lng' => 68.8481, 'farm' => 'Indus Delta Growers', 'crop' => 'Rice'],
            ['name' => 'Noman Sheikh', 'email' => 'noman.larkana@kissan.pk', 'phone' => '+923001110009', 'city' => 'Larkana', 'state' => 'Sindh', 'lat' => 27.5615, 'lng' => 68.2264, 'farm' => 'Kirthar Farm Estate', 'crop' => 'Rice'],
            ['name' => 'Kiran Bibi', 'email' => 'kiran.hyderabad@kissan.pk', 'phone' => '+923001110010', 'city' => 'Hyderabad', 'state' => 'Sindh', 'lat' => 25.3960, 'lng' => 68.3578, 'farm' => 'Lower Sindh Agro', 'crop' => 'Banana'],
            ['name' => 'Arsalan Qureshi', 'email' => 'arsalan.mirpurkhas@kissan.pk', 'phone' => '+923001110011', 'city' => 'Mirpur Khas', 'state' => 'Sindh', 'lat' => 25.5276, 'lng' => 69.0126, 'farm' => 'Fruit Valley Farms', 'crop' => 'Mango'],
            ['name' => 'Rabia Noor', 'email' => 'rabia.peshawar@kissan.pk', 'phone' => '+923001110012', 'city' => 'Peshawar', 'state' => 'Khyber Pakhtunkhwa', 'lat' => 34.0151, 'lng' => 71.5249, 'farm' => 'Frontier Agro Park', 'crop' => 'Maize'],
            ['name' => 'Zainab Malik', 'email' => 'zainab.mardan@kissan.pk', 'phone' => '+923001110013', 'city' => 'Mardan', 'state' => 'Khyber Pakhtunkhwa', 'lat' => 34.1989, 'lng' => 72.0401, 'farm' => 'Swabi Plain Farms', 'crop' => 'Tobacco'],
            ['name' => 'Taimoor Khan', 'email' => 'taimoor.swat@kissan.pk', 'phone' => '+923001110014', 'city' => 'Mingora', 'state' => 'Khyber Pakhtunkhwa', 'lat' => 34.7795, 'lng' => 72.3629, 'farm' => 'Swat Orchard Collective', 'crop' => 'Peach'],
            ['name' => 'Jawad Hussain', 'email' => 'jawad.quetta@kissan.pk', 'phone' => '+923001110015', 'city' => 'Quetta', 'state' => 'Balochistan', 'lat' => 30.1798, 'lng' => 66.9750, 'farm' => 'Highland Apple Farm', 'crop' => 'Apple'],
            ['name' => 'Mehwish Awan', 'email' => 'mehwish.kalat@kissan.pk', 'phone' => '+923001110016', 'city' => 'Kalat', 'state' => 'Balochistan', 'lat' => 29.0266, 'lng' => 66.5936, 'farm' => 'Cold Valley Agriculture', 'crop' => 'Grapes'],
            ['name' => 'Sajid Mehmood', 'email' => 'sajid.gilgit@kissan.pk', 'phone' => '+923001110017', 'city' => 'Gilgit', 'state' => 'Gilgit-Baltistan', 'lat' => 35.9208, 'lng' => 74.3148, 'farm' => 'Northern Fruit Fields', 'crop' => 'Apricot'],
            ['name' => 'Fatima Zahra', 'email' => 'fatima.muzaffarabad@kissan.pk', 'phone' => '+923001110018', 'city' => 'Muzaffarabad', 'state' => 'AJK', 'lat' => 34.3700, 'lng' => 73.4700, 'farm' => 'Neelum Hill Farm', 'crop' => 'Vegetables'],
        ];

        $edgeProfiles = [
            ['name' => 'Locked Farmer', 'email' => 'locked.farmer@kissan.pk', 'phone' => '+923001110101', 'active' => 1, 'failed_login_attempts' => 6, 'locked_until' => $this->now->copy()->addHours(6)],
            ['name' => 'Inactive Farmer', 'email' => 'inactive.farmer@kissan.pk', 'phone' => '+923001110102', 'active' => 0],
            ['name' => 'Shadow Banned Farmer', 'email' => 'shadow.farmer@kissan.pk', 'phone' => '+923001110103', 'active' => 1, 'is_shadow_banned' => 1],
            ['name' => 'Temporarily Banned Farmer', 'email' => 'tempban.farmer@kissan.pk', 'phone' => '+923001110104', 'active' => 1, 'is_banned' => 1, 'banned_reason' => 'Abusive marketplace behavior', 'banned_until' => $this->now->copy()->addDays(15)],
            ['name' => 'Unverified Farmer', 'email' => 'unverified.farmer@kissan.pk', 'phone' => '+923001110105', 'active' => 1, 'email_verified_at' => null],
        ];

        $result = [];

        foreach ($profiles as $profile) {
            $payload = [
                'name' => $profile['name'],
                'email' => $profile['email'],
                'phone' => $profile['phone'],
                'password' => Hash::make('Farmer@123456'),
                'role' => 'user',
                'active' => 1,
                'wallet_amount' => random_int(1000, 250000) / 100,
                'email_verified_at' => $this->now->copy()->subDays(random_int(15, 500)),
                'password_changed_at' => $this->now->copy()->subDays(random_int(5, 180)),
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'is_banned' => 0,
                'is_shadow_banned' => 0,
                'created_at' => $this->now->copy()->subDays(random_int(30, 700)),
                'updated_at' => $this->now,
            ];

            $this->upsertByEmail($payload);
            $userId = (int) DB::table('users')->where('email', $profile['email'])->value('id');

            $result[] = [
                'id' => $userId,
                'name' => $profile['name'],
                'email' => $profile['email'],
                'city' => $profile['city'],
                'state' => $profile['state'],
                'lat' => $profile['lat'],
                'lng' => $profile['lng'],
                'farm' => $profile['farm'],
                'crop' => $profile['crop'],
            ];
        }

        foreach ($edgeProfiles as $edge) {
            $payload = [
                'name' => $edge['name'],
                'email' => $edge['email'],
                'phone' => $edge['phone'],
                'password' => Hash::make('Farmer@123456'),
                'role' => 'user',
                'active' => $edge['active'] ?? 1,
                'wallet_amount' => random_int(0, 10000) / 100,
                'email_verified_at' => $edge['email_verified_at'] ?? $this->now,
                'failed_login_attempts' => $edge['failed_login_attempts'] ?? 0,
                'locked_until' => $edge['locked_until'] ?? null,
                'is_banned' => $edge['is_banned'] ?? 0,
                'banned_reason' => $edge['banned_reason'] ?? null,
                'banned_until' => $edge['banned_until'] ?? null,
                'is_shadow_banned' => $edge['is_shadow_banned'] ?? 0,
                'created_at' => $this->now->copy()->subDays(random_int(1, 120)),
                'updated_at' => $this->now,
            ];

            $this->upsertByEmail($payload);
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $users
     */
    private function seedAddresses(array $users): void
    {
        if (! $this->tableExists('user_addresses')) {
            return;
        }

        foreach ($users as $index => $user) {
            DB::table('user_addresses')->updateOrInsert(
                [
                    'user_id' => $user['id'],
                    'label' => 'Home',
                ],
                $this->filterPayload('user_addresses', [
                    'user_id' => $user['id'],
                    'label' => 'Home',
                    'address_line1' => ($index + 10) . ' Chak Road, ' . $user['city'],
                    'address_line2' => 'Near Main Bazar',
                    'city' => $user['city'],
                    'state' => $user['state'],
                    'zip' => (string) random_int(10000, 99999),
                    'country' => 'Pakistan',
                    'lat' => $user['lat'],
                    'lng' => $user['lng'],
                    'is_default' => 1,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ])
            );
        }
    }

    /**
     * @param array<int, array<string, mixed>> $users
     */
    private function seedLocations(array $users): void
    {
        if (! $this->tableExists('user_locations')) {
            return;
        }

        foreach ($users as $user) {
            DB::table('user_locations')->updateOrInsert(
                [
                    'user_id' => $user['id'],
                    'city' => $user['city'],
                ],
                $this->filterPayload('user_locations', [
                    'user_id' => $user['id'],
                    'label' => 'farm',
                    'city' => $user['city'],
                    'region' => $user['state'],
                    'country' => 'Pakistan',
                    'latitude' => $user['lat'],
                    'longitude' => $user['lng'],
                    'is_primary' => 1,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ])
            );
        }
    }

    private function seedSeasonalData(): void
    {
        if (! $this->tableExists('seasonal_data')) {
            return;
        }

        $rows = [
            ['season' => 'Rabi', 'region' => 'Punjab', 'crop_name' => 'Wheat', 'sowing_months' => 'October-November', 'harvesting_months' => 'March-April', 'water_requirement_mm' => 420, 'soil_type_compatibility' => 'Loamy,Clay Loam', 'min_temp_celsius' => '10', 'max_temp_celsius' => '25', 'avg_yield_tons_per_acre' => 1.65, 'notes' => 'Stable Rabi staple crop in canal irrigated belt.'],
            ['season' => 'Kharif', 'region' => 'Punjab', 'crop_name' => 'Basmati Rice', 'sowing_months' => 'June', 'harvesting_months' => 'October', 'water_requirement_mm' => 1100, 'soil_type_compatibility' => 'Clay Loam', 'min_temp_celsius' => '22', 'max_temp_celsius' => '35', 'avg_yield_tons_per_acre' => 2.20, 'notes' => 'Export oriented aromatic rice cluster.'],
            ['season' => 'Kharif', 'region' => 'Sindh', 'crop_name' => 'IRRI Rice', 'sowing_months' => 'June-July', 'harvesting_months' => 'October-November', 'water_requirement_mm' => 1200, 'soil_type_compatibility' => 'Clay', 'min_temp_celsius' => '24', 'max_temp_celsius' => '38', 'avg_yield_tons_per_acre' => 2.45, 'notes' => 'High heat tolerant paddy in lower Indus zone.'],
            ['season' => 'Kharif', 'region' => 'Punjab', 'crop_name' => 'Cotton', 'sowing_months' => 'May-June', 'harvesting_months' => 'September-November', 'water_requirement_mm' => 700, 'soil_type_compatibility' => 'Sandy Loam', 'min_temp_celsius' => '20', 'max_temp_celsius' => '38', 'avg_yield_tons_per_acre' => 0.72, 'notes' => 'Whitefly pressure should be tracked weekly.'],
            ['season' => 'Rabi', 'region' => 'Balochistan', 'crop_name' => 'Apple', 'sowing_months' => 'February-March', 'harvesting_months' => 'August-October', 'water_requirement_mm' => 600, 'soil_type_compatibility' => 'Silty Loam', 'min_temp_celsius' => '2', 'max_temp_celsius' => '26', 'avg_yield_tons_per_acre' => 3.90, 'notes' => 'Requires winter chill hours and pruning cycle.'],
            ['season' => 'Kharif', 'region' => 'Sindh', 'crop_name' => 'Banana', 'sowing_months' => 'Round-year', 'harvesting_months' => 'Round-year', 'water_requirement_mm' => 1800, 'soil_type_compatibility' => 'Loamy', 'min_temp_celsius' => '18', 'max_temp_celsius' => '40', 'avg_yield_tons_per_acre' => 9.80, 'notes' => 'Mirpurkhas production belt, high irrigation demand.'],
            ['season' => 'Zaid', 'region' => 'Punjab', 'crop_name' => 'Watermelon', 'sowing_months' => 'February-March', 'harvesting_months' => 'May-June', 'water_requirement_mm' => 500, 'soil_type_compatibility' => 'Sandy Loam', 'min_temp_celsius' => '22', 'max_temp_celsius' => '40', 'avg_yield_tons_per_acre' => 5.80, 'notes' => 'Short cash cycle between major seasons.'],
            ['season' => 'Rabi', 'region' => 'Khyber Pakhtunkhwa', 'crop_name' => 'Potato', 'sowing_months' => 'October-November', 'harvesting_months' => 'January-February', 'water_requirement_mm' => 500, 'soil_type_compatibility' => 'Sandy Loam', 'min_temp_celsius' => '10', 'max_temp_celsius' => '22', 'avg_yield_tons_per_acre' => 5.40, 'notes' => 'Seed quality and fungal control are key for yield.'],
        ];

        foreach ($rows as $row) {
            DB::table('seasonal_data')->updateOrInsert(
                [
                    'season' => $row['season'],
                    'region' => $row['region'],
                    'crop_name' => $row['crop_name'],
                ],
                $this->filterPayload('seasonal_data', array_merge($row, [
                    'is_active' => 1,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]))
            );
        }
    }

    /**
     * @param array<int, array<string, mixed>> $users
     */
    private function seedFarmProfilesAndSoilTests(array $users): void
    {
        if (! $this->tableExists('farm_profiles') || ! $this->tableExists('soil_tests')) {
            return;
        }

        $soilProfiles = [
            ['soil_type' => 'loamy', 'water_source' => 'irrigation', 'climate_zone' => 'subtropical_humid', 'rainfall_mm' => 610, 'temperature' => 24.5, 'ph' => 7.1, 'organic' => 2.7, 'n' => 72, 'p' => 32, 'k' => 190],
            ['soil_type' => 'clay', 'water_source' => 'both', 'climate_zone' => 'subtropical_humid', 'rainfall_mm' => 890, 'temperature' => 28.2, 'ph' => 6.7, 'organic' => 2.1, 'n' => 68, 'p' => 28, 'k' => 210],
            ['soil_type' => 'sandy loam', 'water_source' => 'irrigation', 'climate_zone' => 'semi_arid', 'rainfall_mm' => 250, 'temperature' => 31.8, 'ph' => 7.8, 'organic' => 1.2, 'n' => 44, 'p' => 20, 'k' => 160],
            ['soil_type' => 'silty loam', 'water_source' => 'rain', 'climate_zone' => 'temperate', 'rainfall_mm' => 740, 'temperature' => 20.1, 'ph' => 6.5, 'organic' => 3.4, 'n' => 78, 'p' => 36, 'k' => 220],
            ['soil_type' => 'saline', 'water_source' => 'irrigation', 'climate_zone' => 'arid', 'rainfall_mm' => 180, 'temperature' => 34.5, 'ph' => 8.4, 'organic' => 0.8, 'n' => 30, 'p' => 12, 'k' => 130],
        ];

        $recommendationPatterns = [
            ['crop' => 'Wheat', 'alternatives' => ['Canola', 'Chickpea'], 'status' => 'completed'],
            ['crop' => 'Rice', 'alternatives' => ['Maize', 'Sesame'], 'status' => 'completed'],
            ['crop' => 'Cotton', 'alternatives' => ['Mung Bean', 'Sunflower'], 'status' => 'completed'],
            ['crop' => 'Potato', 'alternatives' => ['Onion', 'Tomato'], 'status' => 'pending'],
            ['crop' => 'Sorghum', 'alternatives' => ['Millet', 'Sunflower'], 'status' => 'failed'],
        ];

        foreach ($users as $index => $user) {
            $soil = $soilProfiles[$index % count($soilProfiles)];
            $pattern = $recommendationPatterns[$index % count($recommendationPatterns)];

            $farmId = DB::table('farm_profiles')->insertGetId($this->filterPayload('farm_profiles', [
                'user_id' => $user['id'],
                'farm_name' => $user['farm'],
                'location' => $user['city'] . ', ' . $user['state'],
                'farm_size_acres' => random_int(5, 90),
                'soil_type' => $soil['soil_type'],
                'water_source' => $soil['water_source'],
                'climate_zone' => $soil['climate_zone'],
                'previous_crops' => json_encode([$user['crop'], $pattern['alternatives'][0]]),
                'notes' => 'Profile generated for realistic Pakistan agriculture scenario coverage.',
                'created_at' => $this->now->copy()->subDays(random_int(30, 500)),
                'updated_at' => $this->now,
            ]));

            $soilTestId = DB::table('soil_tests')->insertGetId($this->filterPayload('soil_tests', [
                'user_id' => $user['id'],
                'farm_profile_id' => $farmId,
                'nitrogen' => $soil['n'],
                'phosphorus' => $soil['p'],
                'potassium' => $soil['k'],
                'ph_level' => $soil['ph'],
                'organic_matter' => $soil['organic'],
                'humidity' => random_int(35, 82),
                'rainfall_mm' => $soil['rainfall_mm'],
                'temperature' => $soil['temperature'],
                'lab_report' => 'soil-reports/' . $user['id'] . '-baseline.pdf',
                'tested_at' => $this->now->copy()->subDays(random_int(3, 90))->toDateString(),
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]));

            $this->seedCropRecommendation($user['id'], $soilTestId, $soil, $pattern);
            $this->seedCropPlan($user['id'], $farmId, $pattern['crop'], $index);
            $this->seedFertilizerRecommendation($user['id'], $soilTestId, $pattern['crop'], $soil, $index);
            $this->seedDiseaseReportAndSuggestion($user['id'], $pattern['crop'], $index);
        }
    }

    /**
     * @param array<string, mixed> $soil
     * @param array<string, mixed> $pattern
     */
    private function seedCropRecommendation(int $userId, int $soilTestId, array $soil, array $pattern): void
    {
        if (! $this->tableExists('crop_recommendations')) {
            return;
        }

        $status = (string) $pattern['status'];

        $recommendations = [
            ['name' => $pattern['crop'], 'confidence' => $status === 'completed' ? 90.0 : 62.5, 'notes' => 'Best fit based on nutrient profile and district climate.'],
            ['name' => $pattern['alternatives'][0], 'confidence' => 74.0, 'notes' => 'Suitable rotation option to reduce pest cycles.'],
            ['name' => $pattern['alternatives'][1], 'confidence' => 66.5, 'notes' => 'Moderate suitability with controlled irrigation.'],
        ];

        DB::table('crop_recommendations')->insert($this->filterPayload('crop_recommendations', [
            'user_id' => $userId,
            'soil_test_id' => $soilTestId,
            'nitrogen' => $soil['n'],
            'phosphorus' => $soil['p'],
            'potassium' => $soil['k'],
            'ph_level' => $soil['ph'],
            'humidity' => random_int(40, 80),
            'rainfall_mm' => $soil['rainfall_mm'],
            'temperature' => $soil['temperature'],
            'recommended_crops' => json_encode($recommendations),
            'explanation' => $status === 'failed'
                ? 'Model confidence too low due to incomplete sensor quality and outlier nutrient values.'
                : 'Recommendation generated using historical Pakistan district crop suitability and soil metrics.',
            'model_version' => 'plantix-pk-v2',
            'status' => $status,
            'created_at' => $this->now->copy()->subDays(random_int(1, 80)),
            'updated_at' => $this->now,
        ]));
    }

    private function seedCropPlan(int $userId, int $farmId, string $crop, int $index): void
    {
        if (! $this->tableExists('crop_plans')) {
            return;
        }

        $statuses = ['draft', 'active', 'completed', 'archived'];
        $status = $statuses[$index % count($statuses)];
        $season = ['Rabi', 'Kharif', 'Zaid'][$index % 3];

        DB::table('crop_plans')->insert($this->filterPayload('crop_plans', [
            'user_id' => $userId,
            'farm_profile_id' => $farmId,
            'season' => $season,
            'year' => (int) $this->now->format('Y'),
            'primary_crop' => $crop,
            'crop_schedule' => json_encode([
                ['crop' => $crop, 'start_week' => 1, 'end_week' => 2, 'phase' => 'Land Preparation', 'notes' => 'Laser leveling and basal fertilizer'],
                ['crop' => $crop, 'start_week' => 3, 'end_week' => 6, 'phase' => 'Sowing/Transplanting', 'notes' => 'Certified seed and spacing by extension guide'],
                ['crop' => $crop, 'start_week' => 7, 'end_week' => 12, 'phase' => 'Vegetative Management', 'notes' => 'Irrigation, weed and pest monitoring'],
                ['crop' => $crop, 'start_week' => 13, 'end_week' => 18, 'phase' => 'Harvest Window', 'notes' => 'Moisture-safe harvest and storage'],
            ]),
            'water_plan' => json_encode([
                ['week' => 2, 'irrigation_mm' => 45],
                ['week' => 6, 'irrigation_mm' => 70],
                ['week' => 10, 'irrigation_mm' => 65],
                ['week' => 14, 'irrigation_mm' => 40],
            ]),
            'expected_yield_tons' => random_int(12, 90) / 10,
            'estimated_revenue' => random_int(180000, 1200000),
            'soil_suitability_notes' => 'Slight micronutrient correction advised with zinc and boron in split doses.',
            'recommendations' => 'Adopt integrated pest management and alternate wetting/drying where applicable.',
            'status' => $status,
            'created_at' => $this->now->copy()->subDays(random_int(1, 150)),
            'updated_at' => $this->now,
        ]));
    }

    /**
     * @param array<string, mixed> $soil
     */
    private function seedFertilizerRecommendation(int $userId, int $soilTestId, string $crop, array $soil, int $index): void
    {
        if (! $this->tableExists('fertilizer_recommendations')) {
            return;
        }

        $stage = ['seedling', 'vegetative', 'flowering', 'fruiting'][$index % 4];

        $plan = [
            ['name' => 'Urea', 'type' => 'nitrogen', 'dose_kg_per_acre' => 30 + ($index % 3) * 10, 'timing' => 'Split at 20 and 40 DAS', 'notes' => 'Do not apply before heavy rainfall'],
            ['name' => 'DAP', 'type' => 'phosphorus', 'dose_kg_per_acre' => 18 + ($index % 2) * 7, 'timing' => 'Basal at sowing', 'notes' => 'Band placement improves uptake'],
            ['name' => 'SOP', 'type' => 'potassium', 'dose_kg_per_acre' => 10 + ($index % 4) * 5, 'timing' => 'At flowering', 'notes' => 'Improves fruit/fiber quality'],
        ];

        $cost = 0.0;
        foreach ($plan as $item) {
            $cost += $item['dose_kg_per_acre'] * 180;
        }

        DB::table('fertilizer_recommendations')->insert($this->filterPayload('fertilizer_recommendations', [
            'user_id' => $userId,
            'soil_test_id' => $soilTestId,
            'crop_type' => $crop,
            'growth_stage' => $stage,
            'nitrogen' => $soil['n'],
            'phosphorus' => $soil['p'],
            'potassium' => $soil['k'],
            'ph_level' => $soil['ph'],
            'temperature' => $soil['temperature'],
            'humidity' => random_int(45, 82),
            'fertilizer_plan' => json_encode($plan),
            'application_instructions' => 'Apply in split doses, calibrate spreader, and irrigate lightly after top-dressing.',
            'estimated_cost_pkr' => round($cost, 2),
            'model_version' => 'plantix-fertilizer-pk-v2',
            'created_at' => $this->now->copy()->subDays(random_int(1, 90)),
            'updated_at' => $this->now,
        ]));
    }

    private function seedDiseaseReportAndSuggestion(int $userId, string $crop, int $index): void
    {
        if (! $this->tableExists('crop_disease_reports')) {
            return;
        }

        $scenarios = [
            ['status' => 'processed', 'disease' => 'Leaf Rust', 'confidence' => 91.4],
            ['status' => 'manual_review', 'disease' => 'Unknown Lesion Pattern', 'confidence' => 51.3],
            ['status' => 'failed', 'disease' => null, 'confidence' => null],
            ['status' => 'pending', 'disease' => null, 'confidence' => null],
        ];

        $scenario = $scenarios[$index % count($scenarios)];

        $reportId = DB::table('crop_disease_reports')->insertGetId($this->filterPayload('crop_disease_reports', [
            'user_id' => $userId,
            'crop_name' => $crop,
            'image_path' => 'disease-reports/pk-case-' . $userId . '.jpg',
            'detected_disease' => $scenario['disease'],
            'confidence_score' => $scenario['confidence'],
            'all_predictions' => $scenario['disease']
                ? json_encode([
                    ['disease' => $scenario['disease'], 'confidence' => $scenario['confidence']],
                    ['disease' => 'Nutrient Deficiency', 'confidence' => 24.0],
                ])
                : null,
            'model_used' => 'plantix-vision-v3',
            'status' => $scenario['status'],
            'user_description' => 'Farmer reported yellowing and spot spread after irrigation cycle.',
            'created_at' => $this->now->copy()->subDays(random_int(1, 45)),
            'updated_at' => $this->now,
        ]));

        if ($scenario['status'] !== 'processed' || ! $this->tableExists('disease_suggestions')) {
            return;
        }

        DB::table('disease_suggestions')->insert($this->filterPayload('disease_suggestions', [
            'report_id' => $reportId,
            'disease_name' => (string) $scenario['disease'],
            'description' => 'Fungal pressure elevated due to humid canopy and late irrigation timing.',
            'organic_treatment' => 'Neem extract spray 5 ml/L and remove heavily infected leaves.',
            'chemical_treatment' => 'Use Propiconazole or Mancozeb as per local extension dose guidance.',
            'preventive_measures' => 'Improve airflow, avoid night irrigation, and rotate fungicide chemistry.',
            'recommended_products' => json_encode([]),
            'expert_verified' => 1,
            'verified_by' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]));
    }

    /**
     * @param array<int, array<string, mixed>> $users
     */
    private function seedWeatherSignals(array $users): void
    {
        if ($this->tableExists('weather_logs')) {
            $this->seedWeatherLogs($users);
        }

        if ($this->tableExists('weather_alert_logs')) {
            $this->seedWeatherAlerts($users);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $users
     */
    private function seedWeatherLogs(array $users): void
    {
        foreach ($users as $index => $user) {
            DB::table('weather_logs')->insert($this->filterPayload('weather_logs', [
                'city' => $user['city'],
                'latitude' => $user['lat'],
                'longitude' => $user['lng'],
                'temperature_c' => match ($user['city']) {
                    'Jacobabad' => 46.5,
                    'Quetta' => 8.0,
                    default => random_int(18, 43),
                },
                'feels_like_c' => random_int(20, 48),
                'humidity' => random_int(28, 88),
                'wind_speed_kmh' => random_int(4, 38),
                'wind_direction' => ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'][$index % 8],
                'rainfall_mm' => random_int(0, 120),
                'uv_index' => random_int(2, 12),
                'condition' => ['sunny', 'partly_cloudy', 'cloudy', 'rain', 'dusty'][$index % 5],
                'icon_code' => '10d',
                'hourly_forecast' => json_encode([
                    ['hour' => '09:00', 'temp' => random_int(20, 34), 'condition' => 'sunny'],
                    ['hour' => '15:00', 'temp' => random_int(29, 42), 'condition' => 'hot'],
                    ['hour' => '21:00', 'temp' => random_int(18, 30), 'condition' => 'clear'],
                ]),
                'daily_forecast' => json_encode([
                    ['date' => $this->now->toDateString(), 'min' => random_int(15, 27), 'max' => random_int(29, 44), 'condition' => 'clear'],
                    ['date' => $this->now->copy()->addDay()->toDateString(), 'min' => random_int(16, 28), 'max' => random_int(30, 45), 'condition' => 'cloudy'],
                ]),
                'raw_response' => json_encode(['source' => 'seeded', 'country' => 'PK']),
                'has_alert' => ($index % 4 === 0) ? 1 : 0,
                'alert_message' => ($index % 4 === 0) ? 'Potential crop stress weather event expected.' : null,
                'fetched_at' => $this->now,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]));
        }
    }

    /**
     * @param array<int, array<string, mixed>> $users
     */
    private function seedWeatherAlerts(array $users): void
    {
        $alerts = [
            ['alert_type' => 'heat_stress', 'severity' => 'high', 'message' => 'Heatwave likely in lower Sindh. Schedule irrigation early morning.'],
            ['alert_type' => 'heavy_rain', 'severity' => 'medium', 'message' => 'Expected rain spell in central Punjab. Delay urea top-dressing.'],
            ['alert_type' => 'frost_alert', 'severity' => 'high', 'message' => 'Night frost risk in highland orchards. Use smoke and micro-sprinklers.'],
            ['alert_type' => 'wind_advisory', 'severity' => 'low', 'message' => 'Strong wind may affect pesticide spray efficiency.'],
        ];

        foreach (array_slice($users, 0, 8) as $index => $user) {
            $alert = $alerts[$index % count($alerts)];

            DB::table('weather_alert_logs')->insert($this->filterPayload('weather_alert_logs', [
                'user_id' => $user['id'],
                'city' => $user['city'],
                'alert_type' => $alert['alert_type'],
                'severity' => $alert['severity'],
                'message' => $alert['message'],
                'temperature_c' => random_int(2, 47),
                'notification_sent' => 1,
                'notified_at' => $this->now->copy()->subHours(random_int(1, 24)),
                'valid_from' => $this->now->copy()->subHours(2),
                'valid_until' => $this->now->copy()->addHours(18),
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]));
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function upsertByEmail(array $payload): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => $payload['email']],
            $this->filterPayload('users', $payload)
        );
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    /**
     * @return array<int, string>
     */
    private function columns(string $table): array
    {
        if (! isset($this->columnCache[$table])) {
            $this->columnCache[$table] = $this->tableExists($table)
                ? Schema::getColumnListing($table)
                : [];
        }

        return $this->columnCache[$table];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function filterPayload(string $table, array $payload): array
    {
        $columns = $this->columns($table);

        $filtered = [];
        foreach ($payload as $key => $value) {
            if (in_array($key, $columns, true)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
