<?php

namespace Tests\Feature;

use App\Models\DailyInventoryCheck;
use App\Models\Machine;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WarehouseDailyCheckAndOperationsShiftTest extends TestCase
{
    use DatabaseTransactions;

    public function test_warehouse_daily_checks_create_page_renders_with_real_items_and_kpis(): void
    {
        $warehouseManager = User::where('role', 'warehouse')->first() ?? User::factory()->create(['role' => 'warehouse']);

        $response = $this->actingAs($warehouseManager)->get(route('warehouse.dailyChecks.create', ['category' => 'raw_materials']));

        $response->assertStatus(200);
        $response->assertSee('تسجيل محضر الجرد الفعلي للمخزن المركزي');
        $response->assertSee('مطابقة كافة الأرصدة (الفعلي = الدفتري)');
        $response->assertSee('جدول بنود الجرد والمطابقة الحية');
        $response->assertSee('المواد الخام ومستلزمات الإنتاج');
    }

    public function test_warehouse_daily_check_submission_records_surplus_and_deficit_accurately(): void
    {
        $warehouseManager = User::where('role', 'warehouse')->first() ?? User::factory()->create(['role' => 'warehouse']);
        $rawMat1 = RawMaterial::first();
        $rawMat2 = RawMaterial::skip(1)->first() ?? $rawMat1;

        $postData = [
            'check_date' => now()->toDateString(),
            'category' => 'raw_materials',
            'notes' => 'جرد تجريبي بحضور لجنة الجرد المستندي',
            'items' => [
                [
                    'item_id' => $rawMat1->id,
                    'item_type' => 'raw_material',
                    'system_qty' => (float) $rawMat1->current_stock,
                    'actual_qty' => (float) $rawMat1->current_stock + 10, // surplus of 10
                    'unit_cost' => (float) $rawMat1->unit_cost,
                    'reason' => 'زيادة تجارب',
                ],
                [
                    'item_id' => $rawMat2->id,
                    'item_type' => 'raw_material',
                    'system_qty' => (float) $rawMat2->current_stock,
                    'actual_qty' => max(0, (float) $rawMat2->current_stock - 5), // deficit of 5
                    'unit_cost' => (float) $rawMat2->unit_cost,
                    'reason' => 'هادر تشغيل',
                ],
            ],
        ];

        $response = $this->actingAs($warehouseManager)->post(route('warehouse.dailyChecks.store'), $postData);

        $response->assertRedirect();
        $check = DailyInventoryCheck::latest('id')->first();
        $this->assertNotNull($check);
        $this->assertEquals('raw_materials', $check->category);
        $this->assertGreaterThan(0, $check->total_surplus_value);
    }

    public function test_top_management_exclusive_pricing_authority(): void
    {
        $owner = User::where('role', 'owner')->first();
        $gm = User::where('role', 'gm')->first();
        $regularUser = User::where('role', 'sales')->first();

        // Ensure BOM materials remain stable
        RawMaterial::where('code', 'RM-SPOON-ENG')->update(['unit_cost' => 0.9000]);

        $rawMaterial = RawMaterial::where('code', 'RM-PE-FILM-GLOVE')->first() ?? RawMaterial::first();
        $originalCost = (float) $rawMaterial->unit_cost;
        $product = Product::where('code', 'PROD-INV-001')->first() ?? Product::first();
        $originalProdCost = (float) $product->cost_price;
        $originalProdSell = (float) $product->selling_price;
        $originalMinSell = (float) $product->min_selling_price;

        try {
            // Owner can update raw material cost
            $responseOwner = $this->actingAs($owner)->post(route('warehouse.pricing.update'), [
                'type' => 'raw_material',
                'id' => $rawMaterial->id,
                'unit_cost' => 45.50,
            ]);
            $responseOwner->assertRedirect();
            $this->assertEquals(45.50, (float) $rawMaterial->fresh()->unit_cost);

            // GM can update Couvert selling price with 14% markup enforcement
            $responseGM = $this->actingAs($gm)->post(route('warehouse.pricing.update'), [
                'type' => 'finished_good',
                'id' => $product->id,
                'cost_price' => 2.00,
                'selling_price' => 2.50,
            ]);
            $responseGM->assertRedirect();
            $freshProduct = $product->fresh();
            $this->assertEquals(2.00, (float) $freshProduct->cost_price);
            $this->assertEquals(2.50, (float) $freshProduct->selling_price);
            $this->assertEquals(2.28, (float) $freshProduct->min_selling_price); // 2.00 * 1.14 = 2.28

            // Regular warehouse user is forbidden from updating pricing (redirected with error flash and cost is not altered)
            $warehouseUser = User::where('role', 'warehouse')->first() ?? User::factory()->create(['role' => 'warehouse']);
            $responseForbidden = $this->actingAs($warehouseUser)->post(route('warehouse.pricing.update'), [
                'type' => 'raw_material',
                'id' => $rawMaterial->id,
                'unit_cost' => 99.00,
            ]);
            $responseForbidden->assertRedirect();
            $responseForbidden->assertSessionHas('error');
            $this->assertEquals(45.50, (float) $rawMaterial->fresh()->unit_cost);
        } finally {
            $rawMaterial->update(['unit_cost' => $originalCost]);
            $product->update([
                'cost_price' => $originalProdCost,
                'selling_price' => $originalProdSell,
                'min_selling_price' => $originalMinSell,
            ]);
        }
    }

    public function test_operations_hourly_report_supports_shift_selection_and_custom_item(): void
    {
        $operationsManager = User::where('role', 'operations')->first() ?? User::factory()->create(['role' => 'operations']);
        $machine = Machine::first();

        // Submit Shift 2 report with custom product name
        $postData = [
            'machine_id' => $machine->id,
            'operator_name' => 'فني تشغيل وردية 2',
            'report_date' => now()->toDateString(),
            'shift' => '2',
            'hour_slot' => '16:00 - 17:00',
            'product_id' => 'custom',
            'custom_product_name' => 'كوڤير مخصص أبو طارق 4*1 إصدار تجريبي',
            'custom_product_components' => 'معلقة + شوكة + منديل + خلة',
            'units_produced' => 2400,
            'belt_speed_m_min' => 48.0,
            'efficiency_rate' => 83.33,
            'waste_units' => 10,
            'downtime_minutes' => 0,
        ];

        $response = $this->actingAs($operationsManager)->post(route('operations.hourlyReports.store'), $postData);

        $response->assertRedirect();
        $newProd = Product::where('name', 'كوڤير مخصص أبو طارق 4*1 إصدار تجريبي')->first();
        $this->assertNotNull($newProd);
    }
}
