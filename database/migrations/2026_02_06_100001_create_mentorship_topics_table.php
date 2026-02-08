<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorship_topics', function (Blueprint $table) {
            $table->id();

            // Scheduling
            $table->date('topic_date')->index();
            $table->string('day_of_week', 20);
            $table->time('time_slot');

            // Content
            $table->string('title');
            $table->string('category');
            $table->text('description')->nullable();

            // Publishing
            $table->boolean('is_published')->default(true)->index();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Composite index for efficient weekly queries
            $table->index(['topic_date', 'time_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_topics');
    }
};
