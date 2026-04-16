<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_reasons', function (Blueprint $table) {
            if (! Schema::hasColumn('return_reasons', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->nullOnDelete();
                $table->index(['vendor_id', 'is_active']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('return_reasons', function (Blueprint $table) {
            if (Schema::hasColumn('return_reasons', 'vendor_id')) {
                $table->dropIndex(['vendor_id', 'is_active']);
                $table->dropConstrainedForeignId('vendor_id');
            }
        });
    }
};
