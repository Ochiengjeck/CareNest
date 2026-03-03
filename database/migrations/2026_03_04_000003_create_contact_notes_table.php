<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->text('diagnosis')->nullable();
            $table->date('contact_date');
            $table->string('contact_time')->nullable();
            $table->json('person_contacted')->nullable();
            $table->string('contact_name')->nullable();
            $table->json('mode_of_contact')->nullable();
            $table->string('mode_other')->nullable();
            $table->text('contact_summary')->nullable();
            $table->boolean('emergency_issue')->nullable();
            $table->json('signers')->nullable();
            $table->foreignId('signature_id')->nullable()->constrained('signatures')->nullOnDelete();
            $table->text('raw_signature_data')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_notes');
    }
};
