<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'notification_preferences')) {
                $table->json('notification_preferences')->nullable()->after('is_shadow_banned');
            }
        });

        Schema::table('email_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('email_templates', 'name')) {
                $table->string('name', 150)->nullable()->after('id');
            }

            if (! Schema::hasColumn('email_templates', 'email_type')) {
                $table->string('email_type', 50)->default('system')->after('name');
            }

            if (! Schema::hasColumn('email_templates', 'variables')) {
                $table->longText('variables')->nullable()->after('body');
            }

            if (! Schema::hasColumn('email_templates', 'is_send_to_admin')) {
                $table->boolean('is_send_to_admin')->default(false)->after('variables');
            }
        });

        Schema::table('notification_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('notification_logs', 'payload')) {
                $table->longText('payload')->nullable()->after('subject');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            if (Schema::hasColumn('notification_logs', 'payload')) {
                $table->dropColumn('payload');
            }
        });

        Schema::table('email_templates', function (Blueprint $table) {
            if (Schema::hasColumn('email_templates', 'variables')) {
                $table->dropColumn('variables');
            }

            if (Schema::hasColumn('email_templates', 'is_send_to_admin')) {
                $table->dropColumn('is_send_to_admin');
            }

            if (Schema::hasColumn('email_templates', 'email_type')) {
                $table->dropColumn('email_type');
            }

            if (Schema::hasColumn('email_templates', 'name')) {
                $table->dropColumn('name');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'notification_preferences')) {
                $table->dropColumn('notification_preferences');
            }
        });
    }
};