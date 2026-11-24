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
        Schema::table('hourly_machine_reports', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('production_order_id')->constrained('products')->nullOnDelete();
        });

        Schema::table('material_requisitions', function (Blueprint $table) {
            $table->foreignId('received_by')->nullable()->after('issued_by')->constrained('users')->nullOnDelete();
            $table->dateTime('received_at')->nullable()->after('issued_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hourly_machine_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });

        Schema::table('material_requisitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('received_by');
            $table->dropColumn('received_at');
        });
    }
};
