<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_requisition_items', function (Blueprint $table) {
            if (!Schema::hasColumn('material_requisition_items', 'unit')) {
                $table->string('unit')->default('قطعة')->nullable()->after('quantity_requested');
            }
            if (!Schema::hasColumn('material_requisition_items', 'custom_material_name')) {
                $table->string('custom_material_name')->nullable()->after('unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('material_requisition_items', function (Blueprint $table) {
            $table->dropColumn(['unit', 'custom_material_name']);
        });
    }
};
