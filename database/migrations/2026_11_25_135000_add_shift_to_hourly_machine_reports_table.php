<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hourly_machine_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('hourly_machine_reports', 'shift')) {
                $table->string('shift', 20)->default('1')->nullable()->after('report_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hourly_machine_reports', function (Blueprint $table) {
            $table->dropColumn('shift');
        });
    }
};
