<?php

namespace App\Services\Api\V1;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AppointmentApiService
{
    public function listForActor(User $actor, array $filters, int $limit): LengthAwarePaginator
    {
        $query = Appointment::query()->with(['user:id,name,email', 'expert.user:id,name,email']);

        if ($actor->role === 'expert' || $actor->role === 'agency_expert') {
            $expertId = (int) optional($actor->expert)->id;
            $query->where('expert_id', $expertId);
        } elseif ($actor->role !== 'admin') {
            $query->where('user_id', $actor->id);
        }

        if (! empty($filters['search'])) {
            $term = (string) $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('topic', 'like', '%' . $term . '%')
                    ->orWhere('notes', 'like', '%' . $term . '%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%' . $term . '%'))
                    ->orWhereHas('expert.user', fn ($eq) => $eq->where('name', 'like', '%' . $term . '%'));
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('scheduled_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('scheduled_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($limit);
    }
}
