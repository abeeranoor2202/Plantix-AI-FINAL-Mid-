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
        $query = PlatformActivity::query()->with('actor')->latest('created_at');

        if ($request->filled('actor_role')) {
            $query->where('actor_role', $request->string('actor_role'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->string('action') . '%');
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->string('entity_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from')->toDateString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to')->toDateString());
        }

        $activities = $query->paginate(30)->withQueryString();

        return view('admin.activity.index', compact('activities'));
    }
}
