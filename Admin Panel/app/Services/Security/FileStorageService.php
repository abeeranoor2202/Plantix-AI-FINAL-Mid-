<?php

namespace App\Services\Security;

use App\Models\FileRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * FileStorageService
 *
 * Central file upload handler for the entire platform.
 * Enforces:
 *   - Allowed MIME types per category
 *   - Maximum file size limits
 *   - Randomized UUIDs as stored filenames (no path traversal)
 *   - Organized directory structure per category
 *   - Duplicate detection via SHA-256 hash
 *   - DB record creation in `files` table
 *   - Private disk for sensitive documents
 *
 * Folder structure on disk:
 *   public/  products/{year}/
 *            profiles/{year}/
 *            forum/{year}/
 *   private/ experts/{year}/
 *            returns/{year}/
 *            id_documents/{year}/
 */
class FileStorageService
{
    /**
     * Category configuration:
     *   disk      – 'public' or 'private'
     *   folder    – base folder on that disk
     *   mimes     – allowed MIME types
     *   max_kb    – max file size in kilobytes
     *   is_public – whether URL should be accessible without auth
     */
    const CATEGORIES = [
        FileRecord::CATEGORY_PRODUCT_IMAGE => [
            'disk'      => 'public',
            'folder'    => 'products',
            'mimes'     => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            'max_kb'    => 5120,   // 5 MB
            'is_public' => true,
        ],
        FileRecord::CATEGORY_PROFILE_IMAGE => [
            'disk'      => 'public',
            'folder'    => 'profiles',
            'mimes'     => ['image/jpeg', 'image/png', 'image/webp'],
            'max_kb'    => 2048,   // 2 MB
            'is_public' => true,
        ],
        FileRecord::CATEGORY_FORUM_ATTACHMENT => [
            'disk'      => 'public',
            'folder'    => 'forum',
            'mimes'     => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
            'max_kb'    => 10240,  // 10 MB
            'is_public' => true,
        ],
        FileRecord::CATEGORY_EXPERT_CERTIFICATION => [
            'disk'      => 'private',
            'folder'    => 'experts',
            'mimes'     => ['application/pdf', 'image/jpeg', 'image/png'],
            'max_kb'    => 5120,   // 5 MB
            'is_public' => false,
        ],
        FileRecord::CATEGORY_ID_DOCUMENT => [
            'disk'      => 'private',
            'folder'    => 'id_documents',
            'mimes'     => ['application/pdf', 'image/jpeg', 'image/png'],
            'max_kb'    => 5120,   // 5 MB
            'is_public' => false,
        ],
        FileRecord::CATEGORY_RETURN_PROOF => [
            'disk'      => 'private',
            'folder'    => 'returns',
            'mimes'     => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
            'max_kb'    => 10240,  // 10 MB
            'is_public' => false,
        ],
        FileRecord::CATEGORY_OTHER => [
            'disk'      => 'private',
            'folder'    => 'other',
            'mimes'     => ['image/jpeg', 'image/png', 'application/pdf'],
            'max_kb'    => 10240,
            'is_public' => false,
        ],
    ];

    // ── Public interface ──────────────────────────────────────────────────────

    /**
     * Store an uploaded file for a given category.
     *
     * @param  UploadedFile   $file
     * @param  string         $category  One of FileRecord::CATEGORY_* constants
     * @param  int|null       $uploaderId  User ID of the uploader
     * @param  Model|null     $owner      Polymorphic owner model
     * @return FileRecord
     *
     * @throws ValidationException  On MIME or size violation
     * @throws \RuntimeException    On executor upload failure
     */
    public function store(
        UploadedFile $file,
        string       $category,
        ?int         $uploaderId = null,
        ?Model       $owner      = null
    ): FileRecord {
        $config = $this->config($category);

        $this->validateMime($file, $config['mimes']);
        $this->validateSize($file, $config['max_kb']);

        $hash     = hash_file('sha256', $file->getRealPath());
        $ext      = $file->getClientOriginalExtension() ?: $file->guessExtension() ?? 'bin';
        $filename = Str::uuid() . '.' . strtolower($ext);
        $folder   = $config['folder'] . '/' . now()->format('Y');
        $path     = $folder . '/' . $filename;

        $disk = $config['disk'];

        // Store to disk
        $stored = Storage::disk($disk)->putFileAs($folder, $file, $filename);

        if (!$stored) {
            throw new \RuntimeException("File upload failed — storage write error for category: {$category}");
        }

        return FileRecord::create([
            'uploaded_by'   => $uploaderId,
            'fileable_id'   => $owner?->getKey(),
            'fileable_type' => $owner ? get_class($owner) : null,
            'disk'          => $disk,
            'category'      => $category,
            'original_name' => $file->getClientOriginalName(),
            'stored_path'   => $path,
            'mime_type'     => $file->getMimeType() ?? $file->getClientMimeType(),
            'size_bytes'    => $file->getSize(),
            'sha256_hash'   => $hash,
            'is_public'     => $config['is_public'],
        ]);
    }

    /**
     * Replace an existing file with a new upload.
     * Old disk file is deleted after the new record is saved.
     */
    public function replace(
        FileRecord   $old,
        UploadedFile $newFile,
        ?int         $uploaderId = null
    ): FileRecord {
        $record = $this->store($newFile, $old->category, $uploaderId);

        // Delete old disk file and soft-delete the record
        $this->delete($old);

        return $record;
    }

    /**
     * Soft-delete record + physically remove disk file.
     */
    public function delete(FileRecord $record): void
    {
        try {
            Storage::disk($record->disk)->delete($record->stored_path);
        } catch (\Throwable $e) {
            // Log but don't throw — we still want to soft-delete the DB record
            logger()->warning("FileStorageService: disk delete failed for file #{$record->id}", [
                'path'  => $record->stored_path,
                'error' => $e->getMessage(),
            ]);
        }

        $record->delete(); // Soft delete
    }

    /**
     * Get validation rules array suitable for use in Form Requests.
     */
    public static function validationRules(string $category, bool $required = true): array
    {
        $config = self::CATEGORIES[$category] ?? self::CATEGORIES[FileRecord::CATEGORY_OTHER];

        $mimeStr = implode(',', array_map(function ($m) {
            return explode('/', $m)[1]; // 'image/jpeg' → 'jpeg'
        }, $config['mimes']));

        $rule = ($required ? 'required' : 'nullable') . '|file'
              . '|mimes:' . $mimeStr
              . '|max:' . $config['max_kb'];

        return [$rule];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function config(string $category): array
    {
        if (!isset(self::CATEGORIES[$category])) {
            throw new \InvalidArgumentException("Unknown file category: {$category}");
        }
        return self::CATEGORIES[$category];
    }

    private function validateMime(UploadedFile $file, array $allowedMimes): void
    {
        $actualMime = $file->getMimeType();

        if (!in_array($actualMime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'file' => [
                    "File type '{$actualMime}' is not allowed. "
                    . "Allowed types: " . implode(', ', $allowedMimes)
                ],
            ]);
        }
    }

    private function validateSize(UploadedFile $file, int $maxKb): void
    {
        $sizeKb = $file->getSize() / 1024;

        if ($sizeKb > $maxKb) {
            $maxMb = number_format($maxKb / 1024, 1);
            throw ValidationException::withMessages([
                'file' => ["File size exceeds the maximum allowed size of {$maxMb} MB."],
            ]);
        }
    }
}
