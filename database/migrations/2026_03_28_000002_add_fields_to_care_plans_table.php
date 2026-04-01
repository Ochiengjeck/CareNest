<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('care_plans', function (Blueprint $table) {
            $table->json('recovery_team')->nullable()->after('notes');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('care_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn('recovery_team');
        });
    }
};
