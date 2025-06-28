<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_message_id')->constrained()->onDelete('cascade');
            $table->bigInteger('recipient_id');
            $table->string('recipient_type'); // user, group
            $table->text('message_content');
            $table->string('status'); // sent, failed, pending
            $table->text('error_message')->nullable();
            $table->integer('telegram_message_id')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
