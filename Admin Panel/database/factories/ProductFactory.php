<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $price = $this->faker->randomFloat(2, 50, 500);

        return [
            'vendor_id' => Vendor::factory(),
            'name' => ucfirst($name),
            'sku' => strtoupper($this->faker->bothify('SKU-#####')),
            'slug' => Str::slug($name . '-' . $this->faker->unique()->numberBetween(100, 9999)),
            'description' => $this->faker->sentence(),
            'price' => $price,
            'discount_price' => null,
            'is_active' => true,
            'status' => Product::STATUS_ACTIVE,
            'stock_quantity' => $this->faker->numberBetween(5, 50),
            'track_stock' => true,
            'is_featured' => false,
            'is_returnable' => true,
            'is_refundable' => true,
            'return_window_days' => 7,
            'rating_avg' => 0.00,
            'rating_count' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'is_active' => true,
            'status' => Product::STATUS_ACTIVE,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
            'status' => Product::STATUS_INACTIVE,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'stock_quantity' => 0,
        ]);
    }
}
