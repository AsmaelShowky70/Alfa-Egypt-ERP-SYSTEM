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
        Schema::table('raw_materials', function (Blueprint $table) {
            if (! Schema::hasColumn('raw_materials', 'image')) {
                $table->string('image')->nullable()->after('reorder_level');
            }
            if (! Schema::hasColumn('raw_materials', 'package_info')) {
                $table->string('package_info')->nullable()->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            if (Schema::hasColumn('raw_materials', 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn('raw_materials', 'package_info')) {
                $table->dropColumn('package_info');
            }
        });
    }
};
