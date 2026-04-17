<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->cleanupOrphanedAndInconsistentRows();
        $this->enforceMissingForeignKeys();
        $this->enforceMissingIndexes();
    }

    public function down(): void
    {
        // Data cleanup is intentionally irreversible.
        // Constraints added here are additive safety constraints and are kept on rollback.
    }

    private function cleanupOrphanedAndInconsistentRows(): void
    {
        if (Schema::hasTable('order_items') && Schema::hasTable('orders')) {
            DB::statement('DELETE oi FROM order_items oi LEFT JOIN orders o ON o.id = oi.order_id WHERE o.id IS NULL');
        }

        if (Schema::hasTable('order_disputes') && Schema::hasTable('orders')) {
            DB::statement('DELETE od FROM order_disputes od LEFT JOIN orders o ON o.id = od.order_id WHERE o.id IS NULL');

            if (Schema::hasColumn('order_disputes', 'user_id')) {
                DB::statement('UPDATE order_disputes od INNER JOIN orders o ON o.id = od.order_id SET od.user_id = o.user_id WHERE od.user_id <> o.user_id');
            }

            if (Schema::hasColumn('order_disputes', 'vendor_id')) {
                DB::statement('UPDATE order_disputes od INNER JOIN orders o ON o.id = od.order_id SET od.vendor_id = o.vendor_id WHERE (od.vendor_id IS NULL AND o.vendor_id IS NOT NULL) OR (od.vendor_id IS NOT NULL AND o.vendor_id IS NOT NULL AND od.vendor_id <> o.vendor_id)');
            }
        }

        if (Schema::hasTable('appointment_reschedules') && Schema::hasTable('appointments')) {
            DB::statement('DELETE ar FROM appointment_reschedules ar LEFT JOIN appointments a ON a.id = ar.appointment_id WHERE a.id IS NULL');
        }

        if (Schema::hasTable('appointment_status_histories') && Schema::hasTable('appointments')) {
            DB::statement('DELETE ash FROM appointment_status_histories ash LEFT JOIN appointments a ON a.id = ash.appointment_id WHERE a.id IS NULL');
        }

        if (Schema::hasTable('forum_replies') && Schema::hasTable('forum_threads')) {
            DB::statement('DELETE fr FROM forum_replies fr LEFT JOIN forum_threads ft ON ft.id = fr.thread_id WHERE ft.id IS NULL');
        }

        if (Schema::hasTable('forum_flags')) {
            if (Schema::hasColumn('forum_flags', 'thread_id') && Schema::hasColumn('forum_flags', 'reply_id') && Schema::hasTable('forum_replies')) {
                DB::statement('UPDATE forum_flags ff INNER JOIN forum_replies fr ON fr.id = ff.reply_id SET ff.thread_id = fr.thread_id WHERE ff.reply_id IS NOT NULL AND (ff.thread_id IS NULL OR ff.thread_id <> fr.thread_id)');
            }

            if (Schema::hasColumn('forum_flags', 'thread_id') && Schema::hasColumn('forum_flags', 'reply_id')) {
                DB::statement('DELETE FROM forum_flags WHERE reply_id IS NULL AND thread_id IS NULL');
            }
        }
    }

    private function enforceMissingForeignKeys(): void
    {
        if (Schema::hasTable('order_disputes')) {
            Schema::table('order_disputes', function (Blueprint $table) {
                if (Schema::hasColumn('order_disputes', 'order_id') && ! $this->hasForeignKey('order_disputes', 'order_id')) {
                    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
                }

                if (Schema::hasColumn('order_disputes', 'user_id') && ! $this->hasForeignKey('order_disputes', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                }

                if (Schema::hasColumn('order_disputes', 'vendor_id') && ! $this->hasForeignKey('order_disputes', 'vendor_id')) {
                    $table->foreign('vendor_id')->references('id')->on('vendors')->nullOnDelete();
                }

                if (Schema::hasColumn('order_disputes', 'resolved_by') && ! $this->hasForeignKey('order_disputes', 'resolved_by')) {
                    $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('forum_flags')) {
            Schema::table('forum_flags', function (Blueprint $table) {
                if (Schema::hasColumn('forum_flags', 'thread_id') && ! $this->hasForeignKey('forum_flags', 'thread_id')) {
                    $table->foreign('thread_id')->references('id')->on('forum_threads')->cascadeOnDelete();
                }

                if (Schema::hasColumn('forum_flags', 'flagged_by') && ! $this->hasForeignKey('forum_flags', 'flagged_by')) {
                    $table->foreign('flagged_by')->references('id')->on('users')->cascadeOnDelete();
                }

                if (Schema::hasColumn('forum_flags', 'reviewed_by') && ! $this->hasForeignKey('forum_flags', 'reviewed_by')) {
                    $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('appointment_reschedules')) {
            Schema::table('appointment_reschedules', function (Blueprint $table) {
                if (Schema::hasColumn('appointment_reschedules', 'appointment_id') && ! $this->hasForeignKey('appointment_reschedules', 'appointment_id')) {
                    $table->foreign('appointment_id')->references('id')->on('appointments')->cascadeOnDelete();
                }

                if (Schema::hasColumn('appointment_reschedules', 'requested_by') && ! $this->hasForeignKey('appointment_reschedules', 'requested_by')) {
                    $table->foreign('requested_by')->references('id')->on('users')->cascadeOnDelete();
                }
            });
        }
    }

    private function enforceMissingIndexes(): void
    {
        if (Schema::hasTable('order_disputes')) {
            Schema::table('order_disputes', function (Blueprint $table) {
                if (! $this->hasIndex('order_disputes', 'order_disputes_order_id_unique')) {
                    $table->unique('order_id');
                }

                if (! $this->hasIndex('order_disputes', 'order_disputes_status_index')) {
                    $table->index('status');
                }
            });
        }

        if (Schema::hasTable('forum_flags')) {
            Schema::table('forum_flags', function (Blueprint $table) {
                if (Schema::hasColumn('forum_flags', 'reply_id') && ! $this->hasIndex('forum_flags', 'forum_flags_reply_user_unique')) {
                    $table->unique(['reply_id', 'flagged_by'], 'forum_flags_reply_user_unique');
                }

                if (Schema::hasColumn('forum_flags', 'thread_id') && ! $this->hasIndex('forum_flags', 'forum_flags_thread_user_unique')) {
                    $table->unique(['thread_id', 'flagged_by'], 'forum_flags_thread_user_unique');
                }
            });
        }
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::selectOne(
            'SELECT COUNT(*) AS aggregate
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $result = DB::selectOne('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?', [$indexName]);

        return $result !== null;
    }
};
