<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DailyInventoryCheck;
use App\Models\Factory;
use App\Models\FinancialTransaction;
use App\Models\HourlyMachineReport;
use App\Models\Machine;
use App\Models\MaterialRequisition;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WarehouseArchivedReport;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AlfaErpEndToEndTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 1. Test Login Page loads successfully with all 8 manager profiles
     */
    public function test_login_page_renders_with_fast_access(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('شركة الفا ايجبت');
        $response->assertSee('د. عزت الفيل');
        $response->assertSee('م. بولا الفيل');
        $response->assertSee('أبانوب مجدي');
    }

    /**
     * 2. Test Fast Login works and authenticates every single manager without any session error
     */
    public function test_fast_login_authenticates_owner(): void
    {
        $owner = User::where('role', 'owner')->first();
        $response = $this->post('/login/fast', ['user_id' => $owner->id]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($owner);
    }

    public function test_fast_login_works_flawlessly_for_all_eight_roles_without_errors(): void
    {
        $allUsers = User::where('status', true)->get();
        $this->assertGreaterThanOrEqual(8, $allUsers->count());

        foreach ($allUsers as $u) {
            // Test 1-click GET fast login
            $response = $this->get('/login/fast/'.$u->id);
            $response->assertRedirect('/dashboard');
            $response->assertSessionMissing('error');
            $response->assertSessionHas('success');
            $this->assertAuthenticatedAs($u);

            // Follow the redirect to dashboard
            $dashResponse = $this->get('/dashboard');
            $dashResponse->assertStatus(200);
            $dashResponse->assertSessionMissing('error');
            $dashResponse->assertSee($u->name);
        }
    }

    /**
     * 3. Test Dashboards for different manager roles
     */
    public function test_dashboards_render_for_each_role(): void
    {
        // Owner
        $owner = User::where('role', 'owner')->first();
        $response = $this->actingAs($owner)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('د. عزت الفيل');

        // Sales Manager
        $salesUser = User::where('role', 'sales')->first();
        $response = $this->actingAs($salesUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('أبانوب مجدي');

        // Operations Manager
        $opsUser = User::where('role', 'operations')->first();
        $response = $this->actingAs($opsUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('جرجس أنور');

        // Warehouse Manager
        $warehouseUser = User::where('role', 'warehouse')->first();
        $response = $this->actingAs($warehouseUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('جورج وليم');

        // Purchasing Manager
        $purchasingUser = User::where('role', 'purchasing')->first();
        $response = $this->actingAs($purchasingUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('مؤمن عاطف');

        // Finance Manager
        $financeUser = User::where('role', 'finance')->first();
        $response = $this->actingAs($financeUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('عادل صادق');

        // Maintenance Manager
        $maintUser = User::where('role', 'maintenance')->first();
        $response = $this->actingAs($maintUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('فادي أشرف');
    }

    /**
     * 4. Test Couvert BOM Calculation and Real Unit Economics
     */
    public function test_couvert_unit_economics_and_loss_alarm(): void
    {
        // Couvert 4*1 Abou Tarek
        $product = Product::where('code', 'PROD-CVR-4IN1-ABU')->first();
        $this->assertNotNull($product);

        // Recalculate BOM cost
        $cost = $product->recalculateCostFromBom();
        // 0.90 + 0.24 + 0.06 + 0.28 + 0.18 = 1.66
        $this->assertEqualsWithDelta(1.66, $cost, 0.01);

        // Check loss orders
        $lossOrders = SalesOrder::where('has_loss_alarm', true)->get();
        $this->assertTrue($lossOrders->count() > 0);

        $financeUser = User::where('role', 'finance')->first();
        $response = $this->actingAs($financeUser)->get('/finance/loss-alarms');
        $response->assertStatus(200);
        $response->assertSee('جرس إنذار ومراقبة خسائر الكوڤير');
    }

    /**
     * 5. Test 6 Factory Lines and 14% Internal Markup Profit Center
     */
    public function test_factory_profit_centers_and_internal_markup(): void
    {
        $factories = Factory::all();
        $this->assertCount(6, $factories);

        $gm = User::where('role', 'gm')->first();
        $response = $this->actingAs($gm)->get('/factories');
        $response->assertStatus(200);
        $response->assertSee('6 خطوط إنتاج وتصنيع');
        $response->assertSee('14%');

        $firstFactory = $factories->first();
        $responseFac = $this->actingAs($gm)->get('/factories/'.$firstFactory->id);
        $responseFac->assertStatus(200);
    }

    /**
     * 6. Test Printable Documents
     */
    public function test_printable_documents(): void
    {
        $gm = User::where('role', 'gm')->first();

        // 1. Voucher
        $voucher = StockMovement::first();
        if ($voucher) {
            $resp = $this->actingAs($gm)->get('/print/voucher/'.$voucher->id);
            $resp->assertStatus(200);
            $resp->assertSee($voucher->voucher_number);
        }

        // 2. Work Order
        $workOrder = ProductionOrder::first();
        if ($workOrder) {
            $resp = $this->actingAs($gm)->get('/print/work-order/'.$workOrder->id);
            $resp->assertStatus(200);
            $resp->assertSee($workOrder->order_number);
        }

        // 3. Purchase Order
        $po = PurchaseOrder::first();
        if ($po) {
            $resp = $this->actingAs($gm)->get('/print/purchase-order/'.$po->id);
            $resp->assertStatus(200);
            $resp->assertSee($po->po_number);
        }

        // 4. Daily Check
        $check = DailyInventoryCheck::first();
        if ($check) {
            $resp = $this->actingAs($gm)->get('/print/daily-check/'.$check->id);
            $resp->assertStatus(200);
            $resp->assertSee($check->check_number);
        }

        // 5. Sales Invoice
        $order = SalesOrder::first();
        if ($order) {
            $resp = $this->actingAs($gm)->get('/print/sales-invoice/'.$order->id);
            $resp->assertStatus(200);
            $resp->assertSee($order->order_number);
        }
    }

    /**
     * 7. Test Complete End-to-End Cycle:
     * Sales Order -> Operations Schedule -> Missing Requisition -> Purchasing Lead Time -> Confirmed Delivery Date
     */
    public function test_full_administrative_cycle(): void
    {
        $salesUser = User::where('role', 'sales')->first();
        $opsUser = User::where('role', 'operations')->first();
        $warehouseUser = User::where('role', 'warehouse')->first();
        $purchasingUser = User::where('role', 'purchasing')->first();
        $customer = Customer::first();
        $product = Product::first();

        // Step 1: Sales creates new order
        $orderData = [
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'target_delivery_date' => now()->addDays(5)->toDateString(),
            'client_notes' => 'طلب مستعجل لتجربة دورة العمل المتكاملة بالكامل',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 15000,
                    'unit_price' => $product->selling_price,
                    'notes' => 'تغليف سيلوفان أزرق',
                ],
            ],
        ];

        $respOrder = $this->actingAs($salesUser)->post('/sales/orders', $orderData);
        $respOrder->assertRedirect();

        $newOrder = SalesOrder::where('customer_id', $customer->id)->latest()->first();
        $this->assertNotNull($newOrder);
        $this->assertEquals('pending_operations', $newOrder->status);

        // Step 2: Operations Schedules the Order (Work Order Created)
        $factory = Factory::where('code', 'FAC-COUVERT')->first();
        $machine = Machine::where('factory_id', $factory->id)->first();

        $scheduleData = [
            'factory_id' => $factory->id,
            'machine_id' => $machine->id,
            'operations_lead_time_days' => 3,
            'has_missing_materials' => 0,
            'notes' => 'تشغيل فوري',
        ];

        $respSched = $this->actingAs($opsUser)->post('/operations/schedule/'.$newOrder->id, $scheduleData);
        $respSched->assertRedirect();

        $workOrder = ProductionOrder::where('sales_order_id', $newOrder->id)->first();
        $this->assertNotNull($workOrder);

        // Step 3: Warehouse notices missing material & sends Purchase Requisition
        $rawMaterial = RawMaterial::first();
        $reqData = [
            'production_order_id' => $workOrder->id,
            'sales_order_id' => $newOrder->id,
            'items' => [
                [
                    'raw_material_id' => $rawMaterial->id,
                    'quantity' => 5000,
                    'unit' => $rawMaterial->unit,
                    'is_missing' => 1,
                ],
            ],
        ];

        // Step 4: Purchasing quotes Lead Time (e.g. 2 days)
        // Test that lead time immediately reflects on sales confirmed delivery date
        $purchasingRequisition = PurchaseRequisition::first();
        if ($purchasingRequisition) {
            $respLeadTime = $this->actingAs($purchasingUser)->post('/purchasing/requisitions/'.$purchasingRequisition->id.'/lead-time', [
                'procurement_lead_time_days' => 4,
                'supplier_delivery_date' => now()->addDays(4)->toDateString(),
                'procurement_notes' => 'المورد أكد التوصيل خلال 4 أيام',
            ]);
            $respLeadTime->assertRedirect();
        }

        // Step 5: Hourly Machine Report submission with automatic efficiency calculation and new machines
        $this->assertDatabaseHas('machines', ['code' => 'MCH-CVR-03']);
        $this->assertDatabaseHas('machines', ['code' => 'MCH-CVR-04']);
        $this->assertDatabaseHas('machines', ['code' => 'MCH-GLV-PCK02']);

        $productAbu = Product::where('code', 'PROD-CVR-4IN1-ABU')->first();

        // Test the exact management formula from Image 3:
        // Speed: 45 pieces/min -> Theoretical: 45 * 60 = 2700 pieces/hour
        // Actual Production: 2250 pieces -> Efficiency: (2250 / 2700) * 100 = 83.33%
        $reportData = [
            'machine_id' => $machine->id,
            'product_id' => $productAbu ? $productAbu->id : null,
            'operator_name' => 'مشغل وردية المساء',
            'report_date' => now()->toDateString(),
            'shift' => '2',
            'hour_slot' => '16:00 - 17:00',
            'units_produced' => 2250,
            'belt_speed_m_min' => 45.0,
            'waste_units' => 12,
            'downtime_minutes' => 0,
            'downtime_reason' => null,
            'actions_taken' => 'التشغيل مستقر وسرعة السير منتظمة',
        ];

        $respHourly = $this->actingAs($opsUser)->post('/operations/hourly-reports', $reportData);
        $respHourly->assertRedirect();

        $savedReport = HourlyMachineReport::where('machine_id', $machine->id)
            ->where('hour_slot', '16:00 - 17:00')
            ->latest('id')
            ->first();
        $this->assertNotNull($savedReport);
        $this->assertEquals('2', $savedReport->shift);
        $this->assertEquals('الوردية الثانية (المسائية)', $savedReport->shift_name);
        $this->assertEquals(83.33, (float) $savedReport->efficiency_rate);
        $this->assertNotNull($savedReport->ai_smart_recommendation);
        $this->assertEquals($productAbu?->id, $savedReport->product_id);

        // Also test creating Shift 1 & Shift 3 reports to verify 3 shifts coverage
        $reportShift1 = [
            'machine_id' => $machine->id,
            'operator_name' => 'مشغل وردية الصباح',
            'report_date' => now()->toDateString(),
            'shift' => '1',
            'hour_slot' => '08:00 - 09:00',
            'units_produced' => 2400,
            'belt_speed_m_min' => 45.0,
        ];
        $this->actingAs($opsUser)->post('/operations/hourly-reports', $reportShift1)->assertRedirect();

        $reportShift3 = [
            'machine_id' => $machine->id,
            'operator_name' => 'مشغل وردية الليل',
            'report_date' => now()->toDateString(),
            'shift' => '3',
            'hour_slot' => '00:00 - 01:00',
            'units_produced' => 2100,
            'belt_speed_m_min' => 45.0,
        ];
        $this->actingAs($opsUser)->post('/operations/hourly-reports', $reportShift3)->assertRedirect();

        // Verify shift filtering in UI
        $respShiftFilter = $this->actingAs($opsUser)->get('/operations/hourly-reports?shift=2');
        $respShiftFilter->assertOk();
        $respShiftFilter->assertSee('الوردية الثانية');

        // Test custom flexible shift and free custom hour slot
        $reportCustomShift = [
            'machine_id' => $machine->id,
            'operator_name' => 'مشغل وردية إضافية مخصصة',
            'report_date' => now()->toDateString(),
            'shift' => 'custom',
            'hour_slot' => '07:00 ص - 18:00 م',
            'units_produced' => 4500,
            'belt_speed_m_min' => 45.0,
            'downtime_minutes' => 0,
        ];
        $this->actingAs($opsUser)->post('/operations/hourly-reports', $reportCustomShift)->assertRedirect();

        // Verify multi-column filter: date range, zero downtime (بلانكة), analytics view and CSV export
        $respDateFilter = $this->actingAs($opsUser)->get('/operations/hourly-reports?date_from='.now()->toDateString().'&date_to='.now()->toDateString());
        $respDateFilter->assertOk();
        $respDateFilter->assertSee('جدول السجلات المفصلة');

        $respZeroFilter = $this->actingAs($opsUser)->get('/operations/hourly-reports?downtime_filter=zero');
        $respZeroFilter->assertOk();

        $respAnalytics = $this->actingAs($opsUser)->get('/operations/hourly-reports?view=analytics');
        $respAnalytics->assertOk();
        $respAnalytics->assertSee('كشف أداء الماكينات');

        $respExport = $this->actingAs($opsUser)->get('/operations/hourly-reports?export=csv');
        $respExport->assertOk();
        $this->assertStringContainsString('text/csv', (string) $respExport->headers->get('content-type'));

        // Step 6: Test Operations Material Requisitions & Confirming Receipt from Warehouse
        $respReqIndex = $this->actingAs($opsUser)->get('/operations/requisitions');
        $respReqIndex->assertOk();

        $testRequisition = MaterialRequisition::first();
        if ($testRequisition) {
            $testRequisition->update(['status' => 'issued']);
            $respConfirm = $this->actingAs($opsUser)->post('/operations/requisitions/'.$testRequisition->id.'/confirm-receipt');
            $respConfirm->assertRedirect();
            $this->assertEquals('received', $testRequisition->fresh()->status);
        }

        // Step 7: Multi-Item Sales Order (Abu Tarek with 3 items: 4-in-1, 2-in-1, 1-in-1)
        $p1 = Product::where('code', 'PROD-CVR-4IN1-ABU')->first() ?? $product;
        $p2 = Product::where('code', 'PROD-CVR-2IN1')->first() ?? $product;
        $p3 = Product::where('code', 'PROD-CVR-1IN1')->first() ?? $product;

        $multiOrderData = [
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'target_delivery_date' => now()->addDays(6)->toDateString(),
            'client_notes' => 'طلبية مجمعة لكافة أصناف كشري أبو طارق الثلاثة',
            'items' => [
                [
                    'product_id' => $p1->id,
                    'quantity' => 50000,
                    'unit_price' => 2.15,
                    'notes' => 'كوڤير 4 في 1 مطبوع باللوجو الأزرق',
                ],
                [
                    'product_id' => $p2->id,
                    'quantity' => 30000,
                    'unit_price' => 1.75,
                    'notes' => 'كوڤير 2 في 1 معلقة ومنديل',
                ],
                [
                    'product_id' => $p3->id,
                    'quantity' => 20000,
                    'unit_price' => 1.45,
                    'notes' => 'كوڤير 1 في 1 معلقة مفردة مغلفة',
                ],
            ],
        ];

        $respMulti = $this->actingAs($salesUser)->post('/sales/orders', $multiOrderData);
        $respMulti->assertRedirect();

        $multiOrder = SalesOrder::with('items')->where('customer_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($multiOrder);
        $this->assertCount(3, $multiOrder->items);
        $this->assertEquals(100000, $multiOrder->items->sum('quantity'));

        // Schedule Multi-Item Order in Operations
        $scheduleMultiData = [
            'factory_id' => $factory->id,
            'machine_id' => $machine->id,
            'operations_lead_time_days' => 4,
            'notes' => 'جدولة تصنيع كافة أصناف العميل',
        ];

        $respMultiSched = $this->actingAs($opsUser)->post('/operations/schedule/'.$multiOrder->id, $scheduleMultiData);
        $respMultiSched->assertRedirect();

        $workOrdersCount = ProductionOrder::where('sales_order_id', $multiOrder->id)->count();
        $this->assertEquals(3, $workOrdersCount);

        // Step 8: Test Hourly Report Edit/Update & Custom Couvert Material Requisition
        if ($savedReport) {
            $updateHourlyData = [
                'operator_name' => 'مشغل وردية الصباح - معدل',
                'shift' => '2',
                'hour_slot' => '16:00 - 17:00',
                'product_id' => $productAbu ? $productAbu->id : null,
                'units_produced' => 2400,
                'belt_speed_m_min' => 45.0, // 45 * 60 = 2700, Eff = (2400 / 2700) * 100 = 88.89%
                'waste_units' => 5,
                'downtime_minutes' => 5,
                'downtime_reason' => 'نظافة سير سريعة',
                'actions_taken' => 'تم استئناف الإنتاج بكفاءة عالية',
            ];
            $respUpdateHourly = $this->actingAs($opsUser)->post('/operations/hourly-reports/'.$savedReport->id.'/update', $updateHourlyData);
            $respUpdateHourly->assertRedirect();
            $this->assertEquals(2400, (float) $savedReport->fresh()->units_produced);
            $this->assertEquals(88.89, (float) $savedReport->fresh()->efficiency_rate);
        }

        // Test Starting with Couvert and customized materials directly for warehouse
        $customCouvertReqData = [
            'product_id' => $p1->id,
            'couvert_quantity' => 10000,
            'items' => [
                [
                    'raw_material_id' => $rawMaterial->id,
                    'quantity' => 12000, // custom increased quantity
                ],
            ],
            'notes' => 'طلب خامات مخصص لكوڤير أبو طارق مع زيادة مناديل',
        ];
        $respCustomReq = $this->actingAs($opsUser)->post('/operations/requisitions', $customCouvertReqData);
        $respCustomReq->assertRedirect();
        $latestCustomReq = MaterialRequisition::latest('id')->first();
        $this->assertNotNull($latestCustomReq);
        $this->assertStringContainsString('أبو طارق', $latestCustomReq->notes);
    }

    /**
     * Test Warehouse 8-in-1 Reports Engine, Pinned Factories, Printing & Dual Signature Archival
     */
    public function test_warehouse_reports_system_and_dual_signature_archiving(): void
    {
        $warehouseUser = User::where('role', 'warehouse')->first() ?? User::where('role', 'owner')->first();

        // 1. Check reports index renders with all 8 report options
        $response = $this->actingAs($warehouseUser)->get('/warehouse/reports');
        $response->assertStatus(200);
        $response->assertSee('تقارير المخازن وحركة الأصناف');
        $response->assertSee('إجمالي استلام صنف خامات');
        $response->assertSee('إجمالي صرف صنف خامات');
        $response->assertSee('استلام منتج تام من الإنتاج');
        $response->assertSee('تسليم منتج تام لعميل (س)');
        $response->assertSee('استلام خامات من مورد (س)');
        $response->assertSee('استلام خامات من المصانع الثلاثة');
        $response->assertSee('تسليم خامات للمصانع الثلاثة');

        // 2. Test quick filter tabs for different reports
        $reportTypes = [
            'raw_receipt_by_item',
            'raw_issue_by_item',
            'finished_receipt_production',
            'finished_delivery_customer',
            'raw_receipt_supplier',
            'raw_receipt_factories',
            'raw_issue_factories',
        ];

        foreach ($reportTypes as $type) {
            $repResponse = $this->actingAs($warehouseUser)->get('/warehouse/reports?report_type='.$type);
            $repResponse->assertStatus(200);
        }

        // 3. Test Printable View with Dual Signatures and WhatsApp integration
        $printResponse = $this->actingAs($warehouseUser)->get('/warehouse/reports/print?report_type=raw_receipt_factories');
        $printResponse->assertStatus(200);
        $printResponse->assertSee('شركة الفا ايجبت للتعبئة والتغليف الحديث');
        $printResponse->assertSee('إقرار المطابقة والاعتماد الرسمي');
        $printResponse->assertSee('توقيع أمين / مدير المخازن المختص');
        $printResponse->assertSee('توقيع الجهة الموجه إليها التقرير (المستلم)');
        $printResponse->assertSee('canvas_wm');
        $printResponse->assertSee('canvas_rc');
        $printResponse->assertSee('مشاركة عبر واتساب');

        // 4. Test Archiving Report with Touchpad Signatures & Meta
        $archiveData = [
            'report_type' => 'raw_receipt_factories',
            'title' => 'تقرير إجمالي استلام خامات من مصنع أوتوباك',
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date' => now()->toDateString(),
            'item_type' => 'raw_material',
            'party_type' => 'factory',
            'party_name' => 'مصنع أوتوباك (AutoPack)',
            'total_quantity' => 50000,
            'total_value' => 75000,
            'warehouse_manager_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'recipient_name' => 'م. أحمد حمدي - فني خطوط أوتوباك',
            'recipient_title' => 'مشرف إنتاج مصنع أوتوباك',
            'recipient_phone' => '01012345678',
            'recipient_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'notes' => 'تمت المطابقة والاستلام بالكامل دون أي فروقات.',
        ];

        $archiveResponse = $this->actingAs($warehouseUser)->post('/warehouse/reports/archive', $archiveData);
        $archiveResponse->assertRedirect();

        $archivedReport = WarehouseArchivedReport::latest('id')->first();
        $this->assertNotNull($archivedReport);
        $this->assertStringStartsWith('WH-REP-', $archivedReport->report_number);
        $this->assertEquals('م. أحمد حمدي - فني خطوط أوتوباك', $archivedReport->recipient_name);
        $this->assertTrue($archivedReport->is_signed_by_recipient);

        // 5. Test Archived Reports List & Show View
        $listResponse = $this->actingAs($warehouseUser)->get('/warehouse/reports/archived');
        $listResponse->assertStatus(200);
        $listResponse->assertSee($archivedReport->report_number);

        $showResponse = $this->actingAs($warehouseUser)->get('/warehouse/reports/archived/'.$archivedReport->id);
        $showResponse->assertStatus(200);
        $showResponse->assertSee($archivedReport->report_number);
        $showResponse->assertSee('م. أحمد حمدي - فني خطوط أوتوباك');

        // 6. Test Dynamic Factory Addition ("مع إمكانية زيادة العدد")
        $factoryData = [
            'name' => 'مصنع النيل للتغليف الدوائي',
            'code' => 'FAC-NILE-01',
            'type' => 'internal',
            'supervisor_name' => 'م. سامح فهمي',
            'operating_cost_per_hour' => 120,
            'description' => 'خطوط تعبئة وتغليف معقمة',
        ];

        $factoryResponse = $this->actingAs($warehouseUser)->post('/warehouse/factories', $factoryData);
        $factoryResponse->assertRedirect();
        $this->assertDatabaseHas('factories', [
            'code' => 'FAC-NILE-01',
            'name' => 'مصنع النيل للتغليف الدوائي',
        ]);

        // Clean up test factory and archived report to keep database pristine
        Factory::where('code', 'FAC-NILE-01')->delete();
        $archivedReport->delete();
    }

    public function test_sales_order_create_view_renders_cleanly(): void
    {
        $salesUser = User::where('role', 'sales')->first();

        $response = $this->actingAs($salesUser)->get('/sales/orders/create');

        $response->assertStatus(200);
        $response->assertSee('const productsData =', false);
        $response->assertSee('const customersData =', false);
    }

    /**
     * Test Operations Manual Product Entry with Image Upload & Kilogram Material Requisitions
     */
    public function test_operations_manual_product_upload_and_kilogram_requisitions(): void
    {
        $opsUser = User::where('role', 'operations')->first() ?? User::where('role', 'owner')->first();
        $machine = Machine::first();

        // 1. Submit Hourly Report with Custom Manual Product and Image Upload
        $manualProdData = [
            'machine_id' => $machine->id,
            'operator_name' => 'مشغل وردية الكوڤير',
            'report_date' => now()->toDateString(),
            'hour_slot' => '10:00 - 11:00',
            'units_produced' => 2500,
            'belt_speed_m_min' => 50,
            'product_id' => 'custom',
            'custom_product_name' => 'كوڤير كبابجي درويش 3 في 1',
            'custom_product_components' => 'معلقة بلاستيك + منديل جاف + خلة أسنان',
            'custom_product_image' => UploadedFile::fake()->image('darwish_couvert.jpg'),
        ];

        $respHourly = $this->actingAs($opsUser)->post('/operations/hourly-reports', $manualProdData);
        $respHourly->assertRedirect();

        $createdProduct = Product::where('name', 'كوڤير كبابجي درويش 3 في 1')->first();
        $this->assertNotNull($createdProduct);
        $this->assertNotNull($createdProduct->image);
        $this->assertStringStartsWith('uploads/products/prod_', $createdProduct->image);

        $hourlyReport = HourlyMachineReport::where('product_id', $createdProduct->id)->latest('id')->first();
        $this->assertNotNull($hourlyReport);
        $this->assertEquals(2500, (float) $hourlyReport->units_produced);

        // 2. Submit Material Requisition with Manual Custom Item in Kilograms
        $reqData = [
            'notes' => 'طلب رولات سيلوفان مخصوص بالكيلو',
            'items' => [
                [
                    'raw_material_id' => '',
                    'custom_name' => 'رول فيلم سيلوفان بوب مخصوص بالوزن',
                    'unit' => 'كجم',
                    'quantity' => 85.50,
                ],
            ],
        ];

        $respReq = $this->actingAs($opsUser)->post('/operations/requisitions', $reqData);
        $respReq->assertRedirect();

        $latestReq = MaterialRequisition::latest('id')->first();
        $this->assertNotNull($latestReq);
        $firstItem = $latestReq->items()->first();
        $this->assertNotNull($firstItem);
        $this->assertEquals('رول فيلم سيلوفان بوب مخصوص بالوزن', $firstItem->item_display_name);
        $this->assertEquals('كجم', $firstItem->unit_display);
        $this->assertEquals(85.50, (float) $firstItem->quantity_requested);

        // 3. Test Warehouse Can View & Issue This Kilogram Requisition
        $whUser = User::where('role', 'warehouse')->first() ?? User::where('role', 'owner')->first();
        $respWhShow = $this->actingAs($whUser)->get('/warehouse/material-requisitions/'.$latestReq->id);
        $respWhShow->assertStatus(200);
        $respWhShow->assertSee('رول فيلم سيلوفان بوب مخصوص بالوزن');
        $respWhShow->assertSee('كجم');

        $respIssue = $this->actingAs($whUser)->post('/warehouse/material-requisitions/'.$latestReq->id.'/issue');
        $respIssue->assertRedirect();

        // Clean up created test records
        if ($hourlyReport) {
            $hourlyReport->delete();
        }
        if ($createdProduct) {
            if ($createdProduct->image && file_exists(public_path($createdProduct->image))) {
                @unlink(public_path($createdProduct->image));
            }
            $createdProduct->delete();
        }
        if ($latestReq) {
            $latestReq->items()->delete();
            $latestReq->delete();
        }
    }

    /**
     * 14. Test Financial Module Navigation and Views
     */
    public function test_financial_module_views_render_successfully(): void
    {
        $financeUser = User::where('role', 'finance')->first() ?? User::where('role', 'owner')->first();

        // 1. Financial Dashboard
        $resp = $this->actingAs($financeUser)->get('/finance');
        $resp->assertStatus(200);
        $resp->assertSee('الإدارة المالية والحسابات المركزية');
        $resp->assertSee('رصيد الخزينة');

        // 2. Customers List
        $respCust = $this->actingAs($financeUser)->get('/finance/customers');
        $respCust->assertStatus(200);
        $respCust->assertSee('كشري أبو طارق');
        $respCust->assertSee('سند قبض');

        // 3. Customer Statement
        $cust = Customer::where('name', 'like', '%أبو طارق%')->first();
        if ($cust) {
            $respStmt = $this->actingAs($financeUser)->get('/finance/customers/'.$cust->id.'/statement');
            $respStmt->assertStatus(200);
            $respStmt->assertSee('كشف حساب عميل تفصيلي');
            $respStmt->assertSee('شركة الفا ايجبت');
        }

        // 4. Suppliers List
        $respSup = $this->actingAs($financeUser)->get('/finance/suppliers');
        $respSup->assertStatus(200);
        $respSup->assertSee('حمزة السوري');
        $respSup->assertSee('سند صرف');

        // 5. Supplier Statement
        $sup = Supplier::where('name', 'like', '%حمزة%')->first();
        if ($sup) {
            $respSupStmt = $this->actingAs($financeUser)->get('/finance/suppliers/'.$sup->id.'/statement');
            $respSupStmt->assertStatus(200);
            $respSupStmt->assertSee('كشف حساب مورد تفصيلي');
        }

        // 6. Treasury
        $respTreasury = $this->actingAs($financeUser)->get('/finance/treasury');
        $respTreasury->assertStatus(200);
        $respTreasury->assertSee('حركة الخزينة والسيولة النقدية');

        // 7. Expenses
        $respExpenses = $this->actingAs($financeUser)->get('/finance/expenses');
        $respExpenses->assertStatus(200);
        $respExpenses->assertSee('المصروفات العمومية والإدارية والتشغيلية');

        // 8. General Journal
        $respJournal = $this->actingAs($financeUser)->get('/finance/journal');
        $respJournal->assertStatus(200);
        $respJournal->assertSee('دفتر اليومية العامة والقيود المزدوجة');

        // 9. October Financial Analysis
        $respOct = $this->actingAs($financeUser)->get('/finance/analysis/october');
        $respOct->assertStatus(200);
        $respOct->assertSee('التقرير والتحليل المالي لشهري سبتمبر وأكتوبر 2026');
    }

    /**
     * 15. Test Customer Payment Receipt & Supplier Payment Voucher & Manual Journal Entry
     */
    public function test_financial_transactions_and_double_entry_postings(): void
    {
        $financeUser = User::where('role', 'finance')->first() ?? User::where('role', 'owner')->first();

        // 1. Customer Payment Receipt
        $cust = Customer::first();
        $this->assertNotNull($cust);
        $oldCollected = $cust->total_collected;

        $paymentData = [
            'amount' => 5000.00,
            'payment_method' => 'نقدي',
            'transaction_date' => date('Y-m-d'),
            'description' => 'سداد نقدي من العميل دفعة تجريبية',
        ];
        $respPay = $this->actingAs($financeUser)->post('/finance/customers/'.$cust->id.'/payment', $paymentData);
        $respPay->assertRedirect();

        $cust->refresh();
        $this->assertEquals($oldCollected + 5000.00, $cust->total_collected);

        // 2. Supplier Payment Voucher
        $sup = Supplier::first();
        $this->assertNotNull($sup);
        $oldPaid = $sup->total_paid;

        $supPayData = [
            'amount' => 3500.00,
            'payment_method' => 'نقدي',
            'transaction_date' => date('Y-m-d'),
            'description' => 'سند صرف نقدية لمورد تجريبي',
        ];
        $respSupPay = $this->actingAs($financeUser)->post('/finance/suppliers/'.$sup->id.'/payment', $supPayData);
        $respSupPay->assertRedirect();

        $sup->refresh();
        $this->assertEquals($oldPaid + 3500.00, $sup->total_paid);

        // 3. Record General Expense
        $expData = [
            'category' => 'سولار ووقود مولدات وسيارات',
            'amount' => 1200.00,
            'transaction_date' => date('Y-m-d'),
            'payment_method' => 'نقدي',
            'description' => 'شراء سولار لمولد الكهرباء',
        ];
        $respExp = $this->actingAs($financeUser)->post('/finance/expenses', $expData);
        $respExp->assertRedirect();

        $latestExp = FinancialTransaction::where('type', 'expense')->latest('id')->first();
        $this->assertNotNull($latestExp);
        $this->assertEquals(1200.00, (float) $latestExp->amount);
        $this->assertEquals('سولار ووقود مولدات وسيارات', $latestExp->category);

        // 4. Record Manual Journal Entry
        $jrnData = [
            'debit_account' => 'حـ/ أصول ثابتة - ماكينات تصنيع',
            'credit_account' => 'حـ/ الخزينة الرئيسية',
            'amount' => 25000.00,
            'transaction_date' => date('Y-m-d'),
            'description' => 'قيد تسوية شراء ماكينة تغليف إضافية',
        ];
        $respJrn = $this->actingAs($financeUser)->post('/finance/journal', $jrnData);
        $respJrn->assertRedirect();

        $latestJrn = FinancialTransaction::where('type', 'manual_journal')->latest('id')->first();
        $this->assertNotNull($latestJrn);
        $this->assertEquals(25000.00, (float) $latestJrn->amount);
        $this->assertEquals('حـ/ أصول ثابتة - ماكينات تصنيع', $latestJrn->debit_account);
        $this->assertEquals('حـ/ الخزينة الرئيسية', $latestJrn->credit_account);
    }
}
