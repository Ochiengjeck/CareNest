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
        Schema::create('discharges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();

            // Provider Information
            $table->string('agency_name')->nullable();
            $table->string('discharge_staff_name')->nullable();
            $table->foreignId('discharge_staff_id')->nullable()->constrained('users')->nullOnDelete();

            // Dates
            $table->date('discharge_date');

            // Aftercare Information
            $table->string('next_level_of_care')->nullable();
            $table->text('barriers_to_transition')->nullable();
            $table->text('strengths_for_discharge')->nullable();

            // Clinical Summary (Page 1)
            $table->text('reason_for_admission')->nullable();
            $table->text('course_of_treatment')->nullable();
            $table->text('discharge_status_recommendations')->nullable();

            // Page 2
            $table->text('discharge_condition_reason')->nullable();
            $table->text('crisis_plan')->nullable();
            $table->json('future_appointments')->nullable();

            // Page 3
            $table->text('special_needs')->nullable();
            $table->json('medications_at_discharge')->nullable();
            $table->text('personal_possessions')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discharges');
    }
};
