<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'author_id' => User::factory()->state(['role' => 'vendor']),
            'title' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'address' => $this->faker->streetAddress(),
            'phone' => $this->faker->numerify('03#########'),
            'is_active' => true,
            'is_approved' => true,
            'status' => Vendor::STATUS_APPROVED,
            'delivery_fee' => $this->faker->randomFloat(2, 0, 15),
            'min_order_amount' => $this->faker->randomFloat(2, 0, 100),
            'commission_rate' => 10.00,
            'rating' => 0,
            'review_count' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => Vendor::STATUS_PENDING,
            'is_approved' => false,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => Vendor::STATUS_SUSPENDED,
            'is_active' => false,
        ]);
    }
}
