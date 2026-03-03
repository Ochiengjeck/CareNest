<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('safety_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->text('diagnosis')->nullable();
            $table->json('warning_signs')->nullable();
            $table->json('coping_strategies')->nullable();
            $table->json('distraction_people')->nullable();
            $table->json('distraction_places')->nullable();
            $table->json('help_people')->nullable();
            $table->json('crisis_professionals')->nullable();
            $table->text('environment_safety')->nullable();
            $table->json('signers')->nullable();
            $table->foreignId('signature_id')->nullable()->constrained('signatures')->nullOnDelete();
            $table->text('raw_signature_data')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('safety_plans');
    }
};
