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
        Schema::create('mentorship_lessons', function (Blueprint $table) {
            $table->id();

            // Content
            $table->string('title');
            $table->string('category');
            $table->text('content');
            $table->text('summary')->nullable();

            // Source tracking
            $table->foreignId('source_topic_id')->nullable()->constrained('mentorship_topics')->nullOnDelete();
            $table->boolean('is_ai_generated')->default(true);

            // Publishing
            $table->boolean('is_published')->default(false);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('category');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentorship_lessons');
    }
};
