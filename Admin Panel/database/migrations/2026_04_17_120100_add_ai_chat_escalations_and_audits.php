<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_chat_escalations')) {
            Schema::create('ai_chat_escalations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('ai_chat_sessions')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('latest_message_id')->nullable()->constrained('ai_chat_messages')->nullOnDelete();
                $table->enum('status', ['pending', 'assigned', 'resolved', 'closed'])->default('pending');
                $table->text('reason')->nullable();
                $table->foreignId('assigned_expert_id')->nullable()->constrained('experts')->nullOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('user_id');
                $table->index('assigned_expert_id');
                $table->index('created_at');
            });
        }

        if (! Schema::hasTable('ai_chat_audits')) {
            Schema::create('ai_chat_audits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('ai_chat_sessions')->cascadeOnDelete();
                $table->foreignId('message_id')->nullable()->constrained('ai_chat_messages')->nullOnDelete();
                $table->string('event_type', 60);
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('event_type');
                $table->index('created_at');
                $table->index('session_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_audits');
        Schema::dropIfExists('ai_chat_escalations');
    }
};
