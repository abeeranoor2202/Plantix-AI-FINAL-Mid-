<?php

namespace App\Services\Api\V1;

use App\Models\ForumThread;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ForumApiService
{
    public function list(array $filters, int $limit): LengthAwarePaginator
    {
        $query = ForumThread::query()
            ->with(['user:id,name', 'category:id,name,slug'])
            ->approved();

        if (! empty($filters['search'])) {
            $term = (string) $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('title', 'like', '%' . $term . '%')
                    ->orWhere('body', 'like', '%' . $term . '%');
            });
        }

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $sortBy = $filters['sort_by'] ?? 'latest';
        if ($sortBy === 'popular') {
            $query->orderByDesc('replies_count');
        } elseif ($sortBy === 'oldest') {
            $query->orderBy('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        return $query->orderByDesc('is_pinned')->paginate($limit);
    }

    public function detail(ForumThread $thread): ForumThread
    {
        return $thread->load([
            'user:id,name',
            'category:id,name,slug',
            'replies.user:id,name',
        ]);
    }
}
