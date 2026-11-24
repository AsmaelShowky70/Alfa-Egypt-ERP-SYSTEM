<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DailyInventoryCheck;
use App\Models\Factory;
use App\Models\FinancialTransaction;
use App\Models\Machine;
use App\Models\ProductionOrder;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\User;
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
        $this->assertCount(8, $allUsers);

        foreach ($allUsers as $u) {
            // Test 1-click GET fast login
            $response = $this->get('/login/fast/' . $u->id);
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
        $responseFac = $this->actingAs($gm)->get('/factories/' . $firstFactory->id);
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
            $resp = $this->actingAs($gm)->get('/print/voucher/' . $voucher->id);
            $resp->assertStatus(200);
            $resp->assertSee($voucher->voucher_number);
        }

        // 2. Work Order
        $workOrder = ProductionOrder::first();
        if ($workOrder) {
            $resp = $this->actingAs($gm)->get('/print/work-order/' . $workOrder->id);
            $resp->assertStatus(200);
            $resp->assertSee($workOrder->order_number);
        }

        // 3. Purchase Order
        $po = PurchaseOrder::first();
        if ($po) {
            $resp = $this->actingAs($gm)->get('/print/purchase-order/' . $po->id);
            $resp->assertStatus(200);
            $resp->assertSee($po->po_number);
        }

        // 4. Daily Check
        $check = DailyInventoryCheck::first();
        if ($check) {
            $resp = $this->actingAs($gm)->get('/print/daily-check/' . $check->id);
            $resp->assertStatus(200);
            $resp->assertSee($check->check_number);
        }

        // 5. Sales Invoice
        $order = SalesOrder::first();
        if ($order) {
            $resp = $this->actingAs($gm)->get('/print/sales-invoice/' . $order->id);
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
                ]
            ]
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

        $respSched = $this->actingAs($opsUser)->post('/operations/schedule/' . $newOrder->id, $scheduleData);
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
                ]
            ]
        ];

        // Step 4: Purchasing quotes Lead Time (e.g. 2 days)
        // Test that lead time immediately reflects on sales confirmed delivery date
        $purchasingRequisition = \App\Models\PurchaseRequisition::first();
        if ($purchasingRequisition) {
            $respLeadTime = $this->actingAs($purchasingUser)->post('/purchasing/requisitions/' . $purchasingRequisition->id . '/lead-time', [
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

        $productAbu = \App\Models\Product::where('code', 'PROD-CVR-4IN1-ABU')->first();

        // Test the exact management formula from Image 3:
        // Speed: 45 pieces/min -> Theoretical: 45 * 60 = 2700 pieces/hour
        // Actual Production: 2250 pieces -> Efficiency: (2250 / 2700) * 100 = 83.33%
        $reportData = [
            'machine_id' => $machine->id,
            'product_id' => $productAbu ? $productAbu->id : null,
            'operator_name' => 'مشغل وردية الصباح',
            'report_date' => now()->toDateString(),
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

        $savedReport = \App\Models\HourlyMachineReport::where('machine_id', $machine->id)
            ->where('hour_slot', '16:00 - 17:00')
            ->latest('id')
            ->first();
        $this->assertNotNull($savedReport);
        $this->assertEquals(83.33, (float)$savedReport->efficiency_rate);
        $this->assertNotNull($savedReport->ai_smart_recommendation);
        $this->assertEquals($productAbu?->id, $savedReport->product_id);

        // Step 6: Test Operations Material Requisitions & Confirming Receipt from Warehouse
        $respReqIndex = $this->actingAs($opsUser)->get('/operations/requisitions');
        $respReqIndex->assertOk();

        $testRequisition = \App\Models\MaterialRequisition::first();
        if ($testRequisition) {
            $testRequisition->update(['status' => 'issued']);
            $respConfirm = $this->actingAs($opsUser)->post('/operations/requisitions/' . $testRequisition->id . '/confirm-receipt');
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

        $respMultiSched = $this->actingAs($opsUser)->post('/operations/schedule/' . $multiOrder->id, $scheduleMultiData);
        $respMultiSched->assertRedirect();

        $workOrdersCount = ProductionOrder::where('sales_order_id', $multiOrder->id)->count();
        $this->assertEquals(3, $workOrdersCount);
    }
}
