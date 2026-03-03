<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_progress_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();

            // Shift timing
            $table->date('shift_date');
            $table->time('shift_start_time')->nullable();
            $table->time('shift_end_time')->nullable();

            // Appointment
            $table->json('appointment')->nullable();
            $table->string('appointment_other')->nullable();

            // Mood, speech, behaviors
            $table->json('mood')->nullable();
            $table->string('mood_other')->nullable();
            $table->json('speech')->nullable();
            $table->string('speech_other')->nullable();
            $table->json('behaviors')->nullable();
            $table->string('behaviors_other')->nullable();

            // Quick checks
            $table->boolean('resident_redirected')->nullable();
            $table->boolean('outing_in_community')->nullable();
            $table->string('therapy_participation')->nullable(); // yes, no, refused
            $table->boolean('awol')->nullable();
            $table->boolean('welfare_checks')->nullable();
            $table->string('medication_administered')->nullable(); // yes, no, refused

            // Meals
            $table->string('meal_preparation', 2)->nullable(); // I, HP, R, PA, TA, VP, NP
            $table->json('meals')->nullable();
            $table->json('snacks')->nullable();

            // ADLs & prompts
            $table->boolean('adls_completed')->nullable();
            $table->boolean('prompted_medications')->nullable();
            $table->boolean('prompted_adls')->nullable();
            $table->boolean('water_temperature_adjusted')->nullable();
            $table->boolean('clothing_assistance')->nullable();

            // Activities
            $table->json('activities')->nullable();
            $table->string('activities_other')->nullable();

            // Summary & signature
            $table->text('note_summary')->nullable();
            $table->foreignId('signature_id')->nullable()->constrained('signatures')->nullOnDelete();

            // Audit
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_progress_notes');
    }
};
