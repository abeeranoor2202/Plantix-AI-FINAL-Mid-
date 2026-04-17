<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('platform_activities')) {
            Schema::table('platform_activities', function (Blueprint $table) {
                if (! $this->hasIndex('platform_activities', 'platform_activities_actor_role_created_at_index')) {
                    $table->index(['actor_role', 'created_at']);
                }
                if (! $this->hasIndex('platform_activities', 'platform_activities_entity_type_entity_id_index')) {
                    $table->index(['entity_type', 'entity_id']);
                }
            });
        }

        if (Schema::hasTable('notification_logs') && Schema::hasTable('users')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                if (Schema::hasColumn('notification_logs', 'user_id')) {
                    try {
                        $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                    } catch (\Throwable $e) {
                        // Foreign key may already exist on some environments.
                    }
                }
            });
        }

        if (Schema::hasTable('payments') && Schema::hasTable('orders')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'order_id')) {
                    try {
                        $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
                    } catch (\Throwable $e) {
                        // Keep migration idempotent across existing installs.
                    }
                }
            });
        }

        if (Schema::hasTable('appointment_status_histories') && Schema::hasTable('appointments')) {
            Schema::table('appointment_status_histories', function (Blueprint $table) {
                if (Schema::hasColumn('appointment_status_histories', 'appointment_id')) {
                    try {
                        $table->foreign('appointment_id')->references('id')->on('appointments')->cascadeOnDelete();
                    } catch (\Throwable $e) {
                        // Keep migration idempotent across existing installs.
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('platform_activities')) {
            Schema::table('platform_activities', function (Blueprint $table) {
                try {
                    $table->dropIndex(['actor_role', 'created_at']);
                } catch (\Throwable $e) {
                }

                try {
                    $table->dropIndex(['entity_type', 'entity_id']);
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('notification_logs')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                try {
                    $table->dropForeign(['order_id']);
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('appointment_status_histories')) {
            Schema::table('appointment_status_histories', function (Blueprint $table) {
                try {
                    $table->dropForeign(['appointment_id']);
                } catch (\Throwable $e) {
                }
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return false;
        }

        $database = DB::getDatabaseName();

        $row = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->first();

        return $row !== null;
    }
};
