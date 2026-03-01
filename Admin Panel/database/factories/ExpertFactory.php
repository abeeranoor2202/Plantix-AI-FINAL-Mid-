<?php

namespace Database\Factories;

use App\Models\Expert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expert>
 */
class ExpertFactory extends Factory
{
    protected $model = Expert::class;

    public function definition(): array
    {
        return [
            'user_id'                       => User::factory(),
            'status'                        => Expert::STATUS_PENDING,
            'specialty'                     => $this->faker->randomElement([
                'Soil Science', 'Crop Disease', 'Pest Management',
                'Organic Farming', 'Irrigation', 'Agronomy',
            ]),
            'bio'                           => $this->faker->paragraph(),
            'profile_image'                 => null,
            'is_available'                  => false,
            'hourly_rate'                   => $this->faker->randomFloat(2, 10, 200),
            'consultation_price'            => $this->faker->randomFloat(2, 20, 300),
            'consultation_duration_minutes' => $this->faker->randomElement([30, 45, 60]),
            'rating_avg'                    => null,
            'total_appointments'            => 0,
            'total_completed'               => 0,
            'total_cancelled'               => 0,
            'verified_at'                   => null,
            'suspended_at'                  => null,
            'rejection_reason'              => null,
        ];
    }

    // ── State helpers ─────────────────────────────────────────────────────────

    /** Expert who has been approved and is accepting bookings. */
    public function approved(): static
    {
        return $this->state(fn () => [
            'status'       => Expert::STATUS_APPROVED,
            'is_available' => true,
            'verified_at'  => now(),
        ]);
    }

    /** Expert under admin review. */
    public function underReview(): static
    {
        return $this->state(fn () => [
            'status' => Expert::STATUS_UNDER_REVIEW,
        ]);
    }

    /** Rejected expert. */
    public function rejected(): static
    {
        return $this->state(fn () => [
            'status'           => Expert::STATUS_REJECTED,
            'rejection_reason' => 'Insufficient qualifications.',
        ]);
    }

    /** Suspended expert. */
    public function suspended(): static
    {
        return $this->state(fn () => [
            'status'       => Expert::STATUS_SUSPENDED,
            'suspended_at' => now(),
        ]);
    }

    /** Inactive expert. */
    public function inactive(): static
    {
        return $this->state(fn () => [
            'status'       => Expert::STATUS_INACTIVE,
            'is_available' => false,
        ]);
    }

    /**
     * Attach a freshly-created User and ensure user.role = 'expert'.
     * Usage: Expert::factory()->withExpertUser()->create()
     */
    public function withExpertUser(): static
    {
        return $this->state(function () {
            $user = User::factory()->create(['role' => 'expert']);
            return ['user_id' => $user->id];
        });
    }
}
