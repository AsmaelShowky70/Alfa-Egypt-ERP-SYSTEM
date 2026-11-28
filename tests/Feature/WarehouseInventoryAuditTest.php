<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WarehouseInventoryAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected User $warehouseUser;

    protected User $purchasingUser;

    protected User $operationsUser;

    protected User $ownerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->warehouseUser = User::where('role', 'warehouse')->first() ?? User::where('role', 'owner')->first();
        $this->purchasingUser = User::where('role', 'purchasing')->first() ?? User::where('role', 'owner')->first();
        $this->operationsUser = User::where('role', 'operations')->first() ?? User::where('role', 'owner')->first();
        $this->ownerUser = User::where('role', 'owner')->first();
    }

    /**
     * 1. Purchasing can view warehouse shortages and reorder alerts
     */
    public function test_purchasing_can_view_warehouse_shortages(): void
    {
        $response = $this->actingAs($this->purchasingUser)->get(route('purchasing.warehouseShortages'));
        $response->assertStatus(200);
        $response->assertSee('نواقص المخازن وتنبيهات إعادة الطلب الفوري');
        $response->assertSee('نواقص المواد الخام والمستلزمات');
    }

    /**
     * 2. Operations can view live warehouse stock in read-only mode
     */
    public function test_operations_can_view_warehouse_stock_read_only(): void
    {
        $response = $this->actingAs($this->operationsUser)->get(route('operations.warehouseStock'));
        $response->assertStatus(200);
        $response->assertSee('أرصدة خامات ومستلزمات المخزن المركزي');
        $response->assertSee('وضع العرض اللحظي فقط (Read-Only)');
        $response->assertSee('طلب صرف');
    }

    /**
     * 3. Warehouse Raw Materials View and CRUD
     */
    public function test_warehouse_raw_materials_page_and_creation(): void
    {
        $response = $this->actingAs($this->warehouseUser)->get(route('warehouse.rawMaterials'));
        $response->assertStatus(200);
        $response->assertSee('المواد الخام ومستلزمات الإنتاج (المخزن المركزي)');

        // Store new raw material
        $supplier = Supplier::first();
        $storeResponse = $this->actingAs($this->warehouseUser)->post(route('warehouse.rawMaterials.store'), [
            'name' => 'رول بوليمر شفاف تجريبي للتست',
            'category' => 'رولات سيلوفان وبوليمرات',
            'unit' => 'كجم',
            'current_stock' => 150.5,
            'reorder_level' => 30.0,
            'unit_cost' => 65.0,
            'package_info' => 'وزن الرول 75 كجم / عرض 30 سم',
            'warehouse_location' => 'عنبر الرولات - رصيف 4',
            'supplier_id' => $supplier?->id,
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('raw_materials', [
            'name' => 'رول بوليمر شفاف تجريبي للتست',
            'category' => 'رولات سيلوفان وبوليمرات',
            'unit' => 'كجم',
        ]);

        $material = RawMaterial::where('name', 'رول بوليمر شفاف تجريبي للتست')->first();
        $this->assertNotNull($material);

        // Update raw material
        $updateResponse = $this->actingAs($this->warehouseUser)->put(route('warehouse.rawMaterials.update', $material->id), [
            'name' => 'رول بوليمر شفاف تجريبي للتست - معدل',
            'category' => 'رولات سيلوفان وبوليمرات',
            'unit' => 'كجم',
            'current_stock' => 180.0,
            'reorder_level' => 40.0,
            'unit_cost' => 70.0,
            'package_info' => 'وزن الرول 90 كجم',
            'warehouse_location' => 'عنبر الرولات - رف 5',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('raw_materials', [
            'id' => $material->id,
            'name' => 'رول بوليمر شفاف تجريبي للتست - معدل',
            'current_stock' => 180.0,
        ]);
    }

    /**
     * 4. Fast Stock Adjustment creates StockMovement and updates inventory
     */
    public function test_stock_adjustment_creates_movement_voucher(): void
    {
        $material = RawMaterial::first();
        $this->assertNotNull($material);

        $initialStock = (float) $material->current_stock;
        $newStock = $initialStock + 25.0;

        $response = $this->actingAs($this->warehouseUser)->post(route('warehouse.adjustStock'), [
            'item_type' => 'raw_material',
            'item_id' => $material->id,
            'new_stock' => $newStock,
            'reason' => 'جرد فعلي دوري ومطابقة المخزن',
            'notes' => 'تعديل فحص جردي اختباري',
        ]);

        $response->assertRedirect();
        $material->refresh();
        $this->assertEquals($newStock, (float) $material->current_stock);

        $this->assertDatabaseHas('stock_movements', [
            'type' => 'inventory_adjustment',
            'reference_type' => 'inventory_adjustment',
            'reference_id' => $material->id,
        ]);
    }

    /**
     * 5. Finished Goods CRUD and Direct Sale from Warehouse
     */
    public function test_finished_goods_page_and_direct_sale_with_accounting(): void
    {
        $response = $this->actingAs($this->warehouseUser)->get(route('warehouse.finishedGoods'));
        $response->assertStatus(200);
        $response->assertSee('مخزن المنتج التام (الكوڤير والأصناف الجاهزة للتسليم والبيع)');

        $customer = Customer::first();
        $this->assertNotNull($customer);

        // Create or find a test product with stock
        $product = Product::create([
            'code' => 'PROD-TEST-'.rand(100, 999),
            'name' => 'كوڤير ضيافة فاخر للتست السريع',
            'category' => 'الكوڤير المخصص',
            'unit' => 'قطعة',
            'cost_price' => 1.20,
            'selling_price' => 1.75,
            'min_selling_price' => 1.30,
            'current_stock' => 5000,
            'reorder_level' => 1000,
            'customer_id' => $customer->id,
            'status' => true,
        ]);

        // Dispatch direct sale (Cash)
        $saleResponse = $this->actingAs($this->warehouseUser)->post(route('warehouse.dispatchSale'), [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'quantity' => 500,
            'unit_price' => 1.75,
            'dispatch_date' => Carbon::now()->toDateString(),
            'payment_type' => 'cash',
            'received_by' => 'مندوب العميل مصطفى كامل',
            'notes' => 'صرف فوري نقدي مباشر',
        ]);

        $saleResponse->assertRedirect();
        $product->refresh();
        $this->assertEquals(4500, (int) $product->current_stock);

        // Verify Stock Movement
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'delivery_customer',
            'total_value' => 500 * 1.75,
        ]);

        // Verify Financial Transaction posted
        $this->assertDatabaseHas('financial_transactions', [
            'type' => 'customer_payment',
            'customer_id' => $customer->id,
            'amount' => 500 * 1.75,
            'status' => 'posted',
        ]);
    }

    /**
     * 6. Spare Parts Management (Add, Edit, and Image Upload from Warehouse)
     */
    public function test_spare_parts_page_and_creation_and_update(): void
    {
        $response = $this->actingAs($this->warehouseUser)->get(route('warehouse.spareParts'));
        $response->assertStatus(200);
        $response->assertSee('مخزن قطع الغيار ومستلزمات صيانة الماكينات');
        $response->assertSee('إضافة قطعة غيار جديدة');

        // Store new spare part
        $storeResponse = $this->actingAs($this->warehouseUser)->post(route('warehouse.spareParts.store'), [
            'name' => 'سير سحب كوفير حراري 300 مم تجريبي',
            'unit' => 'متر',
            'current_stock' => 15,
            'reorder_level' => 5,
            'unit_cost' => 450.00,
            'warehouse_location' => 'دولاب قطع الغيار - رف 4',
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('raw_materials', [
            'name' => 'سير سحب كوفير حراري 300 مم تجريبي',
            'category' => 'spare_part',
            'unit' => 'متر',
            'current_stock' => 15,
        ]);

        $sparePart = RawMaterial::where('name', 'سير سحب كوفير حراري 300 مم تجريبي')->first();
        $this->assertNotNull($sparePart);
        $this->assertStringStartsWith('SP-', $sparePart->code);

        // Update spare part
        $updateResponse = $this->actingAs($this->warehouseUser)->put(route('warehouse.spareParts.update', $sparePart->id), [
            'name' => 'سير سحب كوفير حراري 300 مم تجريبي - محدث',
            'unit' => 'متر',
            'current_stock' => 20,
            'reorder_level' => 6,
            'unit_cost' => 500.00,
            'warehouse_location' => 'دولاب قطع الغيار - رف 5',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('raw_materials', [
            'id' => $sparePart->id,
            'name' => 'سير سحب كوفير حراري 300 مم تجريبي - محدث',
            'current_stock' => 20,
            'unit_cost' => 500.00,
        ]);
    }
}
