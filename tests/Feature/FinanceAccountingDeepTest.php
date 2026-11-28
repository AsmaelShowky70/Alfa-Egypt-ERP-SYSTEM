<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Factory;
use App\Models\FinancialTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FinanceAccountingDeepTest extends TestCase
{
    use DatabaseTransactions;

    protected User $financeUser;

    protected User $ownerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->financeUser = User::where('role', 'finance')->first() ?? User::where('role', 'owner')->first();
        $this->ownerUser = User::where('role', 'owner')->first();
    }

    /**
     * 1. Test All Finance Pages Return 200 and Load Correct Blade Templates
     */
    public function test_all_finance_pages_render_with_status_200_and_kpis(): void
    {
        // A. Executive Dashboard
        $resp = $this->actingAs($this->financeUser)->get('/finance');
        $resp->assertStatus(200);
        $resp->assertSee('الإدارة المالية والحسابات المركزية');
        $resp->assertSee('رصيد الخزينة والسيولة الحالية');
        $resp->assertSee('إجمالي مديونيات العملاء');
        $resp->assertSee('إجمالي مستحقات الموردين');
        $resp->assertSee('صافي الأرباح التشغيلية التقديرية');

        // B. Customers Ledger
        $respCust = $this->actingAs($this->financeUser)->get('/finance/customers');
        $respCust->assertStatus(200);
        $respCust->assertSee('مستحقات وأرصدة العملاء');
        $respCust->assertSee('سند قبض');
        $respCust->assertSee('كشف حساب');

        // C. Customer Statement (A4 Sheet)
        $cust = Customer::first();
        $this->assertNotNull($cust);
        $respCustStmt = $this->actingAs($this->financeUser)->get('/finance/customers/'.$cust->id.'/statement');
        $respCustStmt->assertStatus(200);
        $respCustStmt->assertSee('كشف حساب عميل تفصيلي');
        $respCustStmt->assertSee('شركة الفا ايجبت');
        $respCustStmt->assertSee('رصيد أول المدة');
        $respCustStmt->assertSee('المحاسب المالي المختص');

        // D. Suppliers Ledger
        $respSup = $this->actingAs($this->financeUser)->get('/finance/suppliers');
        $respSup->assertStatus(200);
        $respSup->assertSee('حسابات والتزامات الموردين والعربونات المقدمة');
        $respSup->assertSee('سند صرف');

        // E. Supplier Statement (A4 Sheet)
        $sup = Supplier::first();
        $this->assertNotNull($sup);
        $respSupStmt = $this->actingAs($this->financeUser)->get('/finance/suppliers/'.$sup->id.'/statement');
        $respSupStmt->assertStatus(200);
        $respSupStmt->assertSee('كشف حساب مورد تفصيلي');
        $respSupStmt->assertSee('شركة الفا ايجبت');

        // F. Treasury Cash Flow
        $respTr = $this->actingAs($this->financeUser)->get('/finance/treasury');
        $respTr->assertStatus(200);
        $respTr->assertSee('حركة الخزينة والسيولة النقدية');
        $respTr->assertSee('الرصيد الفعلي الحالي بالخزينة');

        // G. General & Operating Expenses (18 categories)
        $respExp = $this->actingAs($this->financeUser)->get('/finance/expenses');
        $respExp->assertStatus(200);
        $respExp->assertSee('المصروفات العمومية والإدارية والتشغيلية');
        $respExp->assertSee('18 بنداً محاسبياً');

        // H. General Journal (Double-Entry Ledger)
        $respJrn = $this->actingAs($this->financeUser)->get('/finance/journal');
        $respJrn->assertStatus(200);
        $respJrn->assertSee('دفتر اليومية العامة والقيود المزدوجة');
        $respJrn->assertSee('ميزان توازن قيود اليومية العامة');
        $respJrn->assertSee('موزون 100%');

        // I. October Financial Analysis (Excel Replica)
        $respOct = $this->actingAs($this->financeUser)->get('/finance/analysis/october');
        $respOct->assertStatus(200);
        $respOct->assertSee('التقرير والتحليل المالي لشهري سبتمبر وأكتوبر 2026');
        $respOct->assertSee('المبيعات والعملاء والضرائب');
        $respOct->assertSee('م غمومية');
        $respOct->assertSee('الخزنة وتدفقات السيولة');
    }

    /**
     * 2. Test Excel Opening Balances Integrity (Customers & Suppliers)
     */
    public function test_excel_imported_balances_accuracy(): void
    {
        // Customer: كشري أبو طارق (Opening Balance 713,000 EGP)
        $abouTarek = Customer::where('name', 'like', '%أبو طارق%')->first();
        $this->assertNotNull($abouTarek, 'Customer كشري أبو طارق must exist');
        $this->assertEquals(713000.00, (float) $abouTarek->opening_balance);

        // Customer with Advance (محمود الحريري -114,048 EGP)
        $hariri = Customer::where('name', 'like', '%الحريري%')->first();
        $this->assertNotNull($hariri, 'Customer محمود الحريري must exist');
        $this->assertEquals(-114048.00, (float) $hariri->opening_balance);

        // Supplier: ريمون أكياس الروبيكي (Opening Balance 280,241 EGP)
        $remon = Supplier::where('name', 'like', '%ريمون%')->first();
        $this->assertNotNull($remon, 'Supplier ريمون must exist');
        $this->assertEquals(280241.00, (float) $remon->opening_balance);

        // Supplier with Advance Deposit: حمزة السوري (-110,000 EGP)
        $hamza = Supplier::where('name', 'like', '%حمزة السوري%')->first();
        $this->assertNotNull($hamza, 'Supplier حمزة السوري must exist');
        $this->assertEquals(-110000.00, (float) $hamza->opening_balance);
        $this->assertLessThan(0, $hamza->current_balance);
    }

    /**
     * 3. Test Customer Receipt Voucher (سند قبض) and Balance Impact
     */
    public function test_customer_receipt_voucher_recording_and_treasury_inflow(): void
    {
        $cust = Customer::where('opening_balance', '>', 50000)->first();
        $this->assertNotNull($cust);

        $initialBalance = $cust->current_balance;
        $initialCollected = $cust->total_collected;

        $inflowAmount = 15000.00;

        $payload = [
            'amount' => $inflowAmount,
            'payment_method' => 'نقدي',
            'transaction_date' => date('Y-m-d'),
            'description' => 'سند قبض تجريبي فحص الحسابات',
        ];

        $response = $this->actingAs($this->financeUser)
            ->post('/finance/customers/'.$cust->id.'/payment', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cust->refresh();
        $this->assertEquals($initialCollected + $inflowAmount, (float) $cust->total_collected);
        $this->assertEquals($initialBalance - $inflowAmount, (float) $cust->current_balance);

        // Verify Financial Transaction Entry
        $txn = FinancialTransaction::where('customer_id', $cust->id)
            ->where('description', 'سند قبض تجريبي فحص الحسابات')
            ->first();

        $this->assertNotNull($txn);
        $this->assertEquals('customer_payment', $txn->type);
        $this->assertEquals('حـ/ الخزينة الرئيسية', $txn->debit_account);
        $this->assertStringContainsString($cust->name, $txn->credit_account);
        $this->assertEquals($inflowAmount, (float) $txn->amount);

        // Clean up
        $txn->delete();
    }

    /**
     * 4. Test Supplier Payment Voucher (سند صرف لمورد) and Balance Impact
     */
    public function test_supplier_payment_voucher_recording_and_treasury_outflow(): void
    {
        $sup = Supplier::where('opening_balance', '>', 10000)->first();
        $this->assertNotNull($sup);

        $initialBalance = $sup->current_balance;
        $initialPaid = $sup->total_paid;

        $outflowAmount = 8500.00;

        $payload = [
            'amount' => $outflowAmount,
            'payment_method' => 'شيك',
            'check_number' => 'CHK-TEST-999',
            'due_date' => date('Y-m-d', strtotime('+15 days')),
            'transaction_date' => date('Y-m-d'),
            'description' => 'سداد بشيك للمورد فحص الحسابات',
        ];

        $response = $this->actingAs($this->financeUser)
            ->post('/finance/suppliers/'.$sup->id.'/payment', $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $sup->refresh();
        $this->assertEquals($initialPaid + $outflowAmount, (float) $sup->total_paid);
        $this->assertEquals($initialBalance - $outflowAmount, (float) $sup->current_balance);

        // Verify Financial Transaction Entry
        $txn = FinancialTransaction::where('supplier_id', $sup->id)
            ->where('description', 'سداد بشيك للمورد فحص الحسابات')
            ->first();

        $this->assertNotNull($txn);
        $this->assertEquals('supplier_payment', $txn->type);
        $this->assertStringContainsString($sup->name, $txn->debit_account);
        $this->assertEquals('حـ/ أوراق دفع (شيكات صادرة للموردين)', $txn->credit_account);
        $this->assertEquals('CHK-TEST-999', $txn->check_number);
        $this->assertEquals($outflowAmount, (float) $txn->amount);

        // Clean up
        $txn->delete();
    }

    /**
     * 5. Test Treasury Movement Voucher (سند حركة خزينة مباشر)
     */
    public function test_treasury_movement_vouchers(): void
    {
        // A. Inflow Voucher
        $inflowData = [
            'direction' => 'inflow',
            'category' => 'تمويل مؤقت من الشركاء',
            'amount' => 50000.00,
            'payment_method' => 'نقدي',
            'transaction_date' => date('Y-m-d'),
            'debit_account' => 'حـ/ الخزينة الرئيسية',
            'credit_account' => 'حـ/ جاري الشركاء',
            'description' => 'توريد نقدية لدعم السيولة',
        ];

        $respIn = $this->actingAs($this->financeUser)->post('/finance/treasury/voucher', $inflowData);
        $respIn->assertRedirect();
        $respIn->assertSessionHas('success');

        $inflowTxn = FinancialTransaction::where('description', 'توريد نقدية لدعم السيولة')->first();
        $this->assertNotNull($inflowTxn);
        $this->assertEquals(50000.00, (float) $inflowTxn->amount);

        // B. Outflow Voucher
        $outflowData = [
            'direction' => 'outflow',
            'category' => 'مصروفات تشغيل عاجلة',
            'amount' => 7500.00,
            'payment_method' => 'نقدي',
            'transaction_date' => date('Y-m-d'),
            'debit_account' => 'حـ/ مصروفات تشغيل',
            'credit_account' => 'حـ/ الخزينة الرئيسية',
            'description' => 'صرف نقدية عاجلة',
        ];

        $respOut = $this->actingAs($this->financeUser)->post('/finance/treasury/voucher', $outflowData);
        $respOut->assertRedirect();
        $respOut->assertSessionHas('success');

        $outflowTxn = FinancialTransaction::where('description', 'صرف نقدية عاجلة')->first();
        $this->assertNotNull($outflowTxn);
        $this->assertEquals(7500.00, (float) $outflowTxn->amount);

        // Clean up
        $inflowTxn->delete();
        $outflowTxn->delete();
    }

    /**
     * 6. Test Expense Recording under the 18 Real Categories & Factory Allocation
     */
    public function test_general_expense_recording_with_factory_cost_center(): void
    {
        $factory = Factory::first();
        $this->assertNotNull($factory);

        $expenseData = [
            'category' => 'صيانة ونظافة مصانع',
            'amount' => 4500.00,
            'transaction_date' => date('Y-m-d'),
            'payment_method' => 'نقدي',
            'factory_id' => $factory->id,
            'description' => 'صيانة طارئة لسير ماكينة التغليف',
        ];

        $resp = $this->actingAs($this->financeUser)->post('/finance/expenses', $expenseData);
        $resp->assertRedirect();
        $resp->assertSessionHas('success');

        $expTxn = FinancialTransaction::where('description', 'صيانة طارئة لسير ماكينة التغليف')->first();
        $this->assertNotNull($expTxn);
        $this->assertEquals('expense', $expTxn->type);
        $this->assertEquals('صيانة ونظافة مصانع', $expTxn->category);
        $this->assertEquals(4500.00, (float) $expTxn->amount);
        $this->assertEquals($factory->id, $expTxn->factory_id);
        $this->assertEquals('حـ/ الخزينة الرئيسية', $expTxn->credit_account);

        // Clean up
        $expTxn->delete();
    }

    /**
     * 7. Test Manual Journal Entry with Double Entry Balancing
     */
    public function test_manual_journal_entry_balancing(): void
    {
        $jrnData = [
            'debit_account' => 'حـ/ أصول ثابتة - قوالب حقن جديدة',
            'credit_account' => 'حـ/ أوراق دفع - شيكات مؤجلة',
            'amount' => 75000.00,
            'transaction_date' => date('Y-m-d'),
            'description' => 'إثبات شراء اسطمبة حقن معالق فاخرة',
        ];

        $resp = $this->actingAs($this->financeUser)->post('/finance/journal', $jrnData);
        $resp->assertRedirect();
        $resp->assertSessionHas('success');

        $jrnTxn = FinancialTransaction::where('description', 'إثبات شراء اسطمبة حقن معالق فاخرة')->first();
        $this->assertNotNull($jrnTxn);
        $this->assertEquals('manual_journal', $jrnTxn->type);
        $this->assertEquals('حـ/ أصول ثابتة - قوالب حقن جديدة', $jrnTxn->debit_account);
        $this->assertEquals('حـ/ أوراق دفع - شيكات مؤجلة', $jrnTxn->credit_account);
        $this->assertEquals(75000.00, (float) $jrnTxn->amount);

        // Clean up
        $jrnTxn->delete();
    }

    /**
     * 8. Test Cross-Layer Automation: Sales Order -> Auto Sales Invoice
     */
    public function test_sales_order_creation_triggers_automatic_sales_invoice(): void
    {
        $salesUser = User::where('role', 'sales')->first() ?? $this->ownerUser;
        $cust = Customer::first();
        $product = Product::first();

        $this->assertNotNull($cust);
        $this->assertNotNull($product);

        $qty = 2000;
        $unitPrice = 2.50;
        $subtotal = $qty * $unitPrice; // 5000
        $tax = $subtotal * 0.14;       // 700
        $total = $subtotal + $tax;     // 5700

        $orderData = [
            'customer_id' => $cust->id,
            'order_date' => date('Y-m-d'),
            'target_delivery_date' => date('Y-m-d', strtotime('+7 days')),
            'client_notes' => 'طلب مبيعات اختبار ربط الحسابات التلقائي',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'notes' => 'تسليم عاجل',
                ],
            ],
        ];

        $resp = $this->actingAs($salesUser)->post('/sales/orders', $orderData);
        $resp->assertRedirect();

        $salesOrder = SalesOrder::where('client_notes', 'طلب مبيعات اختبار ربط الحسابات التلقائي')->first();
        $this->assertNotNull($salesOrder);

        // Verify Automatic Sales Invoice Financial Transaction was posted!
        $invoice = FinancialTransaction::where('sales_order_id', $salesOrder->id)->first();
        $this->assertNotNull($invoice, 'Automatic sales_invoice must be generated in finance ledger');
        $this->assertEquals('sales_invoice', $invoice->type);
        $this->assertEquals($cust->id, $invoice->customer_id);
        $this->assertEquals($total, (float) $invoice->amount);
        $this->assertEquals($subtotal, (float) $invoice->net_amount);
        $this->assertEquals(round($tax, 2), round((float) $invoice->tax_vat_amount, 2));
        $this->assertEquals(round($subtotal * 0.01, 2), (float) $invoice->tax_withholding_amount);
        $this->assertStringContainsString($cust->name, $invoice->debit_account);
        $this->assertEquals('حـ/ إيرادات المبيعات', $invoice->credit_account);

        // Clean up
        $invoice->delete();
        $salesOrder->items()->delete();
        $salesOrder->delete();
    }

    /**
     * 9. Test Cross-Layer Automation: Purchasing PO -> Auto Supplier Bill or Internal Markup
     */
    public function test_purchase_order_creation_triggers_automatic_financial_posting(): void
    {
        $purchasingUser = User::where('role', 'purchasing')->first() ?? $this->ownerUser;
        $supplier = Supplier::first();
        $this->assertNotNull($supplier);

        $poData = [
            'is_internal_factory' => 0,
            'supplier_id' => $supplier->id,
            'order_date' => date('Y-m-d'),
            'expected_delivery_date' => date('Y-m-d', strtotime('+5 days')),
            'destination_warehouse' => 'مخزن رولات وخامات التغليف',
            'notes' => 'أمر شراء تجريبي لاختبار ربط الحسابات',
            'items' => [
                [
                    'raw_material_id' => null,
                    'item_name' => 'كرتون مضلع 5 طبقات فحص حسابات',
                    'quantity' => 500,
                    'unit_price' => 12.00,
                ],
            ],
        ];

        $resp = $this->actingAs($purchasingUser)->post('/purchasing/orders', $poData);
        $resp->assertRedirect();

        $po = PurchaseOrder::where('notes', 'أمر شراء تجريبي لاختبار ربط الحسابات')->first();
        $this->assertNotNull($po);

        // Verify Automatic Supplier Bill was created!
        $bill = FinancialTransaction::where('purchase_order_id', $po->id)->first();
        $this->assertNotNull($bill, 'Automatic supplier_bill must be generated in finance ledger');
        $this->assertEquals('supplier_bill', $bill->type);
        $this->assertEquals($supplier->id, $bill->supplier_id);
        $this->assertEquals($po->total_amount, (float) $bill->amount);
        $this->assertEquals('حـ/ مشتريات خامات ومستلزمات', $bill->debit_account);
        $this->assertStringContainsString($supplier->name, $bill->credit_account);

        // Clean up
        $bill->delete();
        $po->items()->delete();
        $po->delete();
    }

    /**
     * 10. Test Role Authorization and Permissions
     */
    public function test_role_authorization_for_finance_department(): void
    {
        // 1. Finance User has access
        $respFin = $this->actingAs($this->financeUser)->get('/finance');
        $respFin->assertStatus(200);

        // 2. Owner User has access
        $respOwner = $this->actingAs($this->ownerUser)->get('/finance');
        $respOwner->assertStatus(200);

        // 3. Maintenance or Warehouse user is redirected with an error message
        $maintUser = User::where('role', 'maintenance')->first();
        if ($maintUser) {
            $respMaint = $this->actingAs($maintUser)->get('/finance');
            $respMaint->assertRedirect('/dashboard');
            $respMaint->assertSessionHas('error');
        }
    }
}
