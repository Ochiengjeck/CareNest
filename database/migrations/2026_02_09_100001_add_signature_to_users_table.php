<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('signature_data')->nullable()->after('remember_token');
            $table->timestamp('signature_updated_at')->nullable()->after('signature_data');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['signature_data', 'signature_updated_at']);
        });
    }
};
