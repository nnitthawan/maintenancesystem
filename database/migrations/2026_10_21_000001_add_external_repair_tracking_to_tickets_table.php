<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('external_repair_location')->nullable()->after('repair_method');
            $table->date('external_repair_returned_at')->nullable()->after('external_repair_location');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['external_repair_location', 'external_repair_returned_at']);
        });
    }
};
