<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('real_time_notifications')) {
            Schema::create('real_time_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('receiver_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('recipient_id')->nullable();
                $table->enum('role', ['user', 'expert', 'vendor', 'admin'])->default('user');
                $table->string('type', 100)->default('system');
                $table->string('title')->nullable();
                $table->text('message')->nullable();
                $table->enum('status', ['unread', 'read'])->default('unread');
                $table->boolean('read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->string('action_url', 500)->nullable();
                $table->json('metadata')->nullable();
                $table->string('dedup_key', 190)->nullable();
                $table->timestamps();

                $table->index(['receiver_id', 'status', 'created_at'], 'rt_notifications_receiver_status_created_idx');
                $table->index(['role', 'type'], 'rt_notifications_role_type_idx');
                $table->index(['dedup_key'], 'rt_notifications_dedup_idx');
            });

            return;
        }

        Schema::table('real_time_notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('real_time_notifications', 'sender_id')) {
                $table->foreignId('sender_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('real_time_notifications', 'receiver_id')) {
                $table->foreignId('receiver_id')->nullable()->after('sender_id')->constrained('users')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('real_time_notifications', 'role')) {
                $table->enum('role', ['user', 'expert', 'vendor', 'admin'])->default('user')->after('receiver_id');
            }

            if (! Schema::hasColumn('real_time_notifications', 'type')) {
                $table->string('type', 100)->default('system')->after('role');
            }

            if (! Schema::hasColumn('real_time_notifications', 'status')) {
                $table->enum('status', ['unread', 'read'])->default('unread')->after('type');
            }

            if (! Schema::hasColumn('real_time_notifications', 'action_url')) {
                $table->string('action_url', 500)->nullable()->after('message');
            }

            if (! Schema::hasColumn('real_time_notifications', 'metadata')) {
                $table->json('metadata')->nullable()->after('action_url');
            }

            if (! Schema::hasColumn('real_time_notifications', 'dedup_key')) {
                $table->string('dedup_key', 190)->nullable()->after('metadata');
            }

            if (! Schema::hasColumn('real_time_notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('read');
            }

            try {
                $table->index(['receiver_id', 'status', 'created_at'], 'rt_notifications_receiver_status_created_idx');
            } catch (\Throwable $e) {
            }

            try {
                $table->index(['role', 'type'], 'rt_notifications_role_type_idx');
            } catch (\Throwable $e) {
            }

            try {
                $table->index(['dedup_key'], 'rt_notifications_dedup_idx');
            } catch (\Throwable $e) {
            }
        });

        DB::table('real_time_notifications')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                $receiverId = $row->receiver_id ?? $row->recipient_id ?? null;
                if (! $receiverId) {
                    continue;
                }

                DB::table('real_time_notifications')
                    ->where('id', $row->id)
                    ->update([
                        'receiver_id' => $receiverId,
                        'role' => $row->role ?? 'user',
                        'status' => ($row->status ?? null) ?: ((bool) $row->read ? 'read' : 'unread'),
                        'action_url' => $row->action_url ?? data_get(json_decode((string) $row->metadata, true) ?: [], 'action_url'),
                        'read_at' => $row->read_at ?? (($row->read ?? false) ? $row->updated_at : null),
                    ]);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('real_time_notifications')) {
            return;
        }

        Schema::table('real_time_notifications', function (Blueprint $table) {
            try {
                $table->dropIndex('rt_notifications_receiver_status_created_idx');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex('rt_notifications_role_type_idx');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropIndex('rt_notifications_dedup_idx');
            } catch (\Throwable $e) {
            }

            if (Schema::hasColumn('real_time_notifications', 'read_at')) {
                $table->dropColumn('read_at');
            }
            if (Schema::hasColumn('real_time_notifications', 'dedup_key')) {
                $table->dropColumn('dedup_key');
            }
            if (Schema::hasColumn('real_time_notifications', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('real_time_notifications', 'action_url')) {
                $table->dropColumn('action_url');
            }
            if (Schema::hasColumn('real_time_notifications', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('real_time_notifications', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('real_time_notifications', 'receiver_id')) {
                $table->dropConstrainedForeignId('receiver_id');
            }
            if (Schema::hasColumn('real_time_notifications', 'sender_id')) {
                $table->dropConstrainedForeignId('sender_id');
            }
        });
    }
};
