<?php

namespace App\Services\Vendor;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(
        ?int $actorId,
        Model $auditable,
        string $action,
        ?array $beforeState = null,
        ?array $afterState = null,
        array $meta = [],
        ?Request $request = null,
    ): AuditLog {
        return AuditLog::create([
            'actor_id'     => $actorId,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
            'action'       => $action,
            'before_state'  => $beforeState,
            'after_state'   => $afterState,
            'meta'         => $meta ?: null,
            'ip_address'   => $request?->ip(),
            'user_agent'   => $request?->userAgent(),
            'created_at'   => now(),
        ]);
    }
}
