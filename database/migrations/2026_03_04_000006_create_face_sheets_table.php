<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->text('diagnosis')->nullable();
            $table->text('facility_address')->nullable();
            $table->string('facility_phone')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('eye_color')->nullable();
            $table->string('race')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('hair_color')->nullable();
            $table->text('identifiable_marks')->nullable();
            $table->string('primary_language')->nullable();
            $table->boolean('court_ordered')->nullable();
            $table->text('family_emergency_contact')->nullable();
            $table->string('facility_emergency_contact')->nullable();
            $table->text('medication_allergies')->nullable();
            $table->text('other_allergies')->nullable();
            $table->string('pcp_name')->nullable();
            $table->string('pcp_phone')->nullable();
            $table->text('pcp_address')->nullable();
            $table->string('specialist_1_type')->nullable();
            $table->string('specialist_1_name')->nullable();
            $table->string('specialist_1_phone')->nullable();
            $table->text('specialist_1_address')->nullable();
            $table->string('psych_name')->nullable();
            $table->string('psych_phone')->nullable();
            $table->text('psych_address')->nullable();
            $table->string('specialist_2_type')->nullable();
            $table->string('specialist_2_name')->nullable();
            $table->string('specialist_2_phone')->nullable();
            $table->text('specialist_2_address')->nullable();
            $table->string('preferred_hospital')->nullable();
            $table->string('preferred_hospital_phone')->nullable();
            $table->text('preferred_hospital_address')->nullable();
            $table->string('health_plan')->nullable();
            $table->string('health_plan_id')->nullable();
            $table->string('case_manager_name')->nullable();
            $table->string('case_manager_phone')->nullable();
            $table->string('case_manager_email')->nullable();
            $table->string('ss_rep_payee')->nullable();
            $table->string('ss_rep_phone')->nullable();
            $table->string('ss_rep_email')->nullable();
            $table->text('mental_health_diagnoses')->nullable();
            $table->text('medical_diagnoses')->nullable();
            $table->text('past_surgeries')->nullable();
            $table->json('signers')->nullable();
            $table->foreignId('signature_id')->nullable()->constrained('signatures')->nullOnDelete();
            $table->text('raw_signature_data')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_sheets');
    }
};
