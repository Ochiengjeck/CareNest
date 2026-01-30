<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapy_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->date('session_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('service_type', [
                'individual',
                'group',
                'intake_assessment',
                'crisis',
                'collateral',
                'case_management',
                'treatment_planning',
                'discharge',
                'other',
            ])->index();
            $table->enum('challenge_index', [
                'substance_use',
                'mental_health',
                'physical_health',
                'employment_education',
                'financial_housing',
                'legal',
                'psychosocial_family',
                'spirituality',
            ])->nullable();
            $table->string('session_topic');
            $table->text('interventions')->nullable();
            $table->text('progress_notes')->nullable();
            $table->text('client_plan')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])
                ->default('scheduled')
                ->index();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_signed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapy_sessions');
    }
};
