<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discharges', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('admin_override_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            $table->timestamp('admin_override_at')->nullable()->after('admin_override_by');
            $table->timestamp('ai_generated_at')->nullable()->after('admin_override_at');
            $table->json('selected_agencies')->nullable()->after('future_appointments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discharges', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['admin_override_by']);
            $table->dropColumn(['updated_by', 'admin_override_by', 'admin_override_at', 'ai_generated_at', 'selected_agencies']);
        });
    }
};
