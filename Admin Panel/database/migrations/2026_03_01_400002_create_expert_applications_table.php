<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expert Applications table.
 *
 * Tracks the user → expert upgrade lifecycle:
 *   pending → under_review → approved | rejected
 *
 * When approved, the admin creates an Expert record for the user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_applications', function (Blueprint $table) {
            $table->id();

            // Applicant (must be an existing user with role = 'customer')
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // ── Professional background ───────────────────────────────────
            $table->string('full_name');
            $table->string('specialization');
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->text('qualifications')->nullable();
            $table->text('bio')->nullable();

            // File paths (stored on disk, not DB content)
            $table->string('certifications_path')->nullable();  // PDF / image
            $table->string('id_document_path')->nullable();

            // ── Contact / location ────────────────────────────────────────
            $table->string('contact_phone')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();

            $table->enum('account_type', ['individual', 'agency'])->default('individual');
            $table->string('agency_name')->nullable();

            // ── Workflow state ────────────────────────────────────────────
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
            ])->default('pending');

            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ───────────────────────────────────────────────────
            $table->index('user_id');
            $table->index('status');
            $table->index(['status', 'created_at'], 'applications_status_created_index');

            // A user may only have ONE active (non-rejected) application
            // Enforced at application layer; DB allows multiple (for history).
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_applications');
    }
};
