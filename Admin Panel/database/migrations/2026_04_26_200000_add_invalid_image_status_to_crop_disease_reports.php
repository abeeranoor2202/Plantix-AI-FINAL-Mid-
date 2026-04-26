<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds 'invalid_image' to the crop_disease_reports.status enum.
 *
 * This status is set when the VGG16 model's confidence is below the
 * CONFIDENCE_THRESHOLD (0.70), meaning the uploaded image is not a
 * recognisable plant leaf.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE crop_disease_reports
            MODIFY COLUMN status
            ENUM('pending','processed','failed','manual_review','invalid_image')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Remove invalid_image rows before reverting enum (avoid data truncation error)
        DB::statement("
            UPDATE crop_disease_reports
            SET status = 'manual_review'
            WHERE status = 'invalid_image'
        ");

        DB::statement("
            ALTER TABLE crop_disease_reports
            MODIFY COLUMN status
            ENUM('pending','processed','failed','manual_review')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
