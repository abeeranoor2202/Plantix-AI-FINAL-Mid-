<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central file registry.
 *
 * Every file uploaded through FileStorageService is tracked here.
 * Enables:
 *   - Orphan detection (file on disk but no DB record, or vice versa)
 *   - Soft delete (record + scheduled cleanup removes disk file)
 *   - Duplicate detection via sha256_hash
 *   - Storage quota monitoring per uploader
 *
 * disk: 'public' | 'private' | 's3'
 * category: 'product_image' | 'expert_certification' | 'return_proof' |
 *           'profile_image' | 'forum_attachment' | 'id_document' | 'other'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('uploaded_by')->nullable()->index()
                  ->comment('user_id of uploader');

            // Polymorphic owner — links to Product, Expert, ReturnRequest, etc.
            $table->nullableMorphs('fileable');

            $table->string('disk', 20)->default('public')
                  ->comment('public|private');

            $table->string('category', 40)->index()
                  ->comment('product_image|expert_certification|return_proof|profile_image|forum_attachment|id_document|other');

            // Original filename for display; stored name is randomized UUID
            $table->string('original_name', 255);
            $table->string('stored_path', 500)
                  ->comment('Path relative to disk root, e.g. products/2026/uuid.jpg');

            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');

            // SHA-256 hash for duplicate detection — nullable for large binaries
            $table->char('sha256_hash', 64)->nullable()->index();

            $table->boolean('is_public')->default(true)
                  ->comment('False = must go through signed URL or auth check');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['category', 'disk', 'created_at'], 'files_cat_disk_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
