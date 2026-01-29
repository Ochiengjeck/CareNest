<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('dosage');
            $table->string('frequency');
            $table->enum('route', ['oral', 'topical', 'injection', 'inhalation', 'sublingual', 'rectal', 'other'])->default('oral');
            $table->string('prescribed_by');
            $table->date('prescribed_date');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'discontinued', 'on_hold'])->default('active')->index();
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
