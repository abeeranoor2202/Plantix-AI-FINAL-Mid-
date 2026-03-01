<?php

namespace Database\Factories;

use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ForumThreadFactory extends Factory
{
    protected $model = ForumThread::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6);

        return [
            'user_id'           => User::factory(),
            'forum_category_id' => ForumCategory::factory(),
            'title'             => $title,
            'slug'              => Str::slug($title) . '-' . $this->faker->unique()->randomNumber(5),
            'body'              => $this->faker->paragraphs(2, true),
            'status'            => ForumThread::STATUS_OPEN,
            'is_pinned'         => false,
            'is_approved'       => true,
            'views'             => 0,
            'replies_count'     => 0,
        ];
    }

    public function locked(): static
    {
        return $this->state(['status' => ForumThread::STATUS_LOCKED]);
    }

    public function resolved(): static
    {
        return $this->state(['status' => ForumThread::STATUS_RESOLVED]);
    }

    public function archived(): static
    {
        return $this->state(['status' => ForumThread::STATUS_ARCHIVED]);
    }

    public function pinned(): static
    {
        return $this->state(['is_pinned' => true]);
    }

    public function unapproved(): static
    {
        return $this->state(['is_approved' => false]);
    }
}
