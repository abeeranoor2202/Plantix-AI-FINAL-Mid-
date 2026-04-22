<?php

namespace App\Services\Platform;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UniversalSearchService
{
    public function search(User $actor, string $term, int $limit = 8, int $page = 1): array
    {
        $term = trim($term);
        $limit = max(1, min($limit, 20));
        $page = max(1, $page);

        return [
            'users' => $this->users($actor, $term, $limit, $page),
            'orders' => $this->orders($actor, $term, $limit, $page),
            'appointments' => $this->appointments($actor, $term, $limit, $page),
            'forum_threads' => $this->forumThreads($term, $limit, $page),
            'products' => $this->products($actor, $term, $limit, $page),
        ];
    }

    private function users(User $actor, string $term, int $limit, int $page): array
    {
        if (! in_array($actor->role, ['admin', 'vendor', 'expert', 'agency_expert'], true)) {
            return $this->emptyResult($page, $limit);
        }

        $query = DB::table('users')
            ->select('id', 'name', 'role')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->whereNull('deleted_at');

        if ($actor->role === 'admin') {
            $query->addSelect('email');
        }

        return $this->paginateBuilder($query, $page, $limit, 'users.id');
    }

    private function orders(User $actor, string $term, int $limit, int $page): array
    {
        if (in_array($actor->role, ['expert', 'agency_expert'], true)) {
            return $this->emptyResult($page, $limit);
        }

        $query = DB::table('orders')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('vendors', 'orders.vendor_id', '=', 'vendors.id')
            ->select('orders.id', 'orders.order_number', 'orders.status', 'orders.total', 'users.name as customer_name', 'vendors.title as vendor_name')
            ->where(function ($q) use ($term) {
                $q->where('orders.order_number', 'like', "%{$term}%")
                    ->orWhere('users.name', 'like', "%{$term}%")
                    ->orWhere('users.email', 'like', "%{$term}%");
                    })
                    ->whereNull('orders.deleted_at');

        if ($actor->role === 'vendor') {
            $query->where('orders.vendor_id', optional($actor->vendor)->id);
        }

        return $this->paginateBuilder($query, $page, $limit, 'orders.id');
    }

    private function appointments(User $actor, string $term, int $limit, int $page): array
    {
        $query = DB::table('appointments')
            ->leftJoin('users', 'appointments.user_id', '=', 'users.id')
            ->leftJoin('experts', 'appointments.expert_id', '=', 'experts.id')
            ->select('appointments.id', 'appointments.status', 'appointments.scheduled_at', 'users.name as customer_name', 'experts.id as expert_id')
            ->where(function ($q) use ($term) {
                $q->where('users.name', 'like', "%{$term}%")
                    ->orWhere('appointments.id', (int) $term);
            })
            ->whereNull('appointments.deleted_at');

        if (in_array($actor->role, ['expert', 'agency_expert'], true)) {
            $query->where('appointments.expert_id', optional($actor->expert)->id);
        }

        if ($actor->role === 'user') {
            $query->where('appointments.user_id', $actor->id);
        }

        return $this->paginateBuilder($query, $page, $limit, 'appointments.id');
    }

    private function forumThreads(string $term, int $limit, int $page): array
    {
        $query = DB::table('forum_threads')
            ->leftJoin('users', 'forum_threads.user_id', '=', 'users.id')
            ->select('forum_threads.id', 'forum_threads.title', 'forum_threads.slug', 'forum_threads.status', 'users.name as author_name')
            ->where(function ($q) use ($term) {
                $q->where('forum_threads.title', 'like', "%{$term}%")
                    ->orWhere('forum_threads.body', 'like', "%{$term}%")
                    ->orWhere('forum_threads.tags', 'like', "%{$term}%")
                    ->orWhere('users.name', 'like', "%{$term}%");
            })
            ->whereNull('forum_threads.deleted_at');

        return $this->paginateBuilder($query, $page, $limit, 'forum_threads.id');
    }

    private function products(User $actor, string $term, int $limit, int $page): array
    {
        $query = DB::table('products')
            ->leftJoin('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->select('products.id', 'products.name', 'products.price', 'products.publish', 'vendors.title as vendor_name')
            ->where(function ($q) use ($term) {
                $q->where('products.name', 'like', "%{$term}%")
                    ->orWhere('products.sku', 'like', "%{$term}%")
                    ->orWhere('vendors.title', 'like', "%{$term}%");
            })
            ->whereNull('products.deleted_at');

        if ($actor->role === 'vendor') {
            $query->where('products.vendor_id', optional($actor->vendor)->id);
        }

        return $this->paginateBuilder($query, $page, $limit, 'products.id');
    }

    private function paginateBuilder($query, int $page, int $limit, string $orderBy): array
    {
        $total = (clone $query)->count();
        $items = (clone $query)
            ->orderByDesc($orderBy)
            ->forPage($page, $limit)
            ->get();

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'last_page' => (int) max(1, (int) ceil($total / $limit)),
            ],
        ];
    }

    private function emptyResult(int $page, int $limit): array
    {
        return [
            'items' => collect(),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => 0,
                'last_page' => 1,
            ],
        ];
    }
}
