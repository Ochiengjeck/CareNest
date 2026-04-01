<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('initial_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();

            // Section 1 — Assessment Information
            $table->date('assessment_date')->nullable();
            $table->string('assessment_time')->nullable();
            $table->string('referral_source')->nullable();
            $table->string('primary_language')->nullable();
            $table->string('assessor_name')->nullable();
            $table->boolean('court_ordered')->nullable();

            // Section 2 — Psychosocial / Demographics
            $table->string('marital_status')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('education_level')->nullable();
            $table->string('living_situation')->nullable();
            $table->boolean('veteran_status')->nullable();

            // Section 3 — Presenting Problem
            $table->text('chief_complaint')->nullable();
            $table->text('presenting_problem')->nullable();
            $table->string('duration_of_problem')->nullable();
            $table->text('previous_treatments')->nullable();
            $table->text('goals_for_treatment')->nullable();

            // Section 4 — Mental Status (JSON)
            $table->json('mental_status')->nullable();

            // Section 5 — Substance Use History (JSON array of rows)
            $table->json('substance_use')->nullable();

            // Section 6 — Medical History
            $table->text('current_medications')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('medication_allergies')->nullable();
            $table->text('other_allergies')->nullable();
            $table->text('hospitalizations')->nullable();

            // Section 7 — Psychiatric History
            $table->text('psychiatric_diagnoses')->nullable();
            $table->text('psychiatric_hospitalizations')->nullable();
            $table->text('psychiatric_medications')->nullable();
            $table->string('psych_provider_name')->nullable();
            $table->string('psych_provider_phone')->nullable();

            // Section 8 — Psychosocial / Legal History
            $table->string('legal_status')->nullable();
            $table->text('legal_history')->nullable();
            $table->text('employment_history')->nullable();
            $table->text('family_history')->nullable();
            $table->text('trauma_history')->nullable();
            $table->text('social_support')->nullable();
            $table->text('cultural_considerations')->nullable();

            // Section 9 — Risk Assessment
            $table->string('suicidal_ideation')->nullable();   // none|passive|active
            $table->boolean('suicide_plan')->nullable();
            $table->text('suicide_history')->nullable();
            $table->string('homicidal_ideation')->nullable();  // none|passive|active
            $table->text('self_harm_history')->nullable();
            $table->string('risk_level')->nullable();          // low|moderate|high|imminent

            // Section 10 — Diagnostic Summary & Recommendations
            $table->text('clinical_summary')->nullable();
            $table->string('primary_diagnosis')->nullable();
            $table->string('secondary_diagnosis')->nullable();
            $table->string('asam_level')->nullable();
            $table->string('level_of_care')->nullable();
            $table->text('treatment_goals')->nullable();
            $table->text('recommendations')->nullable();

            // Signature block
            $table->json('signers')->nullable();
            $table->foreignId('signature_id')->nullable()->constrained('signatures')->nullOnDelete();
            $table->text('raw_signature_data')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('initial_assessments');
    }
};
