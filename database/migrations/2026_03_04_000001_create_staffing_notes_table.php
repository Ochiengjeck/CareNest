<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staffing_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->text('diagnosis')->nullable();
            $table->date('note_date');
            $table->string('begin_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('participant')->nullable();
            $table->text('presenting_issues')->nullable();
            $table->boolean('conducted_within_30_days')->nullable();
            $table->boolean('treatment_plan_requested')->nullable();
            $table->boolean('step_down_discussed')->nullable();
            $table->text('goals_addressed')->nullable();
            $table->text('note_summary')->nullable();
            $table->text('barriers')->nullable();
            $table->text('not_conducted_reason')->nullable();
            $table->text('recommendations')->nullable();
            $table->json('signers')->nullable();
            $table->foreignId('signature_id')->nullable()->constrained('signatures')->nullOnDelete();
            $table->text('raw_signature_data')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staffing_notes');
    }
};
