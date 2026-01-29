<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['general', 'nutrition', 'mobility', 'mental_health', 'personal_care', 'medication', 'social']);
            $table->enum('status', ['active', 'draft', 'archived', 'under_review'])->default('draft')->index();

            $table->date('start_date');
            $table->date('review_date')->nullable();

            $table->text('description')->nullable();
            $table->text('goals')->nullable();
            $table->text('interventions')->nullable();
            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_plans');
    }
};
