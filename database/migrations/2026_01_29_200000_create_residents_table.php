<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('photo_path')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Admission Details
            $table->date('admission_date');
            $table->date('discharge_date')->nullable();
            $table->string('room_number')->nullable();
            $table->string('bed_number')->nullable();
            $table->enum('status', ['active', 'discharged', 'deceased', 'on_leave'])->default('active')->index();

            // Medical Information
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->enum('mobility_status', ['independent', 'assisted', 'wheelchair', 'bedridden'])->default('independent');
            $table->text('dietary_requirements')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            // Next of Kin
            $table->string('nok_name')->nullable();
            $table->string('nok_phone')->nullable();
            $table->string('nok_email')->nullable();
            $table->string('nok_relationship')->nullable();
            $table->text('nok_address')->nullable();

            // Risk & Clinical
            $table->enum('fall_risk_level', ['low', 'medium', 'high'])->default('low');
            $table->boolean('dnr_status')->default(false);
            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
