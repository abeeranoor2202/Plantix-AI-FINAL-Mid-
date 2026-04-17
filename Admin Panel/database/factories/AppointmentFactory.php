<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Expert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'user']),
            'expert_id' => Expert::factory()->approved(),
            'scheduled_at' => now()->addDays(2),
            'duration_minutes' => 60,
            'status' => Appointment::STATUS_CONFIRMED,
            'fee' => $this->faker->randomFloat(2, 20, 150),
            'payment_status' => 'paid',
            'topic' => $this->faker->sentence(4),
            'type' => 'online',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => Appointment::STATUS_PENDING]);
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => Appointment::STATUS_CONFIRMED]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => Appointment::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }
}
