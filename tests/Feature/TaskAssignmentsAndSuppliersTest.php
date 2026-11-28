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

    protected User $fouad;

    protected User $gergesGhatas;

    protected User $purchasing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::where('role', 'gm')->first() ?? User::factory()->create(['role' => 'gm']);
        $this->fouad = User::where('email', 'fouad@alphaegypt.com')->first() ?? User::factory()->create([
            'name' => 'م. فؤاد عبد الملك',
            'email' => 'fouad@alphaegypt.com',
            'role' => 'maintenance',
        ]);
        $this->gergesGhatas = User::where('email', 'gerges.ghatas@alphaegypt.com')->first() ?? User::factory()->create([
            'name' => 'جرجس غطاس',
            'email' => 'gerges.ghatas@alphaegypt.com',
            'role' => 'operations',
            'avatar' => 'uploads/users/gerges_ghatas.jpg',
        ]);
        $this->purchasing = User::where('role', 'purchasing')->first() ?? User::factory()->create(['role' => 'purchasing']);
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

    public function test_glove_machines_overtime_assignments_for_eng_fouad(): void
    {
        $cutMachine = Machine::where('code', 'MCH-GLV-CUT01')->first();
        $pckMachine = Machine::where('code', 'MCH-GLV-PCK01')->first();

        $this->assertNotNull($cutMachine);
        $this->assertNotNull($pckMachine);

        // Check task for cutting machine
        $cutTask = TaskAssignment::where('assigned_to_user_id', $this->fouad->id)
            ->where('machine_id', $cutMachine->id)
            ->where('task_type', 'overtime')
            ->first();

        $this->assertNotNull($cutTask, 'Glove cutting machine overtime assignment must exist for Eng. Fouad.');
        $this->assertEquals('in_progress', $cutTask->status);
        $this->assertTrue($cutTask->followUps()->exists(), 'Cut task must have follow-up reports.');

        // Check task for packaging machine
        $pckTask = TaskAssignment::where('assigned_to_user_id', $this->fouad->id)
            ->where('machine_id', $pckMachine->id)
            ->where('task_type', 'overtime')
            ->first();

        $this->assertNotNull($pckTask, 'Glove packaging machine overtime assignment must exist for Eng. Fouad.');
        $this->assertEquals('in_progress', $pckTask->status);
        $this->assertTrue($pckTask->followUps()->exists(), 'Pck task must have follow-up reports.');
    }

    public function test_wipes_machine_supervision_assignment_for_gerges_ghatas(): void
    {
        $wipesMachine = Machine::where('code', 'MCH-WIPES-01')->first();
        $this->assertNotNull($wipesMachine);

        $this->assertNotEmpty($this->gergesGhatas->avatar);
        $this->assertFileExists(public_path($this->gergesGhatas->avatar));

        $wipesTask = TaskAssignment::where('assigned_to_user_id', $this->gergesGhatas->id)
            ->where('machine_id', $wipesMachine->id)
            ->first();

        $this->assertNotNull($wipesTask, 'Wipes machine task must exist for Gerges Ghatas.');
        $this->assertEquals('supervision', $wipesTask->task_type);
        $this->assertEquals('in_progress', $wipesTask->status);
        $this->assertTrue($wipesTask->followUps()->exists());
    }

    public function test_gm_can_create_new_task_and_employee_can_submit_follow_up(): void
    {
        $taskData = [
            'assigned_to_user_id' => $this->fouad->id,
            'machine_id' => Machine::first()->id,
            'title' => 'مهمة فحص دوري طارئة للسيور',
            'task_type' => 'maintenance',
            'priority' => 'urgent',
            'due_date' => now()->addDays(5)->toDateString(),
            'description' => 'مطلوب فحص شامل للسيور والحساسات لتلافي التباطؤ.',
            'notes' => 'تكليف صادر من الإدارة العليا.',
        ];

        $response = $this->actingAs($this->gm)->post(route('tasks.store'), $taskData);
        $response->assertRedirect();

        $newTask = TaskAssignment::where('title', 'مهمة فحص دوري طارئة للسيور')->first();
        $this->assertNotNull($newTask);

        // Eng. Fouad submits follow-up report
        $followUpData = [
            'report_date' => now()->toDateString(),
            'notes' => 'تم استبدال الحزام وضبط الشد الميكانيكي، وعملت الماكينة بكامل طاقتها.',
            'downtime_minutes' => 20,
            'spare_parts_used' => 'سير ناقل 250 مم',
            'machine_status' => 'running',
            'progress_percentage' => 100,
        ];

        $fuResponse = $this->actingAs($this->fouad)->post(
            route('tasks.follow-ups.store', $newTask),
            $followUpData
        );

        $fuResponse->assertRedirect();
        $this->assertDatabaseHas('task_follow_ups', [
            'task_assignment_id' => $newTask->id,
            'user_id' => $this->fouad->id,
            'spare_parts_used' => 'سير ناقل 250 مم',
            'machine_status' => 'running',
        ]);
    }

    public function test_tasks_index_view_renders_correctly(): void
    {
        $response = $this->actingAs($this->gm)->get(route('tasks.index'));
        $response->assertStatus(200);
        $response->assertSee('سجل المهام والتكليفات الإضافية ومتابعة الماكينات');
        $response->assertSee('م. فؤاد عبد الملك');
        $response->assertSee('جرجس غطاس');
    }

    public function test_fast_login_displays_new_staff(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('م. فؤاد عبد الملك');
        $response->assertSee('جرجس غطاس');
    }
}
