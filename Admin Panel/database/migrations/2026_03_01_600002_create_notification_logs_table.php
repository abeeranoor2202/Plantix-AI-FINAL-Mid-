<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();

            // Recipient
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('recipient_email', 180);
            $table->string('recipient_name', 120)->nullable();
            $table->string('recipient_role', 30)->nullable(); // user, vendor, expert, admin

            // Notification meta
            $table->string('notification_type', 80)->index(); // e.g., order_placed, appointment_confirmed
            $table->string('mailable_class', 200)->nullable();
            $table->string('subject', 255)->nullable();

            // Polymorphic context (order, appointment, forum_thread, etc.)
            $table->string('notifiable_type', 80)->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->index(['notifiable_type', 'notifiable_id']);

            // Status tracking
            $table->enum('status', ['queued', 'sent', 'failed', 'skipped'])->default('queued')->index();
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempt_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Dedup key — prevents sending same notification twice for same event
            $table->string('dedup_key', 100)->nullable()->unique();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
