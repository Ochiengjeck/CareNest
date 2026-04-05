<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->date('meeting_date');
            $table->enum('meeting_type', ['scheduled', 'emergency', 'discharge_planning'])->default('scheduled');
            $table->json('attendees')->nullable();
            $table->text('discussion_notes')->nullable();
            $table->text('treatment_plan_revisions')->nullable();
            $table->date('next_meeting_date')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('art_meetings');
    }
};
