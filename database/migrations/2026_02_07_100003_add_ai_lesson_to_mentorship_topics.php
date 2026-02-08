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
        Schema::table('mentorship_topics', function (Blueprint $table) {
            $table->text('ai_lesson_content')->nullable()->after('description');
            $table->timestamp('ai_lesson_generated_at')->nullable()->after('ai_lesson_content');
            $table->foreignId('ai_lesson_saved_to')->nullable()->after('ai_lesson_generated_at')
                ->constrained('mentorship_lessons')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorship_topics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ai_lesson_saved_to');
            $table->dropColumn(['ai_lesson_content', 'ai_lesson_generated_at']);
        });
    }
};
