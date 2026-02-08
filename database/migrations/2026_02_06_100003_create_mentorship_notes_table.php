<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorship_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('mentorship_topics')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Note content
            $table->text('content');

            $table->timestamps();

            // One note per user per topic
            $table->unique(['topic_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_notes');
    }
};
