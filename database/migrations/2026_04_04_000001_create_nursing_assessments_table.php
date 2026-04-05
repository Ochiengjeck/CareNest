<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursing_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->dateTime('assessment_date');
            $table->json('safety_screening')->nullable();
            $table->json('substance_use_check')->nullable();
            $table->text('physical_condition')->nullable();
            $table->text('nursing_intake_note')->nullable();
            $table->enum('risk_level', ['low', 'moderate', 'high', 'imminent'])->default('low');
            $table->text('risk_assessment_notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursing_assessments');
    }
};
