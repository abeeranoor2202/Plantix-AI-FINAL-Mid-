<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $experts = Expert::query()
            ->with(['user:id,name', 'profile:expert_id,specialization,website,linkedin,contact_phone'])
            ->where('status', Expert::STATUS_APPROVED)
            ->orderByDesc('is_available')
            ->orderByDesc('rating_avg')
            ->orderByDesc('total_completed')
            ->limit(8)
            ->get();

        return view('customer.about-us', compact('experts'));
    }
}

