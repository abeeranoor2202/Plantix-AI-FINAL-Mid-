<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'sender_id' => null,
            'receiver_id' => User::factory(),
            'role' => 'user',
            'title' => $this->faker->sentence(4),
            'message' => $this->faker->sentence(8),
            'status' => 'unread',
            'action_url' => null,
            'metadata' => [],
            'dedup_key' => $this->faker->uuid(),
            'read' => false,
            'read_at' => null,
            'sent_at' => now(),
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => [
            'status' => 'read',
            'read' => true,
            'read_at' => now(),
        ]);
    }
}
