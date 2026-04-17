<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 1000);
        $delivery = $this->faker->randomFloat(2, 0, 20);
        $discount = $this->faker->randomFloat(2, 0, 50);
        $total = max(0, $subtotal + $delivery - $discount);

        return [
            'order_number' => 'ORD-' . strtoupper($this->faker->bothify('######')),
            'user_id' => User::factory()->state(['role' => 'user']),
            'vendor_id' => Vendor::factory(),
            'status' => Order::STATUS_PENDING,
            'subtotal' => $subtotal,
            'delivery_fee' => $delivery,
            'tax_amount' => 0,
            'discount_amount' => $discount,
            'total' => $total,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'delivery_address' => $this->faker->address(),
            'dispute_status' => Order::DISPUTE_NONE,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => Order::STATUS_CONFIRMED]);
    }

    public function delivered(): static
    {
        return $this->state(fn () => [
            'status' => Order::STATUS_DELIVERED,
            'payment_status' => 'paid',
            'delivered_at' => now(),
        ]);
    }
}
