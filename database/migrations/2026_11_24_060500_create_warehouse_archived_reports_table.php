<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_archived_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->string('report_type'); // receipt_raw_materials, issue_raw_materials, receipt_finished_goods, delivery_customer, receipt_supplier, receipt_factory, delivery_factory
            $table->string('title');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('item_type')->nullable(); // raw_material, product, all
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_name')->nullable();
            $table->string('party_type')->nullable(); // customer, supplier, factory, general
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('party_name')->nullable();
            $table->decimal('total_quantity', 14, 2)->default(0);
            $table->decimal('total_value', 14, 2)->default(0);
            $table->integer('items_count')->default(0);
            $table->json('data_payload')->nullable();
            $table->foreignId('warehouse_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('warehouse_manager_signature')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_title')->nullable();
            $table->longText('recipient_signature')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->boolean('is_signed_by_recipient')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_archived_reports');
    }
};
