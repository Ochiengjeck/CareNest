<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->dateTime('recorded_at');
            $table->smallInteger('blood_pressure_systolic')->nullable();
            $table->smallInteger('blood_pressure_diastolic')->nullable();
            $table->smallInteger('heart_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->smallInteger('respiratory_rate')->nullable();
            $table->smallInteger('oxygen_saturation')->nullable();
            $table->decimal('blood_sugar', 5, 1)->nullable();
            $table->decimal('weight', 5, 1)->nullable();
            $table->tinyInteger('pain_level')->nullable();
            $table->enum('consciousness_level', ['alert', 'verbal', 'pain', 'unresponsive'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vitals');
    }
};
