<?php

namespace App\Services\Platform;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UniversalSearchService
{
    public function search(User $actor, string $term, int $limit = 8): array
    {
        $term = trim($term);
        $limit = max(1, min($limit, 20));

        return [
            'users' => $this->users($term, $limit),
            'orders' => $this->orders($actor, $term, $limit),
            'appointments' => $this->appointments($actor, $term, $limit),
            'forum_threads' => $this->forumThreads($term, $limit),
            'products' => $this->products($actor, $term, $limit),
        ];
    }

    private function users(string $term, int $limit): Collection
    {
        return DB::table('users')
            ->select('id', 'name', 'email', 'role')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->limit($limit)
            ->get();
    }

    private function orders(User $actor, string $term, int $limit): Collection
    {
        $query = DB::table('orders')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('vendors', 'orders.vendor_id', '=', 'vendors.id')
            ->select('orders.id', 'orders.order_number', 'orders.status', 'orders.total', 'users.name as customer_name', 'vendors.title as vendor_name')
            ->where(function ($q) use ($term) {
                $q->where('orders.order_number', 'like', "%{$term}%")
                    ->orWhere('users.name', 'like', "%{$term}%")
                    ->orWhere('users.email', 'like', "%{$term}%");
            });

        if ($actor->role === 'vendor') {
            $query->where('orders.vendor_id', optional($actor->vendor)->id);
        }

        if (in_array($actor->role, ['expert', 'agency_expert'], true)) {
            $query->whereRaw('1 = 0');
        }

        return $query->orderByDesc('orders.id')->limit($limit)->get();
    }

    private function appointments(User $actor, string $term, int $limit): Collection
    {
        $query = DB::table('appointments')
            ->leftJoin('users', 'appointments.user_id', '=', 'users.id')
            ->leftJoin('experts', 'appointments.expert_id', '=', 'experts.id')
            ->select('appointments.id', 'appointments.status', 'appointments.scheduled_at', 'users.name as customer_name', 'experts.id as expert_id')
            ->where(function ($q) use ($term) {
                $q->where('users.name', 'like', "%{$term}%")
                    ->orWhere('appointments.id', (int) $term);
            });

        if (in_array($actor->role, ['expert', 'agency_expert'], true)) {
            $query->where('appointments.expert_id', optional($actor->expert)->id);
        }

        if ($actor->role === 'user') {
            $query->where('appointments.user_id', $actor->id);
        }

        return $query->orderByDesc('appointments.id')->limit($limit)->get();
    }

    private function forumThreads(string $term, int $limit): Collection
    {
        return DB::table('forum_threads')
            ->leftJoin('users', 'forum_threads.user_id', '=', 'users.id')
            ->select('forum_threads.id', 'forum_threads.title', 'forum_threads.slug', 'forum_threads.status', 'users.name as author_name')
            ->where(function ($q) use ($term) {
                $q->where('forum_threads.title', 'like', "%{$term}%")
                    ->orWhere('forum_threads.body', 'like', "%{$term}%")
                    ->orWhere('users.name', 'like', "%{$term}%");
            })
            ->orderByDesc('forum_threads.id')
            ->limit($limit)
            ->get();
    }

    private function products(User $actor, string $term, int $limit): Collection
    {
        $query = DB::table('products')
            ->leftJoin('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->select('products.id', 'products.name', 'products.price', 'products.publish', 'vendors.title as vendor_name')
            ->where(function ($q) use ($term) {
                $q->where('products.name', 'like', "%{$term}%")
                    ->orWhere('products.sku', 'like', "%{$term}%")
                    ->orWhere('vendors.title', 'like', "%{$term}%");
            });

        if ($actor->role === 'vendor') {
            $query->where('products.vendor_id', optional($actor->vendor)->id);
        }

        return $query->orderByDesc('products.id')->limit($limit)->get();
    }
}
