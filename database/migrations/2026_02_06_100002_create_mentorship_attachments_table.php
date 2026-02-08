<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorship_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('mentorship_topics')->cascadeOnDelete();

            // File info
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 20);
            $table->unsignedBigInteger('file_size');

            // Metadata
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);

            // Audit
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_attachments');
    }
};
