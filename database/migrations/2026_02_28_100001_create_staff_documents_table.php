<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('category', [
                'personal_information',
                'offer_letter',
                'employment_contract',
                'job_description',
                'tb_risk_assessment',
                'background_dbs_check',
                'right_to_work',
                'care_certificate',
                'tax_documents',
                'compliance_forms',
                'termination_documents',
                'other',
            ])->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->year('document_year')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 10);
            $table->unsignedInteger('file_size');
            $table->enum('status', ['pending', 'completed', 'expired', 'requires_update'])
                  ->default('completed')->index();
            $table->date('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_documents');
    }
};
