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
        Schema::table('mentorship_lessons', function (Blueprint $table) {
            $table->enum('visibility', ['private', 'shared'])->default('private')->after('is_published');
            $table->unsignedInteger('times_used')->default(0)->after('visibility');
            $table->index('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorship_lessons', function (Blueprint $table) {
            $table->dropIndex(['visibility']);
            $table->dropColumn(['visibility', 'times_used']);
        });
    }
};
