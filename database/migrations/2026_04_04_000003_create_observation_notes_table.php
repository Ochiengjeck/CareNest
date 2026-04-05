<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observation_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->dateTime('observed_at');
            $table->enum('observation_type', ['every_15_min', 'every_30_min', 'one_to_one', 'continuous'])->default('every_15_min');
            $table->text('behavior')->nullable();
            $table->string('location')->nullable();
            $table->string('mood_affect')->nullable();
            $table->enum('safety_status', ['safe', 'at_risk', 'unsafe'])->default('safe');
            $table->text('notes')->nullable();
            $table->foreignId('observed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_notes');
    }
};
