<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medication_logs', function (Blueprint $table) {
            $table->string('initials', 10)->nullable()->after('notes');
            $table->string('slot_time', 5)->nullable()->after('initials');
            $table->text('raw_signature_data')->nullable()->after('slot_time');
            $table->foreignId('signature_id')->nullable()->constrained('signatures')->nullOnDelete()->after('raw_signature_data');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE medication_logs MODIFY COLUMN status ENUM('given','refused','withheld','missed','hospital','home_pass','on_hold','unavailable','discontinued')");
        }
    }

    public function down(): void
    {
        Schema::table('medication_logs', function (Blueprint $table) {
            $table->dropForeign(['signature_id']);
            $table->dropColumn(['initials', 'slot_time', 'raw_signature_data', 'signature_id']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE medication_logs MODIFY COLUMN status ENUM('given','refused','withheld','missed')");
        }
    }
};
