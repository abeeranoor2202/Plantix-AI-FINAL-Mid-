<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'vendor_response')) {
                $table->text('vendor_response')->nullable()->after('review_images');
            }

            if (! Schema::hasColumn('reviews', 'vendor_responded_at')) {
                $table->timestamp('vendor_responded_at')->nullable()->after('vendor_response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'vendor_responded_at')) {
                $table->dropColumn('vendor_responded_at');
            }

            if (Schema::hasColumn('reviews', 'vendor_response')) {
                $table->dropColumn('vendor_response');
            }
        });
    }
};
