<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->text('diagnosis')->nullable();
            $table->string('recipient_person_agency')->nullable();
            $table->text('recipient_address')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('recipient_fax')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('agency_name')->nullable();
            $table->json('information_released')->nullable();
            $table->text('purpose')->nullable();
            $table->string('expiration_type')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('expiration_other')->nullable();
            $table->foreignId('employee_signature_id')->nullable()->constrained('signatures')->nullOnDelete();
            $table->text('employee_raw_signature_data')->nullable();
            $table->text('resident_raw_signature_data')->nullable();
            $table->string('witness')->nullable();
            $table->json('signers')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorizations');
    }
};
