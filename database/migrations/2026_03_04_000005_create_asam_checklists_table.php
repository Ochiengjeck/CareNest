<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asam_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->text('diagnosis')->nullable();
            $table->date('discharge_date')->nullable();
            $table->json('dimension_1')->nullable();
            $table->json('dimension_2')->nullable();
            $table->json('dimension_3')->nullable();
            $table->json('dimension_4')->nullable();
            $table->json('dimension_5')->nullable();
            $table->json('dimension_6')->nullable();
            $table->text('asam_score')->nullable();
            $table->text('level_of_care')->nullable();
            $table->string('residential')->nullable();
            $table->text('comment')->nullable();
            $table->json('signers')->nullable();
            $table->foreignId('signature_id')->nullable()->constrained('signatures')->nullOnDelete();
            $table->text('raw_signature_data')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asam_checklists');
    }
};
