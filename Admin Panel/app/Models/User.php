<?php

namespace App\Models;

use App\Services\Security\PermissionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role',
        'status', 'active', 'is_document_verified', 'vendor_id',
        'wallet_amount', 'fcm_token', 'profile_photo',
        'reputation_score', 'reputation_level',
        'must_reset_password', 'role_id',
        'failed_login_attempts', 'locked_until', 'last_login_at', 'last_login_ip', 'password_changed_at',
        'is_banned', 'banned_until', 'banned_reason', 'is_shadow_banned',
        'notification_preferences',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'password_changed_at'  => 'datetime',
        'last_login_at'        => 'datetime',
        'locked_until'         => 'datetime',
        'failed_login_attempts' => 'integer',
        'status'               => 'string',
        'active'               => 'boolean',
        'is_document_verified' => 'boolean',
        'must_reset_password'  => 'boolean',
        'wallet_amount'        => 'decimal:2',
        'reputation_score'     => 'integer',
        'is_banned'            => 'boolean',
        'is_shadow_banned'     => 'boolean',
        'banned_until'         => 'datetime',
        'notification_preferences' => 'array',
    ];

    // -------------------------------------------------------------------------
    // Role helpers
    // -------------------------------------------------------------------------

    public function isAdmin(): bool         { return $this->role === 'admin'; }
    public function isVendor(): bool        { return $this->role === 'vendor'; }
    public function isUser(): bool          { return $this->role === 'user'; }
    public function isExpert(): bool        { return in_array($this->role, ['expert', 'agency_expert']); }
    public function isAgencyExpert(): bool  { return $this->role === 'agency_expert'; }

    public function isActiveAccount(): bool
    {
        return ($this->status ?? 'active') === 'active' && $this->active && ! $this->isCurrentlyBanned();
    }

    public function isSuspendedAccount(): bool
    {
        return ($this->status ?? 'active') === 'suspended' || (! $this->active && ! $this->isCurrentlyBanned());
    }

    public function isBannedAccount(): bool
    {
        return ($this->status ?? 'active') === 'banned' || $this->isCurrentlyBanned();
    }

    public function setAccountStatus(string $status): void
    {
        $this->status = $status;

        if ($status === 'active') {
            $this->active = true;
            $this->is_banned = false;
            $this->banned_until = null;
            $this->banned_reason = null;
        }

        if ($status === 'suspended') {
            $this->active = false;
        }

        if ($status === 'banned') {
            $this->active = false;
            $this->is_banned = true;
        }
    }

    /**
     * True when the user is actively banned.
     * A permanent ban has null banned_until; a temporary ban checks the expiry.
     */
    public function isCurrentlyBanned(): bool
    {
        if (! $this->is_banned) {
            return false;
        }
        // Permanent ban
        if ($this->banned_until === null) {
            return true;
        }
        // Temporary ban — check expiry
        return $this->banned_until->isFuture();
    }

    /** True for shadow-banned users: they can post but content is hidden. */
    public function isShadowBanned(): bool
    {
        return (bool) $this->is_shadow_banned;
    }

    /**
     * True when the user role can create forum threads.
     * Vendors are explicitly excluded.
     */
    public function canCreateForumThread(): bool
    {
        return in_array($this->role, ['user', 'expert', 'agency_expert', 'admin'], true);
    }

    /** Check a named permission from the admin-panel role system */
    public function hasPermission(string $permission): bool
    {
        return app(PermissionService::class)->checkPermission($this, $permission);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function adminRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Additional RBAC roles for multi-role resolution.
     * Legacy role_id is still supported for backward compatibility.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')->withTimestamps();
    }

    /** The vendor store this user owns (vendor role) */
    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class, 'author_id');
    }

    /** All orders placed by this user (customer role) */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /** Orders this driver is assigned to */
    public function drivenOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'driver_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'user_id')->latest();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function favouriteVendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'favourite_vendors')
                    ->withTimestamps();
    }

    public function favouriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favourite_products')
                    ->withTimestamps();
    }

    public function bookedTables(): HasMany
    {
        return $this->hasMany(BookedTable::class, 'user_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    public function expert(): HasOne
    {
        return $this->hasOne(Expert::class, 'user_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class, 'user_id')->where('is_default', true);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class, 'user_id');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'user_id');
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(ForumThread::class, 'user_id');
    }

    public function forumReplies(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'user_id');
    }

    public function appointmentFeedback(): HasMany
    {
        return $this->hasMany(AppointmentFeedback::class, 'user_id');
    }

    public function orderDisputes(): HasMany
    {
        return $this->hasMany(OrderDispute::class, 'user_id');
    }

    // ── AI / Agriculture Relationships ──────────────────────────────────────
    public function farmProfiles(): HasMany
    {
        return $this->hasMany(FarmProfile::class, 'user_id');
    }

    public function soilTests(): HasMany
    {
        return $this->hasMany(SoilTest::class, 'user_id')->latest();
    }

    public function cropRecommendations(): HasMany
    {
        return $this->hasMany(CropRecommendation::class, 'user_id')->latest();
    }

    public function cropPlans(): HasMany
    {
        return $this->hasMany(CropPlan::class, 'user_id')->latest();
    }

    public function diseaseReports(): HasMany
    {
        return $this->hasMany(CropDiseaseReport::class, 'user_id')->latest();
    }

    public function fertilizerRecommendations(): HasMany
    {
        return $this->hasMany(FertilizerRecommendation::class, 'user_id')->latest();
    }

    // ── Weather / Chat Relationships ────────────────────────────────────────
    public function locations(): HasMany
    {
        return $this->hasMany(UserLocation::class, 'user_id');
    }

    public function primaryLocation(): HasOne
    {
        return $this->hasOne(UserLocation::class, 'user_id')->where('is_primary', true);
    }

    public function aiChatSessions(): HasMany
    {
        return $this->hasMany(AiChatSession::class, 'user_id')->latest();
    }
}
