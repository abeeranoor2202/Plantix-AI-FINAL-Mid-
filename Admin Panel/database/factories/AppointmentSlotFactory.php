<?php

namespace Database\Factories;

use App\Models\AppointmentSlot;
use App\Models\Expert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentSlot>
 */
class AppointmentSlotFactory extends Factory
{
    protected $model = AppointmentSlot::class;

    public function definition(): array
    {
        $date = now()->addDays(1)->toDateString();

        return [
            'expert_id' => Expert::factory()->approved(),
            'date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'is_booked' => false,
            'appointment_id' => null,
        ];
    }

    public function booked(): static
    {
        return $this->state(fn () => ['is_booked' => true]);
    }
}
