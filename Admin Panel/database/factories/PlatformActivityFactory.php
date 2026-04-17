<?php

namespace Database\Factories;

use App\Models\PlatformActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformActivity>
 */
class PlatformActivityFactory extends Factory
{
    protected $model = PlatformActivity::class;

    public function definition(): array
    {
        return [
            'actor_user_id' => User::factory(),
            'actor_role' => 'user',
            'action' => 'test.action',
            'entity_type' => 'entity',
            'entity_id' => $this->faker->numberBetween(1, 9999),
            'context' => ['source' => 'factory'],
            'created_at' => now(),
        ];
    }
}
