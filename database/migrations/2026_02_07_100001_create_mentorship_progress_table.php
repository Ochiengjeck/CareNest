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
        Schema::create('mentorship_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('mentorship_topics')->cascadeOnDelete();

            // Completion status
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'revision'])->default('not_started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Revision tracking
            $table->unsignedSmallInteger('revision_count')->default(0);
            $table->timestamp('last_revised_at')->nullable();

            $table->timestamps();

            // One progress record per user per topic
            $table->unique(['user_id', 'topic_id']);

            // Efficient queries by user and status
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentorship_progress');
    }
};
