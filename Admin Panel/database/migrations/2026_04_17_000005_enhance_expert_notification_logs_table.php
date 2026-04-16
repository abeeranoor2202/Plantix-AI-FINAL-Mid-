<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expert_notification_logs')) {
            return;
        }

        Schema::table('expert_notification_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('expert_notification_logs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('expert_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('expert_notification_logs', 'message')) {
                $table->text('message')->nullable()->after('title');
            }

            if (! Schema::hasColumn('expert_notification_logs', 'action_url')) {
                $table->string('action_url', 500)->nullable()->after('body');
            }
        });

        if (Schema::hasColumn('expert_notification_logs', 'message') && Schema::hasColumn('expert_notification_logs', 'body')) {
            \DB::statement("UPDATE expert_notification_logs SET message = COALESCE(message, body)");
        }

    }

    public function down(): void
    {
        if (! Schema::hasTable('expert_notification_logs')) {
            return;
        }

        Schema::table('expert_notification_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('expert_notification_logs', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('expert_notification_logs', 'message')) {
                $table->dropColumn('message');
            }

            if (Schema::hasColumn('expert_notification_logs', 'action_url')) {
                $table->dropColumn('action_url');
            }
        });
    }
};
