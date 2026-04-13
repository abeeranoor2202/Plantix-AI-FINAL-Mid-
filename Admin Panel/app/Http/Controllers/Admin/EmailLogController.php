<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\View\View;

class EmailLogController extends Controller
{
    public function index(): View
    {
        $logs = NotificationLog::query()
            ->latest()
            ->paginate(20);

        return view('admin.email-logs.index', compact('logs'));
    }
}