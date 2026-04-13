<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'platform')) {
                $table->string('platform', 60)->nullable()->after('meeting_link');
            }
            if (! Schema::hasColumn('appointments', 'venue_name')) {
                $table->string('venue_name', 150)->nullable();
            }
            if (! Schema::hasColumn('appointments', 'address_line1')) {
                $table->string('address_line1', 255)->nullable();
            }
            if (! Schema::hasColumn('appointments', 'address_line2')) {
                $table->string('address_line2', 255)->nullable();
            }
            if (! Schema::hasColumn('appointments', 'city')) {
                $table->string('city', 100)->nullable();
            }
            if (! Schema::hasColumn('appointments', 'notifications_enabled')) {
                $table->boolean('notifications_enabled')->default(true)->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            foreach (['platform', 'venue_name', 'address_line1', 'address_line2', 'city', 'notifications_enabled'] as $column) {
                if (Schema::hasColumn('appointments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
