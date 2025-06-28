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
        Schema::create('scheduled_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('media_type')->nullable(); // photo, video, document
            $table->string('media_path')->nullable();
            $table->string('recipient_type'); // user, group
            $table->bigInteger('recipient_id'); // telegram_id do usuário ou grupo
            $table->datetime('scheduled_at');
            $table->string('recurrence_type')->default('none'); // none, daily, weekly, monthly, yearly
            $table->integer('recurrence_interval')->default(1);
            $table->datetime('next_run_at')->nullable();
            $table->datetime('last_sent_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_messages');
    }
};
