<?php

namespace Database\Factories;

use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumReplyFactory extends Factory
{
    protected $model = ForumReply::class;

    public function definition(): array
    {
        return [
            'thread_id'       => ForumThread::factory(),
            'user_id'         => User::factory(),
            'parent_id'       => null,
            'body'            => $this->faker->paragraphs(1, true),
            'status'          => ForumReply::STATUS_VISIBLE,
            'is_approved'     => true,
            'is_official'     => false,
            'is_expert_reply' => false,
            'expert_id'       => null,
            'edited_at'       => null,
        ];
    }

    public function official(): static
    {
        return $this->state(['is_official' => true]);
    }

    public function flagged(): static
    {
        return $this->state(['status' => ForumReply::STATUS_FLAGGED]);
    }

    public function expertReply(): static
    {
        return $this->state(function () {
            $expert = User::factory()->create(['role' => 'expert']);
            return [
                'is_expert_reply' => true,
                'expert_id'       => $expert->id,
            ];
        });
    }

    public function childOf(ForumReply $parent): static
    {
        return $this->state([
            'thread_id' => $parent->thread_id,
            'parent_id' => $parent->id,
        ]);
    }
}
