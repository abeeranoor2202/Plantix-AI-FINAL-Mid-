<?php

namespace Database\Factories;

use App\Models\ExpertApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpertApplication>
 */
class ExpertApplicationFactory extends Factory
{
    protected $model = ExpertApplication::class;

    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'full_name'        => $this->faker->name(),
            'specialization'   => $this->faker->randomElement([
                'Soil Science', 'Crop Disease', 'Pest Management', 'Agronomy',
            ]),
            'experience_years' => $this->faker->numberBetween(1, 30),
            'qualifications'   => $this->faker->sentence(),
            'bio'              => $this->faker->paragraph(),
            'certifications_path' => null,
            'id_document_path'    => null,
            'contact_phone'    => $this->faker->phoneNumber(),
            'city'             => $this->faker->city(),
            'country'          => $this->faker->country(),
            'website'          => null,
            'linkedin'         => null,
            'account_type'     => 'individual',
            'agency_name'      => null,
            'status'           => ExpertApplication::STATUS_PENDING,
            'admin_notes'      => null,
            'reviewed_by'      => null,
            'reviewed_at'      => null,
        ];
    }

    // ── State helpers ─────────────────────────────────────────────────────────

    public function underReview(): static
    {
        return $this->state(fn () => [
            'status'      => ExpertApplication::STATUS_UNDER_REVIEW,
            'reviewed_by' => User::factory()->create(['role' => 'admin'])->id,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status'      => ExpertApplication::STATUS_APPROVED,
            'reviewed_by' => User::factory()->create(['role' => 'admin'])->id,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status'      => ExpertApplication::STATUS_REJECTED,
            'admin_notes' => 'Insufficient qualifications provided.',
            'reviewed_by' => User::factory()->create(['role' => 'admin'])->id,
            'reviewed_at' => now(),
        ]);
    }
}
