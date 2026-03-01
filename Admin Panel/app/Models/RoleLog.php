<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RoleLog — immutable audit trail for all RBAC changes.
 * Every role assignment, removal, and permission change is recorded here.
 */
class RoleLog extends Model
{
    const UPDATED_AT = null;

    const ACTION_ROLE_ASSIGNED                = 'role_assigned';
    const ACTION_ROLE_REMOVED                 = 'role_removed';
    const ACTION_PERMISSION_ADDED             = 'permission_added';
    const ACTION_PERMISSION_REMOVED           = 'permission_removed';
    const ACTION_ROLE_CREATED                 = 'role_created';
    const ACTION_ROLE_DELETED                 = 'role_deleted';
    const ACTION_SUPER_ADMIN_BLOCK            = 'super_admin_escalation_blocked';

    protected $table = 'role_logs';

    protected $fillable = [
        'target_user_id',
        'actor_id',
        'action',
        'old_value',
        'new_value',
        'ip_address',
        'context',
    ];

    protected $casts = [
        'context'    => 'array',
        'created_at' => 'datetime',
    ];

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
