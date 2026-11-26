<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            if (! Schema::hasColumn('raw_materials', 'image')) {
                $table->string('image')->nullable()->after('warehouse_location');
            }
            if (! Schema::hasColumn('raw_materials', 'package_info')) {
                $table->string('package_info')->nullable()->after('image');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'package_info')) {
                $table->string('package_info')->nullable()->after('image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropColumn(['image', 'package_info']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['package_info']);
        });
    }
};
