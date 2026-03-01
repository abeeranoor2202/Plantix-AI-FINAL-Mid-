<?php

namespace Database\Factories;

use App\Models\ForumCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ForumCategoryFactory extends Factory
{
    protected $model = ForumCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'name'        => ucwords($name),
            'slug'        => Str::slug($name),
            'description' => $this->faker->sentence(),
            'sort_order'  => $this->faker->numberBetween(1, 20),
            'is_active'   => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
