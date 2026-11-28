<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\TaskAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TaskAssignmentsAndSuppliersTest extends TestCase
{
    use DatabaseTransactions;

    protected User $gm;

    protected User $fadi;

    protected User $gergesGhatas;

    protected User $purchasing;

    protected User $sales;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::where('role', 'gm')->first() ?? User::factory()->create(['role' => 'gm']);
        $this->fadi = User::where('email', 'fadi@alphaegypt.com')->first();
        $this->gergesGhatas = User::where('email', 'gerges.ghatas@alphaegypt.com')->first();
        $this->purchasing = User::where('role', 'purchasing')->first() ?? User::factory()->create(['role' => 'purchasing']);
        $this->sales = User::where('role', 'sales')->first() ?? User::factory()->create(['role' => 'sales']);
    }

    public function test_suppliers_page_displays_notes_and_edit_works(): void
    {
        $supplier = Supplier::where('code', 'SUP-007')->first() ?? Supplier::first();
        $this->assertNotNull($supplier);

        // Check index view
        $response = $this->actingAs($this->purchasing)->get(route('purchasing.suppliers'));
        $response->assertStatus(200);
        $response->assertSee($supplier->name);

        // Update supplier
        $updateData = [
            'name' => $supplier->name,
            'category' => $supplier->category,
            'contact_person' => 'مسؤول تواصل محدث للتجربة',
            'phone' => '01099999999',
            'email' => 'updated_supplier@test.com',
            'address' => 'المنطقة الصناعية - العاشر من رمضان',
            'average_lead_time_days' => 4,
            'notes' => "سعر الألف قطعة 260 ج بعد التحديث\nشروط الدفع عند الاستلام",
        ];

        $updateResponse = $this->actingAs($this->purchasing)->put(
            route('purchasing.suppliers.update', $supplier),
            $updateData
        );

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'contact_person' => 'مسؤول تواصل محدث للتجربة',
            'phone' => '01099999999',
            'average_lead_time_days' => 4,
        ]);
    }

    public function test_inventory_has_no_imageless_or_dummy_records(): void
    {
        $emptyRM = RawMaterial::whereNull('image_url')->orWhere('image_url', '')->count();
        $this->assertEquals(0, $emptyRM, 'No raw material should be without an image.');

        $emptyProds = Product::whereNull('image_url')->orWhere('image_url', '')->count();
        $this->assertEquals(0, $emptyProds, 'No product should be without an image.');

        // Verify that raw materials with local uploads exist on disk
        foreach (RawMaterial::where('image_url', 'like', '%/uploads/%')->get() as $rm) {
            $path = parse_url($rm->image_url, PHP_URL_PATH);
            $this->assertFileExists(public_path($path), "Raw material {$rm->code} image must exist on disk.");
        }

        // Verify that products with local uploads exist on disk
        foreach (Product::where('image_url', 'like', '%/uploads/%')->get() as $prod) {
            $path = parse_url($prod->image_url, PHP_URL_PATH);
            $this->assertFileExists(public_path($path), "Product {$prod->code} image must exist on disk.");
        }
    }

    public function test_glove_machines_overtime_assignments_for_fadi_ashraf(): void
    {
        $cutMachine = Machine::where('code', 'MCH-GLV-CUT01')->first();
        $pckMachine = Machine::where('code', 'MCH-GLV-PCK01')->first();

        $this->assertNotNull($cutMachine);
        $this->assertNotNull($pckMachine);
        $this->assertNotNull($this->fadi);

        // Check task for cutting machine
        $cutTask = TaskAssignment::where('assigned_to_user_id', $this->fadi->id)
            ->where('machine_id', $cutMachine->id)
            ->where('task_type', 'overtime')
            ->first();

        $this->assertNotNull($cutTask, 'Glove cutting machine overtime assignment must exist for Fadi Ashraf.');
        $this->assertEquals('in_progress', $cutTask->status);
        $this->assertTrue($cutTask->followUps()->exists(), 'Cut task must have follow-up reports.');

        // Check task for packaging machine
        $pckTask = TaskAssignment::where('assigned_to_user_id', $this->fadi->id)
            ->where('machine_id', $pckMachine->id)
            ->where('task_type', 'overtime')
            ->first();

        $this->assertNotNull($pckTask, 'Glove packaging machine overtime assignment must exist for Fadi Ashraf.');
        $this->assertEquals('in_progress', $pckTask->status);
        $this->assertTrue($pckTask->followUps()->exists(), 'Pck task must have follow-up reports.');
    }

    public function test_wipes_machine_supervisor_dedicated_dashboard_and_task(): void
    {
        $wipesMachine = Machine::where('code', 'MCH-WIPES-01')->first();
        $this->assertNotNull($wipesMachine);
        $this->assertNotNull($this->gergesGhatas);

        $this->assertTrue($this->gergesGhatas->isWipesSupervisor());
        $this->assertNotEmpty($this->gergesGhatas->avatar);
        $this->assertFileExists(public_path($this->gergesGhatas->avatar));

        // Test Gerges Ghatas Dashboard: dedicated Wipes dashboard
        $dashResponse = $this->actingAs($this->gergesGhatas)->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('لوحة إشراف وتشغيل خط ماكينة الوايبس');
        $dashResponse->assertSee('جرجس غطاس');
        $dashResponse->assertSee('MCH-WIPES-01');

        // Check Gerges Ghatas Task on wipes machine
        $wipesTask = TaskAssignment::where('assigned_to_user_id', $this->gergesGhatas->id)
            ->where('machine_id', $wipesMachine->id)
            ->first();

        $this->assertNotNull($wipesTask, 'Wipes machine task must exist for Gerges Ghatas.');
        $this->assertEquals('supervision', $wipesTask->task_type);
        $this->assertEquals('in_progress', $wipesTask->status);
        $this->assertTrue($wipesTask->followUps()->exists());
    }

    public function test_tasks_restricted_to_top_management_fadi_and_gerges_only(): void
    {
        // GM can access
        $this->actingAs($this->gm)->get(route('tasks.index'))->assertStatus(200);

        // Fadi Ashraf can access
        $this->actingAs($this->fadi)->get(route('tasks.index'))->assertStatus(200);

        // Gerges Ghatas can access
        $this->actingAs($this->gergesGhatas)->get(route('tasks.index'))->assertStatus(200);

        // Sales user is NOT allowed (403)
        $this->actingAs($this->sales)->get(route('tasks.index'))->assertStatus(403);

        // Purchasing user is NOT allowed (403)
        $this->actingAs($this->purchasing)->get(route('tasks.index'))->assertStatus(403);

        // Only Top Management can create new tasks
        $taskData = [
            'assigned_to_user_id' => $this->fadi->id,
            'machine_id' => Machine::first()->id,
            'title' => 'مهمة فحص دوري طارئة للسيور',
            'task_type' => 'maintenance',
            'priority' => 'urgent',
            'due_date' => now()->addDays(5)->toDateString(),
            'description' => 'مطلوب فحص شامل للسيور والحساسات لتلافي التباطؤ.',
            'notes' => 'تكليف صادر من الإدارة العليا.',
        ];

        // Fadi cannot create a new task assignment (403)
        $this->actingAs($this->fadi)->post(route('tasks.store'), $taskData)->assertStatus(403);

        // GM CAN create a new task assignment
        $gmResponse = $this->actingAs($this->gm)->post(route('tasks.store'), $taskData);
        $gmResponse->assertRedirect();
        $this->assertDatabaseHas('task_assignments', ['title' => 'مهمة فحص دوري طارئة للسيور']);
    }

    public function test_fast_login_displays_fadi_and_gerges_and_no_fouad(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('فادي أشرف');
        $response->assertSee('جرجس غطاس');
        $response->assertDontSee('فؤاد عبد الملك');
    }

    public function test_delegated_production_monitoring_in_operations_and_maintenance_dashboards(): void
    {
        $opsManager = User::where('role', 'operations')->where('email', '!=', 'gerges.ghatas@alphaegypt.com')->first();
        $this->assertNotNull($opsManager);

        // 1. Operations Manager Dashboard shows delegated production lines to relieve burden
        $opsDash = $this->actingAs($opsManager)->get(route('dashboard'));
        $opsDash->assertStatus(200);
        $opsDash->assertSee('متابعة الخطوط المفوضة (لتخفيف العبء عن مدير التشغيل)');
        $opsDash->assertSee('فادي أشرف');
        $opsDash->assertSee('جرجس غطاس');

        // 2. Fadi Ashraf Maintenance Dashboard shows Glove production monitoring overtime
        $fadiDash = $this->actingAs($this->fadi)->get(route('dashboard'));
        $fadiDash->assertStatus(200);
        $fadiDash->assertSee('متابعة تشغيل وإنتاجية ماكينات الجوانتي');
        $fadiDash->assertSee('MCH-GLV-CUT01');
        $fadiDash->assertSee('MCH-GLV-PCK01');
    }
}
