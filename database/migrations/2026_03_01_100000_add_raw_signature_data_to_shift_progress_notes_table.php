<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_progress_notes', function (Blueprint $table) {
            $table->text('raw_signature_data')->nullable()->after('signature_id');
        });
    }

    public function down(): void
    {
        Schema::table('shift_progress_notes', function (Blueprint $table) {
            $table->dropColumn('raw_signature_data');
        });
    }
};
