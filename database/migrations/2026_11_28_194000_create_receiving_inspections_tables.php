<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receiving_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_number')->unique();
            $table->date('inspection_date');
            $table->string('source_type')->default('supplier'); // supplier, internal_factory, other
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('factory_id')->nullable()->constrained('factories')->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->string('supplier_invoice_number')->nullable();
            $table->string('delivered_by')->nullable();
            $table->string('inspector_name');
            $table->string('quality_member_name')->nullable();
            $table->string('overall_status')->default('accepted'); // accepted, partial, rejected
            $table->boolean('add_to_stock')->default(true);
            $table->foreignId('stock_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
            $table->foreignId('warehouse_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('receiving_inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_inspection_id')->constrained('receiving_inspections')->cascadeOnDelete();
            $table->string('item_type')->default('raw_material'); // raw_material, spare_part, product
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_name');
            $table->string('unit')->default('قطعة');
            $table->decimal('delivered_quantity', 14, 3);
            $table->decimal('accepted_quantity', 14, 3);
            $table->decimal('rejected_quantity', 14, 3)->default(0);
            $table->string('inspection_result')->default('accepted'); // accepted, partial, rejected
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receiving_inspection_items');
        Schema::dropIfExists('receiving_inspections');
    }
};
