<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * FileRecord — tracks every file uploaded through FileStorageService.
 *
 * Soft-delete marks a file for removal; CleanupOrphanedFilesJob
 * physically deletes the disk file after the retention grace period.
 */
class FileRecord extends Model
{
    use HasFactory, SoftDeletes;

    const CATEGORY_PRODUCT_IMAGE        = 'product_image';
    const CATEGORY_EXPERT_CERTIFICATION = 'expert_certification';
    const CATEGORY_RETURN_PROOF         = 'return_proof';
    const CATEGORY_PROFILE_IMAGE        = 'profile_image';
    const CATEGORY_FORUM_ATTACHMENT     = 'forum_attachment';
    const CATEGORY_ID_DOCUMENT          = 'id_document';
    const CATEGORY_OTHER                = 'other';

    protected $table = 'files';

    protected $fillable = [
        'uploaded_by',
        'fileable_id',
        'fileable_type',
        'disk',
        'category',
        'original_name',
        'stored_path',
        'mime_type',
        'size_bytes',
        'sha256_hash',
        'is_public',
    ];

    protected $casts = [
        'is_public'  => 'boolean',
        'size_bytes' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    // ── URL helpers ───────────────────────────────────────────────────────────

    /**
     * Returns a publicly accessible URL (public disk) or a temporary signed URL
     * (private disk). Never exposes the raw storage path.
     */
    public function url(int $expiresInMinutes = 60): string
    {
        if ($this->disk === 'public') {
            return Storage::disk('public')->url($this->stored_path);
        }

        return Storage::disk($this->disk)->temporaryUrl(
            $this->stored_path,
            now()->addMinutes($expiresInMinutes)
        );
    }

    /** Size formatted for display (e.g. "2.4 MB") */
    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes < 1024)        return "{$bytes} B";
        if ($bytes < 1048576)     return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824)  return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeCategory($query, string $category): mixed
    {
        return $query->where('category', $category);
    }

    public function scopePrivate($query): mixed
    {
        return $query->where('is_public', false);
    }

    public function scopeOrphaned($query): mixed
    {
        // Files with no living fileable model and uploaded > 7 days ago
        return $query->whereNull('fileable_id')
                     ->where('created_at', '<', now()->subDays(7));
    }
}
