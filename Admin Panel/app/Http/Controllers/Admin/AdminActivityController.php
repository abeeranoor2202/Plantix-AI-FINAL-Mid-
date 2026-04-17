<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminActivityController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:100'],
            'entity_type' => ['nullable', 'string', 'max:100'],
            'actor_role' => ['nullable', 'string', 'max:32'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = PlatformActivity::query()->with('actor')->latest('created_at');

        if (! empty($validated['actor_role'])) {
            $query->where('actor_role', (string) $validated['actor_role']);
        }

        if (! empty($validated['action'])) {
            $query->where('action', 'like', '%' . (string) $validated['action'] . '%');
        }

        if (! empty($validated['entity_type'])) {
            $query->where('entity_type', (string) $validated['entity_type']);
        }

        if (! empty($validated['q'])) {
            $term = (string) $validated['q'];
            $query->where(function ($inner) use ($term) {
                $inner->where('action', 'like', '%' . $term . '%')
                    ->orWhere('entity_type', 'like', '%' . $term . '%')
                    ->orWhere('context', 'like', '%' . $term . '%');
            });
        }

        if (! empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (! empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $activities = $query->paginate(30)->withQueryString();

        $summary = [
            'total' => PlatformActivity::count(),
            'today' => PlatformActivity::whereDate('created_at', now()->toDateString())->count(),
            'critical' => PlatformActivity::whereIn('action', [
                'order.dispute.escalated',
                'forum.flag.confirmed',
                'user.banned',
                'vendor.suspended',
                'expert.suspended',
            ])->count(),
        ];

        return view('admin.activity.index', compact('activities', 'summary'));
    }
}
