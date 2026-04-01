<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_plan_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->text('problem_description');
            $table->text('case_manager_actions')->nullable();
            $table->text('client_actions')->nullable();
            $table->enum('progress_status', ['not_started', 'making_progress', 'achieved', 'not_achieved'])->default('not_started');
            $table->date('target_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_plan_goals');
    }
};
