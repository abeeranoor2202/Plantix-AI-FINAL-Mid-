<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // Add per_user_limit column if it doesn't exist
            if (!Schema::hasColumn('coupons', 'per_user_limit')) {
                $table->integer('per_user_limit')->default(1)->after('usage_limit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (Schema::hasColumn('coupons', 'per_user_limit')) {
                $table->dropColumn('per_user_limit');
            }
        });
    }
};
