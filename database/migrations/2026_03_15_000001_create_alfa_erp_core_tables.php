<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Customers (العملاء ومطاعم كبرى)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. كشري أبو طارق
            $table->string('code')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('commercial_register')->nullable();
            $table->string('logo')->nullable();
            $table->string('payment_terms')->default('آجل 30 يوم');
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 2. Suppliers (الموردون وجهات التصنيع الخارجي)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('category')->default('خامات'); // خامات, قطع غيار, تصنيع خارجي عند الغير
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->integer('average_lead_time_days')->default(3); // متوسط مدة التوريد بالأيام
            $table->decimal('rating', 3, 1)->default(5.0);
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 3. Factories & Independent Production Lines (المصانع وخطوط الإنتاج المستقلة)
        Schema::create('factories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. مصنع الكوڤير، خط قص الجوانتيات، حقن البلاستيك، رولات التغليف، الوايبس
            $table->string('code')->unique();
            $table->enum('type', ['internal', 'outsourced'])->default('internal'); // داخلي، أو عند الغير
            $table->foreignId('outsource_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('supervisor_name')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('operating_cost_per_hour', 12, 2)->default(0);
            $table->decimal('markup_percentage', 5, 2)->default(14.00); // 14% تكلفة إنتاج عند البيع للمخزن المركزي
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. Machines (ماكينات الإنتاج)
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factory_id')->constrained('factories')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->decimal('rated_speed_units_per_hour', 10, 2)->default(1000);
            $table->decimal('belt_speed_m_min', 8, 2)->default(25);
            $table->string('status')->default('running'); // running, idle, maintenance, breakdown
            $table->date('maintenance_due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Raw Materials & Spare Parts (المواد الخام وقطع الغيار ومستلزمات الإنتاج)
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name'); // e.g. معلقة إنجليزي, منديل جاف, خلة صيني, منديل معطر, غلاف شفاف, رول سيلوفان, خرز بلاستيك
            $table->string('category')->default('raw_material'); // raw_material, packaging, spare_part
            $table->string('unit')->default('قطعة'); // قطعة, كجم, متر, رول, كرتونة
            $table->decimal('unit_cost', 10, 4)->default(0); // التكلفة بالجنيه المصري (مثلاً 0.90 جنيه للمعلقة)
            $table->decimal('current_stock', 14, 2)->default(0);
            $table->decimal('reorder_level', 14, 2)->default(1000); // حد إعادة الطلب
            $table->string('warehouse_location')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('source_factory_id')->nullable()->constrained('factories')->nullOnDelete(); // لو منتجة من خط داخلي
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 6. Products (المنتجات التامة والوسيطة - الكوڤير والوايبس والجوانتيات)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name'); // e.g. كوڤير 4*1 أزرق - كشري أبو طارق
            $table->string('category')->default('couvert'); // couvert, wipes, gloves, plastic_cutlery, packaging_roll
            $table->string('type')->default('finished_good'); // finished_good, intermediate
            $table->string('unit')->default('قطعة'); // قطعة, كرتونة
            $table->foreignId('factory_id')->nullable()->constrained('factories')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete(); // إذا كان مخصصاً لعميل
            $table->decimal('cost_price', 10, 4)->default(0); // التكلفة المحسوبة من المكونات
            $table->decimal('selling_price', 10, 4)->default(0); // سعر البيع للعميل
            $table->decimal('min_selling_price', 10, 4)->default(0); // الحد الأدنى المسموح للبيع
            $table->decimal('current_stock', 14, 2)->default(0);
            $table->decimal('reorder_level', 14, 2)->default(500);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 7. Bill of Materials (BOM - تركيبة المنتج ومكونات الكوڤير)
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('raw_materials')->cascadeOnDelete();
            $table->decimal('quantity_per_unit', 10, 4)->default(1); // كمية الخامة لكل قطعة منتج تام
            $table->decimal('waste_percentage', 5, 2)->default(0); // نسبة الهالك
            $table->decimal('unit_cost_snapshot', 10, 4)->default(0);
            $table->decimal('total_cost_per_unit', 10, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 8. Sales Orders (طلبات المبيعات)
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('sales_rep_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending_operations'); 
            // pending_operations, operations_scheduled, procurement_delayed, confirmed, in_production, ready_in_warehouse, completed, cancelled
            $table->date('order_date')->default(now());
            $table->date('target_delivery_date')->nullable(); // التاريخ المطلوب من العميل
            $table->date('confirmed_delivery_date')->nullable(); // التاريخ النهائي المعتمد بعد مراجعة التشغيل والمشتريات
            $table->integer('operations_lead_time_days')->default(0); // مدة التصنيع بالأيام
            $table->integer('procurement_lead_time_days')->default(0); // مدة توفير النواقص بالأيام
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('total_estimated_cost', 14, 2)->default(0);
            $table->decimal('profit_margin_percentage', 6, 2)->default(0);
            $table->boolean('has_loss_alarm')->default(false); // إنذار خسارة إذا كان السعر الثابت أقل من التكلفة!
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid
            $table->text('client_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });

        // 9. Sales Order Items
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 14, 2);
            $table->decimal('unit_price', 10, 4);
            $table->decimal('unit_cost', 10, 4);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('total_cost', 14, 2);
            $table->boolean('has_loss_item')->default(false);
            $table->text('customization_notes')->nullable();
            $table->timestamps();
        });

        // 10. Production Orders / Work Orders (أوامر التشغيل)
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('factory_id')->constrained('factories')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();
            $table->decimal('planned_quantity', 14, 2);
            $table->decimal('produced_quantity', 14, 2)->default(0);
            $table->decimal('defective_quantity', 14, 2)->default(0);
            $table->dateTime('scheduled_start_date')->nullable();
            $table->dateTime('scheduled_end_date')->nullable();
            $table->dateTime('actual_start_date')->nullable();
            $table->dateTime('actual_end_date')->nullable();
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->string('status')->default('draft'); 
            // draft, waiting_materials, ready, in_progress, completed, paused, cancelled
            $table->foreignId('operations_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        // 11. Material Requisitions (طلبات صرف مستلزمات من المخزن للتشغيل)
        Schema::create('material_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('req_number')->unique();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->string('status')->default('requested'); // requested, partial_issued, fully_issued, rejected
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('requested_at')->default(now());
            $table->dateTime('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 12. Material Requisition Items
        Schema::create('material_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_requisition_id')->constrained('material_requisitions')->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('raw_materials')->cascadeOnDelete();
            $table->decimal('quantity_requested', 14, 2);
            $table->decimal('quantity_issued', 14, 2)->default(0);
            $table->decimal('quantity_missing', 14, 2)->default(0);
            $table->decimal('unit_cost', 10, 4)->default(0);
            $table->timestamps();
        });

        // 13. Purchase Requisitions (طلبات شراء خامات ناقصة من المخزن للمشتريات)
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->unique();
            $table->foreignId('warehouse_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('production_order_id')->nullable()->constrained('production_orders')->nullOnDelete();
            $table->string('status')->default('pending_procurement'); 
            // pending_procurement, lead_time_quoted, po_issued, delivered_to_warehouse, cancelled
            $table->integer('procurement_lead_time_days')->default(0); // رد المشتريات بالمدة
            $table->date('estimated_arrival_date')->nullable();
            $table->string('urgency')->default('normal'); // normal, high, critical
            $table->text('reason')->nullable();
            $table->text('procurement_reply_notes')->nullable();
            $table->timestamps();
        });

        // 14. Purchase Requisition Items
        Schema::create('purchase_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requisition_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('raw_materials')->cascadeOnDelete();
            $table->decimal('needed_quantity', 14, 2);
            $table->decimal('current_stock_at_request', 14, 2)->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('estimated_unit_price', 10, 4)->default(0);
            $table->integer('lead_time_days')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 15. Purchase Orders (أوامر التوريد للموردين أو طلبات الشراء من المصانع الداخلية بـ 14%)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('purchase_requisition_id')->nullable()->constrained('purchase_requisitions')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->boolean('is_internal_factory')->default(false); // شراء من مصنع داخلي
            $table->foreignId('from_factory_id')->nullable()->constrained('factories')->nullOnDelete();
            $table->string('destination_warehouse')->default('المخزن المركزي');
            $table->decimal('internal_markup_percentage', 5, 2)->default(14.00); // 14% تكلفة إنتاج تشغيلية
            $table->string('status')->default('issued'); 
            // issued, confirmed, in_transit, received_in_warehouse, cancelled
            $table->date('order_date')->default(now());
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('internal_markup_amount', 14, 2)->default(0); // قيمة الـ 14%
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('purchasing_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 16. Purchase Order Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('raw_material_id')->nullable()->constrained('raw_materials')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('quantity', 14, 2);
            $table->decimal('unit_price', 10, 4);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('received_quantity', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 17. Stock Movements / Vouchers (سندات المخزن: استلام وصرف وتحويل وأرشفة وطباعة)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->string('type'); 
            // receipt_supplier, receipt_factory_internal, issue_production, delivery_customer, inventory_adjustment, spare_part_maintenance
            $table->string('reference_type')->nullable(); // purchase_order, production_order, sales_order, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('warehouse_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('voucher_date')->default(now());
            $table->decimal('total_value', 14, 2)->default(0);
            $table->string('received_or_delivered_by')->nullable(); // المستلم أو المسلّم
            $table->boolean('is_archived')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 18. Stock Movement Items
        Schema::create('stock_movement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_movement_id')->constrained('stock_movements')->cascadeOnDelete();
            $table->string('item_type'); // raw_material, product
            $table->unsignedBigInteger('item_id');
            $table->string('item_name');
            $table->string('unit')->default('قطعة');
            $table->decimal('quantity', 14, 2);
            $table->decimal('unit_cost', 10, 4)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 19. Daily Inventory Checks (الجرد اليومي: خامات، منتج تام، قطع غيار)
        Schema::create('daily_inventory_checks', function (Blueprint $table) {
            $table->id();
            $table->string('check_code')->unique();
            $table->date('check_date')->default(now());
            $table->foreignId('warehouse_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('category', ['raw_materials', 'finished_goods', 'spare_parts'])->default('raw_materials');
            $table->string('status')->default('completed'); // draft, submitted, approved
            $table->decimal('total_surplus_value', 14, 2)->default(0);
            $table->decimal('total_deficit_value', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 20. Daily Inventory Check Items
        Schema::create('daily_inventory_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_inventory_check_id')->constrained('daily_inventory_checks')->cascadeOnDelete();
            $table->string('item_type'); // raw_material, product
            $table->unsignedBigInteger('item_id');
            $table->string('item_name');
            $table->string('unit')->default('قطعة');
            $table->decimal('system_quantity', 14, 2);
            $table->decimal('actual_quantity', 14, 2);
            $table->decimal('difference', 14, 2); // actual - system
            $table->decimal('unit_cost', 10, 4);
            $table->decimal('financial_impact', 14, 2); // difference * unit_cost
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 21. Hourly Machine Reports (تقارير كل ساعة للمكن وخطوط الإنتاج)
        Schema::create('hourly_machine_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->foreignId('factory_id')->constrained('factories')->cascadeOnDelete();
            $table->foreignId('production_order_id')->nullable()->constrained('production_orders')->nullOnDelete();
            $table->string('operator_name');
            $table->date('report_date')->default(now());
            $table->string('hour_slot'); // e.g. 08:00 - 09:00
            $table->decimal('units_produced', 12, 2);
            $table->decimal('belt_speed_m_min', 8, 2)->default(0);
            $table->decimal('efficiency_rate', 5, 2)->default(100); // % كفاءة الماكينة OEE
            $table->decimal('waste_units', 10, 2)->default(0);
            $table->integer('downtime_minutes')->default(0);
            $table->string('downtime_reason')->nullable();
            $table->text('actions_taken')->nullable();
            $table->text('ai_smart_recommendation')->nullable(); // توصية ذكية للقرار للإدارة
            $table->boolean('submitted_to_management')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 22. Financial Transactions (المصروفات، الإيرادات، التكاليف الثابتة والمتغيرة، وتكلفة المصانع)
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->string('type'); 
            // sales_invoice, customer_payment, supplier_bill, supplier_payment, factory_internal_14_markup, fixed_cost, variable_cost, maintenance, salary, utility
            $table->string('category'); // إيرادات مبيعات، تكلفة خامات، تشغيل داخلي 14%، كهرباء وغاز، أجور، صيانة، مصاريف إدارية
            $table->decimal('amount', 14, 2);
            $table->date('transaction_date')->default(now());
            $table->string('payment_method')->default('نقدي'); // نقدي، شيك، تحويل بنكي
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('factory_id')->nullable()->constrained('factories')->nullOnDelete(); // لتحديد أرباح وخسائر كل مصنع مستقل
            $table->string('description');
            $table->string('attachment')->nullable(); // ملف أو صورة الفاتورة
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('hourly_machine_reports');
        Schema::dropIfExists('daily_inventory_check_items');
        Schema::dropIfExists('daily_inventory_checks');
        Schema::dropIfExists('stock_movement_items');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_requisition_items');
        Schema::dropIfExists('purchase_requisitions');
        Schema::dropIfExists('material_requisition_items');
        Schema::dropIfExists('material_requisitions');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('bill_of_materials');
        Schema::dropIfExists('products');
        Schema::dropIfExists('raw_materials');
        Schema::dropIfExists('machines');
        Schema::dropIfExists('factories');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
    }
};
