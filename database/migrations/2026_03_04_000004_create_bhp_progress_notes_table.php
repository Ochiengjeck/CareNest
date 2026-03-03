<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bhp_progress_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->text('diagnosis')->nullable();
            $table->date('discharge_date')->nullable();
            $table->text('progress_note')->nullable();
            $table->text('treatment_goals_progress')->nullable();
            $table->text('sobriety_physical_health')->nullable();
            $table->text('cognitive_emotional')->nullable();
            $table->text('therapeutic_support')->nullable();
            $table->text('progress_towards_goals')->nullable();
            $table->text('barriers')->nullable();
            $table->text('summary_continued_stay')->nullable();
            $table->string('bhp_name_credential')->nullable();
            $table->json('signers')->nullable();
            $table->foreignId('signature_id')->nullable()->constrained('signatures')->nullOnDelete();
            $table->text('raw_signature_data')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bhp_progress_notes');
    }
};
