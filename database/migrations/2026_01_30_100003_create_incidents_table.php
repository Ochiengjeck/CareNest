<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->enum('type', ['fall', 'medication_error', 'injury', 'behavioral', 'equipment_failure', 'other']);
            $table->enum('severity', ['minor', 'moderate', 'major', 'critical']);
            $table->dateTime('occurred_at');
            $table->string('location')->nullable();
            $table->text('description');
            $table->text('immediate_actions')->nullable();
            $table->text('witnesses')->nullable();
            $table->text('outcome')->nullable();
            $table->text('follow_up_actions')->nullable();
            $table->enum('status', ['open', 'under_investigation', 'resolved', 'closed'])->default('open')->index();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
