<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformActivity;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminActivityExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        $query = PlatformActivity::query()->with('actor')->latest('created_at');

        if ($request->filled('actor_role')) {
            $query->where('actor_role', (string) $request->input('actor_role'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . (string) $request->input('action') . '%');
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', (string) $request->input('entity_type'));
        }

        if ($request->filled('q')) {
            $term = (string) $request->input('q');
            $query->where(function ($q) use ($term) {
                $q->where('action', 'like', "%{$term}%")
                    ->orWhere('entity_type', 'like', "%{$term}%")
                    ->orWhere('context', 'like', "%{$term}%");
            });
        }

        $filename = 'platform-activity-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['created_at', 'actor_name', 'actor_role', 'action', 'entity_type', 'entity_id', 'context']);

            $query->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        optional($row->created_at)->toDateTimeString(),
                        $row->actor?->name ?? 'System',
                        $row->actor_role ?? 'system',
                        $row->action,
                        $row->entity_type,
                        $row->entity_id,
                        json_encode($row->context ?? [], JSON_UNESCAPED_SLASHES),
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
