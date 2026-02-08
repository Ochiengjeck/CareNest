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
        Schema::create('mentorship_sessions', function (Blueprint $table) {
            $table->id();

            // Core relationships
            $table->foreignId('topic_id')->constrained('mentorship_topics')->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('mentorship_lessons')->nullOnDelete();

            // Session timing
            $table->date('session_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Participant tracking (count only)
            $table->unsignedSmallInteger('participant_count')->default(0);
            $table->text('participant_notes')->nullable();

            // Status tracking
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');

            // Session content
            $table->text('session_notes')->nullable();
            $table->text('lesson_content_snapshot')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['mentor_id', 'session_date']);
            $table->index(['topic_id', 'status']);
            $table->index('session_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentorship_sessions');
    }
};
