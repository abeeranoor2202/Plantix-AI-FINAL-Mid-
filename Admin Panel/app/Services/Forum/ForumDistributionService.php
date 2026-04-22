<?php

namespace App\Services\Forum;

use App\Models\Expert;
use App\Models\ForumThread;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ForumDistributionService
{
    public function extractTags(string $title, string $body, ?string $categoryName = null): array
    {
        $seed = trim($title . ' ' . $body . ' ' . ($categoryName ?? ''));
        $seed = Str::lower($seed);

        $dictionary = [
            'soil' => ['soil', 'fertility', 'ph', 'nutrient', 'nutrients', 'organic matter'],
            'fertilizer' => ['fertilizer', 'urea', 'npk', 'dap', 'potash', 'compost'],
            'crop disease' => ['disease', 'blight', 'fungus', 'mildew', 'rust', 'spot'],
            'pests' => ['pest', 'insect', 'aphid', 'worm', 'caterpillar', 'borer'],
            'irrigation' => ['irrigation', 'water stress', 'drip', 'sprinkler', 'canal'],
            'wheat' => ['wheat'],
            'rice' => ['rice', 'paddy'],
            'cotton' => ['cotton'],
            'sugarcane' => ['sugarcane'],
        ];

        $tags = [];
        foreach ($dictionary as $tag => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($seed, Str::lower($keyword))) {
                    $tags[] = $tag;
                    break;
                }
            }
        }

        if (empty($tags) && ! empty($categoryName)) {
            $tags[] = Str::lower(trim($categoryName));
        }

        return array_values(array_unique($tags));
    }

    /**
     * @return Collection<int, Expert>
     */
    public function resolveRelevantExperts(ForumThread $thread): Collection
    {
        $tags = collect((array) ($thread->tags ?? []))
            ->map(fn ($tag) => Str::lower((string) $tag))
            ->filter()
            ->values();

        $baseQuery = Expert::query()
            ->with(['user:id,name,email,role,active,status,notification_preferences', 'profile:expert_id,specialization'])
            ->where('status', Expert::STATUS_APPROVED)
            ->where('is_available', true)
            ->whereHas('user', fn ($q) => $q->where('active', true)->whereNull('deleted_at'));

        if ($tags->isEmpty()) {
            return $baseQuery->get();
        }

        $relevant = (clone $baseQuery)
            ->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $needle = '%' . $tag . '%';
                    $q->orWhere('specialty', 'like', $needle)
                        ->orWhereHas('profile', fn ($p) => $p->where('specialization', 'like', $needle))
                        ->orWhereHas('specializations', fn ($s) => $s->where('name', 'like', $needle));
                }
            })
            ->get();

        if ($relevant->isNotEmpty()) {
            return $relevant;
        }

        return $baseQuery->get();
    }
}

