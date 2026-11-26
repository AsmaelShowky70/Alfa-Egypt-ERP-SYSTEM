<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\FinancialTransaction;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class FinancialDataFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed / Update Real Customers with Excel Opening Balances
        $customers = [
            ['name' => 'كشري أبو طارق', 'code' => 'CUST-001', 'opening_balance' => 713000.00, 'phone' => '01011112222', 'payment_terms' => 'آجل 30 يوم'],
            ['name' => 'مطاعم شيخ البلد', 'code' => 'CUST-010', 'opening_balance' => 173166.00, 'phone' => '01022223333', 'payment_terms' => 'آجل 15 يوم'],
            ['name' => 'دينا فارمز (دينا)', 'code' => 'CUST-011', 'opening_balance' => 105090.00, 'phone' => '01033334444', 'payment_terms' => 'آجل 30 يوم'],
            ['name' => 'جيت بوقتك', 'code' => 'CUST-012', 'opening_balance' => 73445.00, 'phone' => '01044445555', 'payment_terms' => 'آجل 15 يوم'],
            ['name' => 'مطاعم مضغوط', 'code' => 'CUST-013', 'opening_balance' => 180913.00, 'phone' => '01055556666', 'payment_terms' => 'آجل 30 يوم'],
            ['name' => 'حلواني إيتوال', 'code' => 'CUST-014', 'opening_balance' => 148233.00, 'phone' => '01066667777', 'payment_terms' => 'آجل 30 يوم'],
            ['name' => 'كبابجي بهيج', 'code' => 'CUST-015', 'opening_balance' => 44000.00, 'phone' => '01077778888', 'payment_terms' => 'آجل 15 يوم'],
            ['name' => 'نوت كافيه (Knot Cafe)', 'code' => 'CUST-016', 'opening_balance' => 48783.00, 'phone' => '01088889999', 'payment_terms' => 'نقدي عند الاستلام'],
            ['name' => 'مطعم الصعيدي', 'code' => 'CUST-017', 'opening_balance' => 41325.00, 'phone' => '01099990000', 'payment_terms' => 'آجل 15 يوم'],
            ['name' => 'أحمد صلاح', 'code' => 'CUST-018', 'opening_balance' => 43218.00, 'phone' => '01112223334', 'payment_terms' => 'آجل 15 يوم'],
            ['name' => 'شركة روين', 'code' => 'CUST-019', 'opening_balance' => 696000.00, 'phone' => '01123334445', 'payment_terms' => 'آجل 45 يوم'],
            ['name' => 'حلب الشهباء', 'code' => 'CUST-020', 'opening_balance' => 0.00, 'phone' => '01134445556', 'payment_terms' => 'نقدي'],
            ['name' => 'عوض الحلال', 'code' => 'CUST-021', 'opening_balance' => 0.00, 'phone' => '01145556667', 'payment_terms' => 'نقدي'],
            ['name' => 'محمود الحريري', 'code' => 'CUST-022', 'opening_balance' => -114048.00, 'phone' => '01156667778', 'payment_terms' => 'دفعات مقدمة / عربون'],
            ['name' => 'محمود إسماعيل', 'code' => 'CUST-023', 'opening_balance' => -50000.00, 'phone' => '01167778889', 'payment_terms' => 'دفعات مقدمة / عربون'],
            ['name' => 'قرية هشام', 'code' => 'CUST-024', 'opening_balance' => -12200.00, 'phone' => '01178889990', 'payment_terms' => 'دفعات مقدمة'],
            ['name' => 'مطعم توبروز', 'code' => 'CUST-025', 'opening_balance' => -40000.00, 'phone' => '01189990001', 'payment_terms' => 'دفعات مقدمة'],
            ['name' => 'مطعم لاكي داكي', 'code' => 'CUST-026', 'opening_balance' => -30000.00, 'phone' => '01190001112', 'payment_terms' => 'دفعات مقدمة'],
            ['name' => 'توكيلات ليبيا', 'code' => 'CUST-027', 'opening_balance' => -40000.00, 'phone' => '01201112223', 'payment_terms' => 'تصدير / دفعات مقدمة'],
        ];

        foreach ($customers as $cData) {
            Customer::updateOrCreate(
                ['name' => $cData['name']],
                $cData
            );
        }

        // 2. Seed / Update Real Suppliers with Excel Opening Balances
        $suppliers = [
            ['name' => 'مصنعيات أبو فهد', 'code' => 'SUP-010', 'category' => 'مصنعيات وتعبئة', 'opening_balance' => 39480.00],
            ['name' => 'تغليف علاء مؤسسة الزكاة', 'code' => 'SUP-011', 'category' => 'تغليف وسلوفان', 'opening_balance' => 25300.00],
            ['name' => 'هيرو كير', 'code' => 'SUP-012', 'category' => 'محاليل ومطهرات', 'opening_balance' => 50000.00],
            ['name' => 'وايبس أحمد بوكيه', 'code' => 'SUP-013', 'category' => 'رولات وايبس معطرة', 'opening_balance' => 40000.00],
            ['name' => 'ديفيد ستار باك (نيو ستار)', 'code' => 'SUP-014', 'category' => 'تغليف وطباعة', 'opening_balance' => 7030.00],
            ['name' => 'حمزة السوري', 'code' => 'SUP-015', 'category' => 'خامات وسلوفان', 'opening_balance' => -110000.00], // عربون مدفوع
            ['name' => 'ريمون أكياس الروبيكي', 'code' => 'SUP-016', 'category' => 'أكياس وبوليمرات', 'opening_balance' => 280241.00],
            ['name' => 'شركة برتاية', 'code' => 'SUP-017', 'category' => 'مستلزمات خشبية وبلاستيكية', 'opening_balance' => 100000.00],
            ['name' => 'المنير باك', 'code' => 'SUP-018', 'category' => 'كرتون وتغليف', 'opening_balance' => 9970.00],
            ['name' => 'برنت باك عادل', 'code' => 'SUP-019', 'category' => 'طباعة وسلوفان', 'opening_balance' => 170000.00],
            ['name' => 'سوفت باك طلعت', 'code' => 'SUP-020', 'category' => 'مناديل وتغليف ورقي', 'opening_balance' => 9560.00],
            ['name' => 'محمد جمال العاشر', 'code' => 'SUP-021', 'category' => 'حقن وبلاستيك', 'opening_balance' => -11071.00], // عربون
            ['name' => 'حقن العمدة (محمد العمدة)', 'code' => 'SUP-022', 'category' => 'معالق وشوك حقن', 'opening_balance' => 58000.00],
            ['name' => 'حقن الأصدقاء', 'code' => 'SUP-023', 'category' => 'حقن بلاستيك', 'opening_balance' => 39965.00],
            ['name' => 'السمادوني', 'code' => 'SUP-024', 'category' => 'خامات عامة', 'opening_balance' => 0.00],
            ['name' => 'سيرا باك أكواب', 'code' => 'SUP-025', 'category' => 'أكواب ورقية وبلاستيك', 'opening_balance' => 30950.00],
            ['name' => 'أم أحمد خامات', 'code' => 'SUP-026', 'category' => 'مستلزمات توريد', 'opening_balance' => 27000.00],
            ['name' => 'شركة الأمل للبلاستيك', 'code' => 'SUP-027', 'category' => 'بوليمرات ورولات', 'opening_balance' => 222595.00],
            ['name' => 'الأهلية للبلاستيك', 'code' => 'SUP-028', 'category' => 'خامات حقن', 'opening_balance' => 70120.00],
            ['name' => 'سيلفر باك', 'code' => 'SUP-029', 'category' => 'ألومنيوم وتغليف', 'opening_balance' => 70000.00],
            ['name' => 'نظمي أورينت', 'code' => 'SUP-030', 'category' => 'منكهات وعطور', 'opening_balance' => 0.00],
        ];

        foreach ($suppliers as $sData) {
            Supplier::updateOrCreate(
                ['name' => $sData['name']],
                $sData
            );
        }

        // 3. Seed Realistic October/November Transactions from Excel Analysis
        // Clear previous dummy transactions so numbers match October sheet
        $abouTarek = Customer::where('name', 'كشري أبو طارق')->first();
        $sheikhElBalad = Customer::where('name', 'مطاعم شيخ البلد')->first();
        $dina = Customer::where('name', 'دينا فارمز (دينا)')->first();
        $roin = Customer::where('name', 'شركة روين')->first();
        $remon = Supplier::where('name', 'ريمون أكياس الروبيكي')->first();
        $alaa = Supplier::where('name', 'تغليف علاء مؤسسة الزكاة')->first();
        $hamza = Supplier::where('name', 'حمزة السوري')->first();

        $transactions = [
            // October Sales Invoices
            [
                'transaction_number' => 'INV-2026-10-001',
                'type' => 'sales_invoice',
                'category' => 'إيرادات مبيعات كوڤير',
                'customer_id' => $abouTarek?->id,
                'amount' => 887050.00,
                'net_amount' => 775200.00,
                'tax_vat_amount' => 108528.00,
                'tax_withholding_amount' => 7752.00,
                'debit_account' => 'حـ/ العملاء - كشري أبو طارق',
                'credit_account' => 'حـ/ إيرادات المبيعات',
                'transaction_date' => '2026-10-15',
                'payment_method' => 'آجل',
                'description' => 'فاتورة مبيعات كوڤير أزرق 285 ألف قطعة وملاعق حلو وشوك للعميل كشري أبو طارق',
            ],
            [
                'transaction_number' => 'INV-2026-10-002',
                'type' => 'sales_invoice',
                'category' => 'إيرادات مبيعات كوڤير',
                'customer_id' => $roin?->id,
                'amount' => 1616548.62,
                'net_amount' => 1430574.00,
                'tax_vat_amount' => 200280.36,
                'tax_withholding_amount' => 14305.74,
                'debit_account' => 'حـ/ العملاء - شركة روين',
                'credit_account' => 'حـ/ إيرادات المبيعات',
                'transaction_date' => '2026-10-18',
                'payment_method' => 'آجل',
                'description' => 'فاتورة مبيعات كوڤير 7*1 (453 ألف قطعة) للعميل روين',
            ],
            [
                'transaction_number' => 'INV-2026-10-003',
                'type' => 'sales_invoice',
                'category' => 'إيرادات مبيعات',
                'customer_id' => $sheikhElBalad?->id,
                'amount' => 105090.00,
                'net_amount' => 93000.00,
                'tax_vat_amount' => 13020.00,
                'tax_withholding_amount' => 930.00,
                'debit_account' => 'حـ/ العملاء - مطاعم شيخ البلد',
                'credit_account' => 'حـ/ إيرادات المبيعات',
                'transaction_date' => '2026-10-22',
                'payment_method' => 'آجل',
                'description' => 'فاتورة كوڤير 2*1 وأكياس مطبوعة لمطاعم شيخ البلد',
            ],

            // Customer Collections (تحصيلات وسندات قبض)
            [
                'transaction_number' => 'REC-2026-10-001',
                'type' => 'customer_payment',
                'category' => 'تحصيلات عملاء',
                'customer_id' => $abouTarek?->id,
                'amount' => 866250.00,
                'debit_account' => 'حـ/ الخزينة الرئيسية',
                'credit_account' => 'حـ/ العملاء - كشري أبو طارق',
                'transaction_date' => '2026-10-25',
                'payment_method' => 'تحويل بنكي',
                'description' => 'سند قبض - تحصيل دفعة مبيعات من كشري أبو طارق بشيك بنكي مسحوب',
            ],
            [
                'transaction_number' => 'REC-2026-10-002',
                'type' => 'customer_payment',
                'category' => 'تحصيلات عملاء',
                'customer_id' => $roin?->id,
                'amount' => 200000.00,
                'debit_account' => 'حـ/ الخزينة الرئيسية',
                'credit_account' => 'حـ/ العملاء - شركة روين',
                'transaction_date' => '2026-10-28',
                'payment_method' => 'نقدي',
                'description' => 'سند قبض - تحصيل نقدي جزء من مديونية روين',
            ],
            [
                'transaction_number' => 'REC-2026-10-003',
                'type' => 'customer_payment',
                'category' => 'تحصيلات عملاء',
                'customer_id' => $dina?->id,
                'amount' => 324536.00,
                'debit_account' => 'حـ/ الخزينة الرئيسية',
                'credit_account' => 'حـ/ العملاء - دينا فارمز',
                'transaction_date' => '2026-10-30',
                'payment_method' => 'شيك',
                'check_number' => 'CHK-889012',
                'description' => 'سند قبض - تحصيل بشيك من دينا فارمز',
            ],

            // Supplier Bills (فواتير مشتريات خامات)
            [
                'transaction_number' => 'BILL-2026-10-001',
                'type' => 'supplier_bill',
                'category' => 'مشتريات رولات وخامات',
                'supplier_id' => $remon?->id,
                'amount' => 1261216.00,
                'debit_account' => 'حـ/ مشتريات خامات وبوليمرات',
                'credit_account' => 'حـ/ الموردين - ريمون أكياس الروبيكي',
                'transaction_date' => '2026-10-12',
                'payment_method' => 'آجل',
                'description' => 'فاتورة توريد رولات أكياس وبوليمرات سيلوفان مسحوبات أكتوبر',
            ],
            [
                'transaction_number' => 'BILL-2026-10-002',
                'type' => 'supplier_bill',
                'category' => 'مشتريات تغليف',
                'supplier_id' => $alaa?->id,
                'amount' => 25300.00,
                'debit_account' => 'حـ/ مشتريات تغليف وسلوفان',
                'credit_account' => 'حـ/ الموردين - علاء مؤسسة الزكاة',
                'transaction_date' => '2026-10-19',
                'payment_method' => 'آجل',
                'description' => 'فاتورة توريد خامات تغليف مسحوبات أكتوبر',
            ],

            // Supplier Payments (سداد لموردين وسندات صرف)
            [
                'transaction_number' => 'PAY-2026-10-001',
                'type' => 'supplier_payment',
                'category' => 'سداد موردين',
                'supplier_id' => $alaa?->id,
                'amount' => 40000.00,
                'debit_account' => 'حـ/ الموردين - علاء مؤسسة الزكاة',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-24',
                'payment_method' => 'نقدي',
                'description' => 'سند صرف نقدية - سداد دفعة مورد علاء مؤسسة الزكاة',
            ],
            [
                'transaction_number' => 'PAY-2026-10-002',
                'type' => 'supplier_payment',
                'category' => 'سداد موردين',
                'supplier_id' => $hamza?->id,
                'amount' => 258800.00,
                'debit_account' => 'حـ/ الموردين - حمزة السوري',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-27',
                'payment_method' => 'تحويل بنكي',
                'description' => 'سند صرف - تحويل سداد فواتير خامات ومسحوبات حمزة السوري',
            ],

            // Real General Expenses (المصروفات العمومية والتشغيلية من شيت م غمومية)
            [
                'transaction_number' => 'EXP-2026-10-001',
                'type' => 'expense',
                'category' => 'مرتبات وأجور',
                'amount' => 133491.00,
                'debit_account' => 'حـ/ مصروفات مرتبات وأجور العاملين',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-05',
                'payment_method' => 'نقدي',
                'description' => 'صرف مرتبات العاملين والفنيين عن شهر سبتمبر',
            ],
            [
                'transaction_number' => 'EXP-2026-10-002',
                'type' => 'expense',
                'category' => 'إيجارات',
                'amount' => 73381.00,
                'debit_account' => 'حـ/ مصروفات إيجار المصنع والمقر',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-01',
                'payment_method' => 'تحويل بنكي',
                'description' => 'سداد القيمة الإيجارية لمصانع ألفا إيجبت عن شهر أكتوبر',
            ],
            [
                'transaction_number' => 'EXP-2026-10-003',
                'type' => 'expense',
                'category' => 'نقل وشحن بضاعة',
                'amount' => 122255.00,
                'debit_account' => 'حـ/ مصروفات نقل وشحن بضاعة للعملاء',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-20',
                'payment_method' => 'نقدي',
                'description' => 'مصاريف سيارات نقل البضاعة وتوزيع طلبيات العملاء',
            ],
            [
                'transaction_number' => 'EXP-2026-10-004',
                'type' => 'expense',
                'category' => 'سلف العاملين',
                'amount' => 90640.00,
                'debit_account' => 'حـ/ سلف العاملين والعهد المؤقتة',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-15',
                'payment_method' => 'نقدي',
                'description' => 'سلف العاملين ومستحقات مؤقتة مجمعة خلال الشهر',
            ],
            [
                'transaction_number' => 'EXP-2026-10-005',
                'type' => 'expense',
                'category' => 'مصاريف تشغيل',
                'amount' => 60715.00,
                'debit_account' => 'حـ/ مصروفات تشغيلية وصيانة مصنع',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-22',
                'payment_method' => 'نقدي',
                'description' => 'مصاريف تشغيل خطوط الإنتاج والكوڤير ومستلزمات صيانة دورية',
            ],
            [
                'transaction_number' => 'EXP-2026-10-006',
                'type' => 'expense',
                'category' => 'نقل عمالة ومواصلات',
                'amount' => 20300.00,
                'debit_account' => 'حـ/ مواصلات ونقل عمالة',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-24',
                'payment_method' => 'نقدي',
                'description' => 'اشتراكات سيارات نقل العمالة والورديات',
            ],
            [
                'transaction_number' => 'EXP-2026-10-007',
                'type' => 'expense',
                'category' => 'فواتير إلكترونية وتأمينات',
                'amount' => 20000.00,
                'debit_account' => 'حـ/ فواتير إلكترونية ومصروفات مهنية',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-25',
                'payment_method' => 'تحويل بنكي',
                'description' => 'رسوم المنظومة الإلكترونية للضرائب والفواتير',
            ],
            [
                'transaction_number' => 'EXP-2026-10-008',
                'type' => 'expense',
                'category' => 'وقود وزيوت',
                'amount' => 15635.00,
                'debit_account' => 'حـ/ وقود وزيوت السيارات والماكينات',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-26',
                'payment_method' => 'نقدي',
                'description' => 'بنزين وسولار وزيوت لسيارات النقل ومولدات المصنع',
            ],
            [
                'transaction_number' => 'EXP-2026-10-009',
                'type' => 'expense',
                'category' => 'دعاية وإعلانات',
                'amount' => 15700.00,
                'debit_account' => 'حـ/ دعاية وإعلانات وتسويق',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-28',
                'payment_method' => 'تحويل إلكتروني',
                'description' => 'حملات تسويقية وتصميمات دعائية للعلامة التجارية ألفا إيجبت',
            ],
            [
                'transaction_number' => 'EXP-2026-10-010',
                'type' => 'expense',
                'category' => 'لف وحزم وتعبئة',
                'amount' => 8665.00,
                'debit_account' => 'حـ/ مصاريف لف وحزم ومستلزمات شحن',
                'credit_account' => 'حـ/ الخزينة الرئيسية',
                'transaction_date' => '2026-10-29',
                'payment_method' => 'نقدي',
                'description' => 'شراء شريط لاصق وبكر تعبئة وكرتون حزم',
            ],
        ];

        foreach ($transactions as $txn) {
            FinancialTransaction::updateOrCreate(
                ['transaction_number' => $txn['transaction_number']],
                $txn
            );
        }
    }
}
